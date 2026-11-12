<?php

namespace App\Services;

use App\Models\Application;
use App\Models\DocumentationWorkerHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class TestDocuDocumentationImport
{
    public function import(Application $application, int $userId): array
    {
        if ((int) $application->id !== 50 || strcasecmp(trim($application->name), 'TestDocu') !== 0) {
            throw new RuntimeException('This import is only for TestDocu, project 50.');
        }

        // Validate the complete deployed payload before making any database changes.
        $root = resource_path('imports/TestDocu');
        $manifest = json_decode(file_get_contents($root.'/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
        $documents = [];
        foreach ($manifest as $relative => $hash) {
            $path = $root.'/modules/'.$relative;
            $source = is_file($path) ? file_get_contents($path) : false;
            $source = $source === false ? false : str_replace("\r\n", "\n", $source);
            if ($source === false || hash('sha256', $source) !== $hash) {
                throw new RuntimeException('The import files are incomplete. Wait for deployment to finish and retry.');
            }
            [$folder, $file] = explode('/', $relative);
            $documents[$folder][$file] = (string) Str::markdown($source, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
        }
        if (count($documents) !== 19 || count($manifest) !== 203) {
            throw new RuntimeException('Unexpected TestDocu import manifest.');
        }

        $result = DB::transaction(function () use ($application, $documents, $userId) {
            // Serialize repeated requests against this project, including double clicks.
            Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $units = $application->units()->get();
            $result = ['modules' => 0, 'documents' => 0, 'skipped' => 0];
            foreach ($documents as $folder => $files) {
                $matches = $units->filter(fn ($unit) => $this->key($unit->name) === $this->key($folder));
                if ($matches->count() > 1) {
                    throw new RuntimeException('Multiple modules match '.$folder.'. Resolve them before importing.');
                }
                $unit = $matches->first();
                if (!$unit) {
                    $unit = $application->units()->create(['name' => ucfirst($folder)]);
                    $units->push($unit);
                    $result['modules']++;
                }
                $existing = $unit->documentations()->get();
                foreach ($files as $file => $html) {
                    $title = pathinfo($file, PATHINFO_FILENAME);
                    if ($existing->contains(fn ($doc) => $this->key(preg_replace('/\.md$/i', '', $doc->title)) === $this->key($title))) {
                        $result['skipped']++;
                        continue;
                    }
                    $doc = $unit->documentations()->create([
                        'title' => $title === 'README' ? 'README' : ucfirst($title),
                        'content' => $html,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'status' => 'in_process',
                    ]);
                    DocumentationWorkerHistory::create([
                        'user_id' => $userId,
                        'application_id' => $application->id,
                        'unit_id' => $unit->id,
                        'documentation_id' => $doc->id,
                        'event' => 'documentation_created',
                        'metadata' => ['source' => 'modules.rar', 'file' => $folder.'/'.$file],
                        'occurred_at' => now(),
                    ]);
                    $existing->push($doc);
                    $result['documents']++;
                }
            }
            if ($result['documents'] > 0) {
                Storage::disk('public')->delete('documents/application_'.$application->id.'_all_documentations_v2.pdf');
            }
            return $result;
        });

        return $result;
    }

    private function key(string $value): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $value));
    }
}
