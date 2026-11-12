<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesApplicationAccess;
use App\Models\Application;
use App\Services\TestDocuDocumentationImport;
use Sentinel;

class TestDocuDocumentationImportController extends Controller
{
    use AuthorizesApplicationAccess;

    public function __invoke(Application $application, TestDocuDocumentationImport $importer)
    {
        $this->authorizeApplicationEdit($application, 'unit.create');
        $this->authorizeApplicationEdit($application, 'documentation.create');
        abort_unless((int) $application->id === 50 && strcasecmp(trim($application->name), 'TestDocu') === 0, 404);

        $result = $importer->import($application, Sentinel::getUser()->id);

        return redirect()->route('units.index', $application)->with('success', sprintf(
            'Imported %d modules and %d documents. Skipped %d existing documents.',
            $result['modules'], $result['documents'], $result['skipped']
        ));
    }
}
