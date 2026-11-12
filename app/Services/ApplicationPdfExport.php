<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Documentation;
use App\Models\Unit;

class ApplicationPdfExport
{
    public function manifest(Application $application, bool $regenerate = false): array
    {
        $parts = [];
        $units = $application->units()->orderBy('id')->with([
            'documentations' => fn ($query) => $query->orderBy('id'),
        ])->get();

        foreach ($units as $unit) {
            foreach ($unit->documentations->isEmpty() ? [null] : $unit->documentations as $document) {
                $parts[] = $this->part($application, $unit, $document, $regenerate);
            }
        }
        if (!$parts) {
            $parts[] = $this->part($application, null, null, $regenerate);
        }

        return [
            'parts' => $parts,
            'revision' => hash('sha256', json_encode(array_column($parts, 'revision'))),
            'title' => $application->name.' - '.__('words.documentation'),
        ];
    }

    public function revision(Application $application, ?Unit $unit, ?Documentation $document): string
    {
        return hash('sha256', json_encode([
            'renderer' => 'application-parts-v1',
            'application' => [$application->id, $application->name],
            'unit' => $unit ? [$unit->id, $unit->name] : null,
            'document' => $document ? [$document->id, $document->title, $document->content] : null,
            'locale' => app()->getLocale(),
            'layout' => hash_file('sha256', resource_path('views/pdf/layouts/documentation.blade.php')),
            'template' => hash_file('sha256', resource_path('views/admin/documentation/pdf_report_all_units.blade.php')),
        ], JSON_THROW_ON_ERROR));
    }

    private function part(Application $application, ?Unit $unit, ?Documentation $document, bool $regenerate): array
    {
        $revision = $this->revision($application, $unit, $document);

        return [
            'label' => $unit ? $unit->name.($document ? ' / '.$document->title : '') : $application->name,
            'revision' => $revision,
            'url' => route('documentations.applicationPdfPart', [
                'application' => $application->id,
                'unitId' => $unit?->id ?? 0,
                'documentId' => $document?->id ?? 0,
                'revision' => $revision,
                'regenerate' => $regenerate ? 1 : 0,
            ]),
        ];
    }
}
