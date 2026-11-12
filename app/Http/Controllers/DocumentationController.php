<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesApplicationAccess;
use App\Models\Application;
use App\Models\Documentation;
use App\Models\DocumentationWorkerHistory;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF;
use RuntimeException;
use Sentinel;
use Throwable;

class DocumentationController extends Controller
{
    use AuthorizesApplicationAccess;
    /**
     * Display documentations belonging to a unit.
     */
    public function index(
        Request $request,
        ?Application $application = null,
        ?Unit $unit = null
    )
    {
        if (!$application) {
            $application = Application::findOrFail(
                $request->query('application')
            );
        }

        $this->authorizeApplicationPermission($application, 'documentation.view');

        if (!$unit) {
            $unit = Unit::where('id', $request->query('unit'))
                ->where('application_id', $application->id)
                ->firstOrFail();
        } else {
            abort_unless($unit->application_id === $application->id, 404);
        }

        $documentations = Documentation::where('unit_id', $unit->id)
            ->latest('id')
            ->get();

        Log::info('Documentation index opened', [
            'application_id' => $application->id,
            'unit_id' => $unit->id,
            'documentations_count' => $documentations->count(),
        ]);

        $canCreateDocumentations = $this->canEditApplication(
            $application,
            'documentation.create'
        );
        $canEditDocumentations = $this->canEditApplication(
            $application,
            'documentation.edit'
        );

        return view(
            'admin.documentation.index',
            compact(
                'documentations',
                'unit',
                'application',
                'canCreateDocumentations',
                'canEditDocumentations'
            )
        );
    }

    /**
     * Display documentation page.
     */
    public function show(Application $application, Unit $unit)
    {
        $this->authorizeApplicationPermission($application, 'documentation.view');
        abort_unless($unit->application_id === $application->id, 404);
        $canCreateDocumentations = $this->canEditApplication(
            $application,
            'documentation.create'
        );

        return view(
            'admin.documentation.show',
            compact(
                'unit',
                'application',
                'canCreateDocumentations'
            )
        );
    }

    /**
     * Display documentation creation page.
     */
    public function create(Application $application, Unit $unit)
    {
        $this->authorizeApplicationEdit($application, 'documentation.create');
        abort_unless($unit->application_id === $application->id, 404);
        return view(
            'admin.documentation.create',
            compact('application', 'unit')
        );
    }

    /**
     * Display documentation edit page.
     */
    public function edit(
        Application $application,
        Unit $unit,
        Documentation $documentation
    ) {
        $this->authorizeApplicationEdit($application, 'documentation.edit');
        abort_unless($unit->application_id === $application->id, 404);
        abort_unless($documentation->unit_id === $unit->id, 404);

        $this->recordWorkerHistory(
            'checked',
            $application,
            $unit,
            $documentation
        );

        return view(
            'admin.documentation.edit',
            compact('documentation', 'application', 'unit')
        );
    }

    /**
     * Store documentation and generate its PDF.
     */
    public function store(
        Request $request,
        Application $application,
        Unit $unit
    ) {
        $this->authorizeApplicationEdit($application, 'documentation.create');
        abort_unless($unit->application_id === $application->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'documentation' => ['required', 'string'],
        ]);

        $userId = Sentinel::getUser()->id;
        $documentation = Documentation::create([
            'unit_id' => $unit->id,
            'title' => $validated['title'],
            'content' => $validated['documentation'],
            'created_by' => $userId,
            'updated_by' => $userId,
            'status' => 'in_process',
        ]);

        $this->recordWorkerHistory(
            'documentation_created',
            $application,
            $unit,
            $documentation
        );

