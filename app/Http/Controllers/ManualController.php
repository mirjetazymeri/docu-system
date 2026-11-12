<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesApplicationAccess;
use App\Models\Application;
use App\Models\Manual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;

class ManualController extends Controller
{
    use AuthorizesApplicationAccess;

    // Display a list of manuals for a specific application
    public function index(Application $application)
    {
        $this->authorizeApplicationPermission($application, 'manuals.view');
        $manuals = $application->manuals; // assuming relationship is defined in Application model
        $canCreateManuals = $this->canEditApplication(
            $application,
            'manuals.create'
        );
        $canEditManuals = $this->canEditApplication(
            $application,
            'manuals.edit'
        );

        return view(
            'admin.manuals.index',
            compact(
                'application',
                'manuals',
                'canCreateManuals',
                'canEditManuals'
            )
        );
    }

    // Show the form for creating a new manual
    public function create(Application $application)
    {
        $this->authorizeApplicationEdit($application, 'manuals.create');
        return view('admin.manuals.create', compact('application'));
    }

    // Store a new manual
    // public function store(Request $request, Application $application)
    // {
    //     $request->validate([
    //         'title' => 'required|string|max:255',
    //         'content' => 'required|string',
    //     ]);

    //     $manual = new Manual();
    //     $manual->title = $request->title;
    //     $manual->content = $request->content;
    //     $manual->application_id = $application->id; // Ensure this is set to the correct application ID
    //     $manual->save();

    //     return redirect()->route('manuals.index', $application->id)
    //                     ->with('success', __('ui.manual_created_successfully'));
    // }


    // Store a new manual
    public function store(Request $request, Application $application)
    {
        $this->authorizeApplicationEdit($application, 'manuals.create');
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $manual = Manual::create([
            'application_id' => $application->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        // Generate the PDF
        $pdf = $this->buildManualPdf($manual, $application);

        // Save the PDF to storage
        $filePath = "documents/manual_{$manual->id}.pdf";
        Storage::put($filePath, $pdf->output());

        // Update the manual with the PDF URL
        $manual->update(['pdf_url' => Storage::url($filePath)]);

        return redirect()->route('manuals.index', $application->id)
                         ->with('success', __('ui.manual_created_successfully'));
    }

    // Show the form for editing the specified manual
    public function edit(Application $application, Manual $manual)
    {
        $this->authorizeApplicationEdit($application, 'manuals.edit');
        abort_unless($manual->application_id === $application->id, 404);
        return view('admin.manuals.edit', compact('application', 'manual'));
    }

    // Update the specified manual
    // public function update(Request $request, Application $application, Manual $manual)
    // {
    //     $request->validate([
    //         'title' => 'required|string|max:255',
    //         'content' => 'required|string',
    //     ]);

    //     $manual->title = $request->title;
    //     $manual->content = $request->content;
    //     $manual->save();

    //     return redirect()->route('manuals.index', $application->id)
    //                      ->with('success', __('ui.manual_updated_successfully'));
    // }

    // Update the specified manual
    public function update(Request $request, Application $application, Manual $manual)
    {
        $this->authorizeApplicationEdit($application, 'manuals.edit');
        abort_unless($manual->application_id === $application->id, 404);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $manual->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        // Generate the updated PDF
        $pdf = $this->buildManualPdf($manual, $application);

        // Save the updated PDF to storage
        $filePath = "documents/manual_{$manual->id}.pdf";
        Storage::put($filePath, $pdf->output());

        // Update the manual with the updated PDF URL
        $manual->update(['pdf_url' => Storage::url($filePath)]);

        return redirect()->route('manuals.index', $application->id)
                         ->with('success', __('ui.manual_updated_successfully'));
    }

    // Delete the specified manual
    public function destroy(Application $application, Manual $manual)
    {
        $this->authorizeApplicationAdministrator();
        abort_unless($manual->application_id === $application->id, 404);
        $manual->delete();
        return redirect()->route('manuals.index', $application->id)
                         ->with('success', __('ui.manual_deleted_successfully'));
    }

    // Generate and download the PDF of the manual
    // public function downloadPDF(Application $application, Manual $manual)
    // {
    //     $pdf = PDF::loadView('admin.manuals.pdf', compact('manual', 'application'));
    //     return $pdf->stream($manual->title . '.pdf');  // This will display the PDF in the browser
    // }
// Generate and open the PDF of the manual in a new tab
public function downloadPDF(Application $application, Manual $manual)
{
    $this->authorizeApplicationPermission($application, 'manuals.view');
    abort_unless($manual->application_id === $application->id, 404);
    $pdf = $this->buildManualPdf($manual, $application);
    $pdfContent = $pdf->output();
    $filePath = "documents/manual_{$manual->id}.pdf";

    Storage::put($filePath, $pdfContent);
    $manual->update(['pdf_url' => Storage::url($filePath)]);

    return response($pdfContent, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . $manual->title . '.pdf"',
    ]);
}

    /**
     * Prepare manual content and register the Unicode emoji font in DomPDF.
     */
    private function buildManualPdf(Manual $manual, Application $application)
    {
        $pdfContent = $this->prepareManualPdfHtml($manual->content);
        $pdf = PDF::loadView(
            'admin.manuals.pdf',
            compact('manual', 'application', 'pdfContent')
        );

        $fontPath = storage_path('fonts/NotoEmoji-Regular.ttf');

        if (!is_file($fontPath)) {
            throw new \RuntimeException('The PDF emoji font is missing.');
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
            throw new \RuntimeException('The PDF emoji font could not be loaded.');
        }

        return $pdf;
    }

    /**
     * Remove unsupported SVG markup and apply the emoji font to Unicode icons.
     */
    private function prepareManualPdfHtml(string $html): string
    {
        $html = preg_replace(
            '#<svg\b[^>]*>.*?</svg\s*>#is',
            '',
            $html
        ) ?? $html;
        $html = preg_replace('#<svg\b[^>]*/\s*>#is', '', $html) ?? $html;

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




}
