<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TranslationController extends Controller
{
    protected $languages = ['en', 'sq']; // Add your available languages here

    public function index()
    {
        $translations = [];
        $languages = $this->languages;

        // Load existing translations from each language file
        foreach ($languages as $lang) {
            $filePath = base_path("lang/$lang/words.php"); // Updated path to match your structure
            if (File::exists($filePath)) {
                $translations[$lang] = include $filePath;
            } else {
                $translations[$lang] = [];
            }
        }

        return view('admin.translations.index', compact('translations', 'languages'));
    }

    public function create()
    {
        $languages = $this->languages;
        return view('admin.translations.create', compact('languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'translations' => 'required|array',
            'translations.*' => 'required|string',
        ]);

        $key = $request->key;

        // Check if the key already exists
        foreach ($this->languages as $lang) {
            $filePath = base_path("lang/$lang/words.php"); // Updated path to match your structure
            $translations = File::exists($filePath) ? include $filePath : [];

            if (array_key_exists($key, $translations)) {
                return redirect()->back()->withErrors(['key' => __('ui.the_key_already_exists')]);
            }
        }

        // Add the translation to each language file
        foreach ($request->translations as $lang => $translation) {
            $filePath = base_path("lang/$lang/words.php"); // Updated path to match your structure

            // Ensure the directory exists
            if (!File::exists(dirname($filePath))) {
                File::makeDirectory(dirname($filePath), 0755, true);
            }

            $translations = File::exists($filePath) ? include $filePath : [];
            $translations[$key] = $translation;

            // Write the updated translations back to the file using [] syntax
            $this->writeTranslationsToFile($filePath, $translations);
        }

        return redirect()->route('admin.translations.index')->with('success', __('ui.translation_added_successfully'));
    }

    public function edit($key)
    {
        $translations = [];
        $languages = $this->languages;

        // Load existing translations for the specific key
        foreach ($languages as $lang) {
            $filePath = base_path("lang/$lang/words.php"); // Updated path to match your structure
            if (File::exists($filePath)) {
                $fileTranslations = include $filePath;
                $translations[$lang] = $fileTranslations[$key] ?? '';
            } else {
                $translations[$lang] = '';
            }
        }

        // Debugging: check if translations are being loaded correctly
        // dd($translations);

        return view('admin.translations.edit', compact('translations', 'key', 'languages'));
    }


    public function update(Request $request, $key)
    {
        $request->validate([
            'translations' => 'required|array',
            'translations.*' => 'required|string',
        ]);

        // Update the translation in each language file
        foreach ($request->translations as $lang => $translation) {
            $filePath = base_path("lang/$lang/words.php"); // Updated path to match your structure
            $translations = File::exists($filePath) ? include $filePath : [];
            $translations[$key] = $translation;

            // Write the updated translations back to the file using [] syntax
            $this->writeTranslationsToFile($filePath, $translations);
        }

        return redirect()->route('admin.translations.index')->with('success', __('ui.translation_updated_successfully'));
    }

    protected function writeTranslationsToFile($filePath, $translations)
    {
        // Convert the translations array to a PHP code string using [] syntax
        $content = "<?php\n\nreturn [\n";
        foreach ($translations as $key => $value) {
            $content .= "    '" . addslashes($key) . "' => '" . addslashes($value) . "',\n";
        }
        $content .= "];\n";

        // Write the content to the specified file path
        File::put($filePath, $content);
    }
}