        try {
            $this->generateAndStorePdf(
                $documentation,
                $unit,
                $application
            );
        } catch (Throwable $exception) {
            Log::error('PDF generation failed after documentation creation', [
                'documentation_id' => $documentation->id,
                'application_id' => $application->id,
                'unit_id' => $unit->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return redirect()
                ->route('admin.documentation.index', [
                    'application' => $application->id,
                    'unit' => $unit->id,
                ])
                ->with(
                    'pdf_error',
                    $this->describePdfFailure($exception)
                );
        }

        return redirect()
            ->route('admin.documentation.index', [
                'application' => $application->id,
                'unit' => $unit->id,
            ])
            ->with(
                'success',
                __('ui.documentation_saved_and_pdf_generated_successfully')
            );
    }

    /**
     * Update documentation and regenerate its PDF.
     */
    public function update(
        Request $request,
        Application $application,
        Unit $unit,
        Documentation $documentation
    ) {
        $this->authorizeApplicationEdit($application, 'documentation.edit');
        abort_unless($unit->application_id === $application->id, 404);
        abort_unless($documentation->unit_id === $unit->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'documentation' => ['required', 'string'],
        ]);

        $documentation->update([
            'title' => $validated['title'],
            'content' => $validated['documentation'],
            'updated_by' => Sentinel::getUser()->id,
        ]);

        $this->recordWorkerHistory(
            'documentation_updated',
            $application,
            $unit,
            $documentation
        );

        try {
            $this->generateAndStorePdf(
                $documentation,
                $unit,
                $application
            );
        } catch (Throwable $exception) {
            Log::error('PDF regeneration failed after documentation update', [
                'documentation_id' => $documentation->id,
                'application_id' => $application->id,
                'unit_id' => $unit->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return redirect()
                ->route('admin.documentation.index', [
                    'application' => $application->id,
                    'unit' => $unit->id,
                ])
                ->with(
                    'pdf_error',
                    $this->describePdfFailure($exception)
                );
        }

        return redirect()
            ->route('admin.documentation.index', [
                'application' => $application->id,
                'unit' => $unit->id,
            ])
            ->with(
                'success',
                __('ui.documentation_updated_and_pdf_regenerated_successfully')
            );
    }

    /**
     * Open an existing PDF.
     *
     * If the file does not exist, generate and store it once.
     */
    public function generatePdf(
        Application $application,
        Unit $unit,
        Documentation $documentation
    ) {
        $this->authorizeApplicationPermission($application, 'documentation.view');
        abort_unless($unit->application_id === $application->id, 404);
        abort_unless($documentation->unit_id === $unit->id, 404);

        $this->recordWorkerHistory(
            'checked',
            $application,
            $unit,
            $documentation
        );

        // Cached PDFs contain translated headings and must match this locale
        // and this version of the document.
        $relativePath = $this->getDocumentationPdfPath($documentation);

        /*
         * First check the correct public storage folder:
         * storage/app/public/documents
         */
        if (
            $relativePath &&
            Storage::disk('public')->exists($relativePath)
        ) {
            $absolutePath = storage_path(
                'app/public/' . $relativePath
            );

            return $this->inlinePdfResponse(
                $absolutePath,
                $documentation->title
            );
        }

        /*
         * Support old PDF files stored in:
         * storage/app/documents
         */
        if ($relativePath) {
            $legacyPath = storage_path(
                'app/' . $relativePath
            );

            if (is_file($legacyPath)) {
                return $this->inlinePdfResponse(
                    $legacyPath,
                    $documentation->title
                );
            }
        }

        /*
         * The PDF file is missing.
         * Generate and save it, then serve the saved file.
         */
        try {
            $relativePath = $this->generateAndStorePdf(
                $documentation,
                $unit,
                $application
            );

            $absolutePath = storage_path(
                'app/public/' . $relativePath
            );

            return $this->inlinePdfResponse(
                $absolutePath,
                $documentation->title
            );
        } catch (Throwable $exception) {
            Log::error('PDF opening or generation failed', [
                'documentation_id' => $documentation->id,
                'application_id' => $application->id,
                'unit_id' => $unit->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return redirect()
                ->back()
                ->with(
                    'pdf_error',
                    $this->describePdfFailure($exception)
                );
        }
    }

    /**
     * Return the progress page immediately; each document renders separately.
     */
    public function generatePdfAll(Request $request, Application $application)
    {
        $this->authorizeApplicationPermission($application, 'documentation.view');
        $export = app(\App\Services\ApplicationPdfExport::class);
        $manifest = $export->manifest($application, $request->boolean('regenerate'));

        if ($request->boolean('manifest')) {
            return response()->json($manifest)->header('Cache-Control', 'private, no-store');
        }

        return response()->view('admin.documentation.pdf_export', [
            'application' => $application,
            'manifest' => $manifest,
        ])->header('Cache-Control', 'private, no-store');
    }

    /**
     * A bounded rendering request, with private, content-versioned caching.
     */
    public function generateApplicationPdfPart(
        Request $request,
        Application $application,
        int $unitId,
        int $documentId
    ) {
        $this->authorizeApplicationPermission($application, 'documentation.view');
        $unit = $unitId ? $application->units()->findOrFail($unitId) : null;
        abort_if(!$unit && ($documentId || $application->units()->exists()), 404);
        $document = $documentId && $unit ? $unit->documentations()->findOrFail($documentId) : null;
        abort_if($unit && !$document && $unit->documentations()->exists(), 409, 'Documentation changed. Reload the export.');

        $revision = app(\App\Services\ApplicationPdfExport::class)->revision($application, $unit, $document);
        abort_unless(hash_equals($revision, (string) $request->query('revision')), 409, 'Documentation changed. Reload the export.');

        $path = 'documents/pdf-parts/'.$application->id.'/'.$revision.'.pdf';
        $disk = Storage::disk('local');
        if (!$request->boolean('regenerate') && $disk->exists($path)) {
            return $this->inlinePdfResponse($disk->path($path), $application->name.'-Documentation');
        }

        $lock = \Illuminate\Support\Facades\Cache::store('file')->lock('pdf-part-'.$revision, 90);
        if (!$lock->get()) {
            return response()->json(['message' => __('ui.this_document_is_being_prepared_please_retry')], 503)
                ->header('Retry-After', '3');
        }

        try {
            // Keep any single failed rendering below the proxy timeout.
            set_time_limit(60);
            ini_set('memory_limit', '512M');
            if ($document) {
                $document->pdf_content = $this->normalizePdfHtml($document->content, $document->id);
            }
            $pdf = PDF::loadView('admin.documentation.pdf_report_all_units', [
                'application' => $application,
                'units' => $unit ? collect([$unit]) : collect(),
                'documentations' => $unit ? [$unit->id => $document ? collect([$document]) : collect()] : [],
            ])->setPaper('a4', 'portrait');
            $pdf->setOption(['isFontSubsettingEnabled' => true, 'isRemoteEnabled' => false]);
            $this->registerPdfEmojiFont($pdf);
            $bytes = $pdf->output();
            // Publish only a complete PDF; a killed renderer cannot leave a partial cache hit.
            $temporary = $path.'.'.Str::uuid().'.tmp';
            if (!$disk->put($temporary, $bytes) || !$disk->move($temporary, $path)) {
                throw new RuntimeException('The PDF part could not be saved.');
            }
            return $this->inlinePdfResponse($disk->path($path), $application->name.'-Documentation');
        } catch (Throwable $exception) {
            Log::error('Application PDF part failed', [
                'application_id' => $application->id,
                'documentation_id' => $documentId,
                'message' => $exception->getMessage(),
            ]);
            return response()->json([
                'message' => __('ui.this_document_could_not_be_rendered_check_its_content_and_try_again'),
            ], 422);
        } finally {
            $lock->release();
        }
    }
    /**
     * Delete documentation and its stored PDF.
     */
    public function destroy(
        Application $application,
        Unit $unit,
        Documentation $documentation
    ) {
        $this->authorizeApplicationAdministrator();
        abort_unless($unit->application_id === $application->id, 404);
        abort_unless($documentation->unit_id === $unit->id, 404);

        $relativePath = $this->getRelativePdfPath(
            $documentation->pdf_url
        );

        if ($relativePath) {
            /*
             * Delete PDF from the correct public disk.
             */
            Storage::disk('public')->delete($relativePath);

            /*
             * Delete an old PDF from storage/app if it exists.
             */
            $legacyPath = storage_path(
                'app/' . $relativePath
            );

            if (is_file($legacyPath)) {
                unlink($legacyPath);
            }
        }

        /*
         * Delete the cached complete application PDF because its
         * content is now outdated.
         */
        Storage::disk('public')->delete(
            $this->getApplicationPdfPath($application)
        );

        $documentation->delete();

        return redirect()
            ->route('admin.documentation.index', [
                'application' => $application->id,
                'unit' => $unit->id,
            ])
            ->with(
                'success',
                __('ui.documentation_deleted_successfully')
            );
    }

    /**
     * Generate and save one documentation PDF.
     */
    private function generateAndStorePdf(
        Documentation $documentation,
        Unit $unit,
        Application $application
    ): string {
        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $start = microtime(true);

        Log::info('Single PDF generation started', [
            'documentation_id' => $documentation->id,
            'application_id' => $application->id,
            'unit_id' => $unit->id,
        ]);

        $pdfSource = $this->sanitizePdfHtml($documentation->content);
        $this->validatePdfContent($pdfSource);
        $pdfContent = $this->wrapPdfEmoji(
            $pdfSource
        );

        $pdf = PDF::loadView(
            'admin.documentation.pdf_report',
            [
                'documentation' => $documentation,
                'unit' => $unit,
                'application' => $application,
                'pdfContent' => $pdfContent,
            ]
        )->setPaper('a4', 'portrait');

        $this->registerPdfEmojiFont($pdf);

        $pdfContent = $pdf->output();

        $relativePath = $this->getDocumentationPdfPath(
            $documentation
        );

        $saved = Storage::disk('public')->put(
            $relativePath,
            $pdfContent
        );

        if (!$saved) {
            throw new RuntimeException(
                'The documentation PDF could not be saved.'
            );
        }

        $documentation->update([
            'pdf_url' => Storage::disk('public')->url(
                $relativePath
            ),
        ]);

        /*
         * Documentation changed, so remove the cached complete PDF.
         * It will be regenerated the next time it is opened.
         */
        Storage::disk('public')->delete(
            $this->getApplicationPdfPath($application)
        );

        Log::info('Single PDF generation completed', [
            'documentation_id' => $documentation->id,
            'application_id' => $application->id,
            'unit_id' => $unit->id,
            'seconds' => round(microtime(true) - $start, 2),
            'memory_mb' => round(
                memory_get_peak_usage(true) / 1024 / 1024,
                2
            ),
            'pdf_size_mb' => round(
                strlen($pdfContent) / 1024 / 1024,
                2
            ),
            'path' => $relativePath,
        ]);

        return $relativePath;
    }

    /**
     * Reject editor content that DomPDF cannot render reliably.
     */
    private function validatePdfContent(string $content): void
    {
        $unsupportedTags = [
            'video',
            'audio',
            'iframe',
            'canvas',
            'object',
            'embed',
        ];

        foreach ($unsupportedTags as $tag) {
            if (preg_match('/<' . $tag . '\b/i', $content)) {
                throw new RuntimeException(
                    'PDF_CONTENT:' . __('pdf_export.unsupported_element', ['tag' => $tag])
                );
            }
        }

        preg_match_all(
            '/<img\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/is',
            $content,
            $imageMatches
        );

        $imageTagCount = preg_match_all('/<img\b/i', $content);

        if ($imageTagCount > count($imageMatches[2])) {
            throw new RuntimeException(
                'PDF_CONTENT:' . __('pdf_export.missing_source')
            );
        }

        foreach ($imageMatches[2] as $source) {
            $source = trim(html_entity_decode($source));

            if ($source === '') {
                throw new RuntimeException(
                    'PDF_CONTENT:' . __('pdf_export.empty_image')
                );
            }

            if (preg_match('#^https?://#i', $source)) {
                throw new RuntimeException(
                    'PDF_CONTENT:' . __('pdf_export.external_image')
                );
            }

            if (str_starts_with($source, 'data:')) {
                if (
                    !preg_match(
                        '#^data:image/(png|jpe?g|gif);base64,([a-z0-9+/=\s]+)$#i',
                        $source,
                        $dataMatch
                    )
                ) {
                    throw new RuntimeException(
                        'PDF_CONTENT:' . __('pdf_export.image_format')
                    );
                }

                $encodedImage = preg_replace('/\s+/', '', $dataMatch[2]);
                $decodedImage = base64_decode($encodedImage, true);

                if ($decodedImage === false) {
                    throw new RuntimeException(
                        'PDF_CONTENT:' . __('pdf_export.damaged_image')
                    );
                }

                if (strlen($decodedImage) > 5 * 1024 * 1024) {
                    throw new RuntimeException(
                        'PDF_CONTENT:' . __('pdf_export.large_image')
                    );
                }
            }
        }
    }

    /**
     * Repair editor HTML fragments before combining many documents.
     *
     * DomPDF is sensitive to orphaned table row groups and nested tables.
     * Loading the fragment through DOMDocument repairs unclosed elements;
     * unsafe row groups and page-break rules are removed explicitly.
     */
    private function normalizePdfHtml(string $content, int $documentationId): string
    {
        $content = $this->sanitizePdfHtml($content);

        if (!class_exists(\DOMDocument::class)) {
            return $this->wrapPdfEmoji($content);
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previousState = libxml_use_internal_errors(true);

        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="pdf-content-root">'
            . $content
            . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        if (!$loaded) {
            Log::warning('Documentation HTML normalization failed', [
                'documentation_id' => $documentationId,
            ]);

            return $this->wrapPdfEmoji($content);
        }

        $xpath = new \DOMXPath($dom);

        /*
         * DomPDF 2.x can crash in TableRowGroup when one large combined
         * report contains malformed/nested editor tables. For the combined
         * PDF only, convert tables to block rows. This preserves all content
         * and removes the table reflower from the rendering path entirely.
         */
        $tables = [];

        foreach ($xpath->query('//table') as $table) {
            $tables[] = $table;
        }

        $convertedTables = 0;

        foreach (array_reverse($tables) as $table) {
            if (!$table->parentNode) {
                continue;
            }

            $tableBlock = $dom->createElement('div');
            $tableBlock->setAttribute('class', 'pdf-safe-table');
            $rows = $xpath->query(
                './tr | ./thead/tr | ./tbody/tr | ./tfoot/tr',
                $table
            );

            foreach ($rows as $row) {
                $rowBlock = $dom->createElement('div');
                $rowBlock->setAttribute('class', 'pdf-safe-table-row');

                foreach (iterator_to_array($row->childNodes) as $cell) {
                    if (
                        !$cell instanceof \DOMElement
                        || !in_array(strtolower($cell->tagName), ['td', 'th'], true)
                    ) {
                        continue;
                    }

                    $cellBlock = $dom->createElement('div');
                    $cellClass = strtolower($cell->tagName) === 'th'
                        ? 'pdf-safe-table-cell pdf-safe-table-heading'
                        : 'pdf-safe-table-cell';
                    $cellBlock->setAttribute('class', $cellClass);

                    while ($cell->firstChild) {
                        $cellBlock->appendChild($cell->firstChild);
                    }

                    $rowBlock->appendChild($cellBlock);
                }

                if ($rowBlock->hasChildNodes()) {
                    $tableBlock->appendChild($rowBlock);
                }
            }

            if (!$tableBlock->hasChildNodes()) {
                while ($table->firstChild) {
                    $tableBlock->appendChild($table->firstChild);
                }
            }

            $table->parentNode->replaceChild($tableBlock, $table);
            $convertedTables++;
        }

        foreach ($xpath->query('//thead[not(ancestor::table)] | //tbody[not(ancestor::table)] | //tfoot[not(ancestor::table)]') as $orphanGroup) {
            $fragment = $dom->createDocumentFragment();

            while ($orphanGroup->firstChild) {
                $fragment->appendChild($orphanGroup->firstChild);
            }

            $orphanGroup->parentNode?->replaceChild($fragment, $orphanGroup);
        }

        /*
         * Also remove orphaned table-related elements. DomPDF creates
         * anonymous table frames for these tags and can still reach the same
         * TableRowGroup crash even when no <table> tag remains.
         */
        $orphanTableElements = [];

        foreach ($xpath->query('//thead | //tbody | //tfoot | //tr | //td | //th') as $element) {
            $orphanTableElements[] = $element;
        }

        foreach (array_reverse($orphanTableElements) as $element) {
            if (!$element->parentNode) {
                continue;
            }

            $replacement = $dom->createElement('div');
            $tagName = strtolower($element->tagName);
            $replacement->setAttribute(
                'class',
                in_array($tagName, ['th', 'thead'], true)
                    ? 'pdf-safe-table-cell pdf-safe-table-heading'
                    : 'pdf-safe-table-cell'
            );

            while ($element->firstChild) {
                $replacement->appendChild($element->firstChild);
            }

            $element->parentNode->replaceChild($replacement, $element);
        }

        foreach ($xpath->query('//*[@style]') as $element) {
            $style = $element->getAttribute('style');
            $style = preg_replace(
                '/page-break-inside\s*:\s*avoid\s*;?/i',
                '',
                $style
            );
            $style = preg_replace(
                '/display\s*:\s*table(?:-row|-row-group|-header-group|-footer-group|-cell)?\s*;?/i',
                'display: block;',
                $style
            );
            $element->setAttribute('style', trim($style));
        }

        if ($errors) {
            Log::debug('Documentation HTML repaired for combined PDF', [
                'documentation_id' => $documentationId,
                'parser_errors' => count($errors),
            ]);
        }

        if ($convertedTables > 0) {
            Log::debug('Documentation tables converted for combined PDF', [
                'documentation_id' => $documentationId,
                'tables_count' => $convertedTables,
            ]);
        }

        $root = $dom->getElementById('pdf-content-root');

        if (!$root) {
            return $this->wrapPdfEmoji($content);
        }

        $html = '';

        foreach ($root->childNodes as $child) {
            $html .= $dom->saveHTML($child);
        }

        return $this->wrapPdfEmoji($html);
    }

    /**
     * Remove editor elements that DomPDF cannot render, without changing the
     * content saved in the database. SVG is commonly introduced by pasted
     * icons and must not prevent the rest of the document from rendering.
     */
    private function sanitizePdfHtml(string $html): string
    {
        $sanitized = preg_replace(
            '#<svg\b[^>]*>.*?</svg\s*>#is',
            '',
            $html
        );

        $sanitized = preg_replace(
            '#<svg\b[^>]*/\s*>#is',
            '',
            $sanitized ?? $html
        );

        return $sanitized ?? $html;
    }

    /**
     * Apply the embedded monochrome emoji font without changing HTML tags.
     */
    private function wrapPdfEmoji(string $html): string
    {
        $emojiPattern = '/('
            . '(?:[\x{1F1E6}-\x{1F1FF}]{2})'
            . '|(?:[#*0-9]\x{FE0F}?\x{20E3})'
            . '|(?:[\x{00A9}\x{00AE}\x{203C}\x{2049}\x{2122}\x{2139}'
            . '\x{2194}-\x{21FF}\x{2300}-\x{23FF}\x{25AA}-\x{27BF}'
            . '\x{2B00}-\x{2BFF}\x{3030}\x{303D}\x{3297}\x{3299}'
            . '\x{1F000}-\x{1FAFF}]'
            . '(?:\x{FE0E}|\x{FE0F})?'
            . '(?:[\x{1F3FB}-\x{1F3FF}])?'
            . '(?:\x{200D}[\x{00A9}\x{00AE}\x{203C}-\x{3299}'
            . '\x{1F000}-\x{1FAFF}](?:\x{FE0E}|\x{FE0F})?'
            . '(?:[\x{1F3FB}-\x{1F3FF}])?)*'
            . '))'
            . '/u';

        $parts = preg_split(
            '/(<[^>]*>)/s',
            $html,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        if ($parts === false) {
            return $html;
        }

        foreach ($parts as $index => $part) {
            if ($part === '' || str_starts_with($part, '<')) {
                continue;
            }

            $parts[$index] = preg_replace(
                $emojiPattern,
                '<span class="pdf-emoji">$1</span>',
                $part
            ) ?? $part;
        }

        return implode('', $parts);
    }

    /**
     * Register the static TrueType emoji font before DomPDF renders the view.
     */
    private function registerPdfEmojiFont($pdf): void
    {
        $fontPath = storage_path('fonts/NotoEmoji-Regular.ttf');

        if (!is_file($fontPath)) {
            throw new RuntimeException(
                'PDF_CONTENT:' . __('pdf_export.font_missing')
            );
        }

        $registered = $pdf->getDomPDF()
            ->getFontMetrics()
            ->registerFont(
                [
                    'family' => 'Noto Emoji PDF',
                    'style' => 'normal',
                    'weight' => 'normal',
                ],
                $fontPath
            );

        if (!$registered) {
            throw new RuntimeException(
                'PDF_CONTENT:' . __('pdf_export.font_error')
            );
        }
    }

    /**
     * Convert a technical PDF exception into safe, actionable feedback.
     */
    private function describePdfFailure(Throwable $exception): array
    {
        $message = $exception->getMessage();
        $suggestions = [
            __('ui.open_the_document_for_editing_and_correct_the_problematic_element'),
            __('ui.save_again_after_making_the_correction'),
        ];

        if (str_starts_with($message, 'PDF_CONTENT:')) {
            $description = trim(substr($message, strlen('PDF_CONTENT:')));
        } elseif (
            stripos($message, 'memory') !== false ||
            stripos($message, 'allowed memory size') !== false
        ) {
            $description = __('ui.the_pdf_exceeded_the_available_memory');
            array_unshift(
                $suggestions,
                __('ui.compress_images_and_split_the_document_into_smaller_parts')
            );
        } elseif (
            stripos($message, 'image') !== false ||
            stripos($message, 'png') !== false ||
            stripos($message, 'jpeg') !== false
        ) {
            $description = __('ui.an_image_could_not_be_read_or_has_an_unsupported_format');
            array_unshift(
                $suggestions,
                __('ui.remove_the_problematic_image_and_upload_it_as_png_or_jpg')
            );
        } elseif (
            stripos($message, 'utf-8') !== false ||
            stripos($message, 'encoding') !== false ||
            stripos($message, 'font') !== false
        ) {
            $description = __('ui.a_symbol_or_font_could_not_be_rendered_in_the_pdf');
            array_unshift(
                $suggestions,
                __('ui.remove_the_unusual_symbol_or_emoji_or_use_standard_text')
            );
        } elseif (
            stripos($message, 'permission') !== false ||
            stripos($message, 'writable') !== false ||
            stripos($message, 'could not be saved') !== false
        ) {
            $description = __('ui.the_pdf_was_created_but_the_server_could_not_save_the_file');
            $suggestions = [
                __('ui.check_the_permissions_of_the_storage_app_public_documents_folder'),
                __('ui.make_sure_the_server_has_free_space_and_try_again'),
            ];
        } else {
            $description = __('ui.the_content_was_saved_but_the_pdf_could_not_be_generated');
            array_unshift(
                $suggestions,
                __('ui.check_images_tables_symbols_and_pasted_formatting')
            );
        }

        return [
            'title' => __('ui.the_pdf_could_not_be_generated'),
            'description' => $description,
            'suggestions' => array_values(array_unique($suggestions)),
            'reference' => now()->format('Ymd-His'),
        ];
    }

    /**
     * Convert a stored URL into a relative storage path.
     *
     * Examples:
     * /storage/documents/file.pdf -> documents/file.pdf
     * storage/documents/file.pdf  -> documents/file.pdf
     * public/documents/file.pdf   -> documents/file.pdf
     */
    private function getRelativePdfPath(?string $pdfUrl): ?string
    {
        if (!$pdfUrl) {
            return null;
        }

        $path = parse_url($pdfUrl, PHP_URL_PATH);

        if (!$path) {
            $path = $pdfUrl;
        }

        $path = ltrim($path, '/');

        $path = preg_replace(
            '#^(storage|public)/#',
            '',
            $path
        );

        return $path ?: null;
    }

    /**
     * Return the relative path for one documentation PDF.
     */
    private function getDocumentationPdfPath(
        Documentation $documentation
    ): string {
        $revision = substr(hash('sha256', json_encode([
            $documentation->title,
            $documentation->content,
            app()->getLocale(),
            hash_file('sha256', resource_path('views/pdf/layouts/documentation.blade.php')),
            hash_file('sha256', resource_path('views/admin/documentation/pdf_report.blade.php')),
        ], JSON_THROW_ON_ERROR)), 0, 16);

        return "documents/documentation_{$documentation->id}_{$revision}.pdf";
    }

    /**
     * Return the relative path for the complete application PDF.
     */
    private function getApplicationPdfPath(
        Application $application
    ): string {
        return "documents/application_{$application->id}_all_documentations_v2.pdf";
    }

    private function recordWorkerHistory(
        string $event,
        Application $application,
        ?Unit $unit = null,
        ?Documentation $documentation = null,
        array $metadata = []
    ): void {
        $user = Sentinel::getUser();

        if (!$user) {
            return;
        }

        /*
         * Read-only visits to a colleague's project must not be counted as
         * contributions in the assigned worker's history.
         */
        if (
            !$this->isDocumentationWorker($user) ||
            !$this->isAssignedToApplication($application, $user->id)
        ) {
            return;
        }

        DocumentationWorkerHistory::create([
            'user_id' => $user->id,
            'application_id' => $application->id,
            'unit_id' => $unit?->id,
            'documentation_id' => $documentation?->id,
            'event' => $event,
            'metadata' => $metadata ?: null,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Return an inline PDF response.
     */
    private function inlinePdfResponse(
        string $absolutePath,
        string $title
    ) {
        if (!is_file($absolutePath)) {
            abort(404, __('ui.pdf_file_not_found'));
        }

        $filename = Str::slug($title);

        if (!$filename) {
            $filename = 'documentation';
        }

        return response()->file($absolutePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>
                'inline; filename="' . $filename . '.pdf"',
            'Cache-Control' =>
                'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
