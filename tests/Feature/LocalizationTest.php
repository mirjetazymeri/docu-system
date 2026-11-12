<?php

namespace Tests\Feature;

use App\Http\Middleware\ApplySelectedLocale;
use App\Http\Requests\ApplicationTestingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_every_translation_has_both_languages_and_matching_parameters(): void
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(lang_path('en'))) as $file) {
            if ($file->getExtension() !== 'php') continue;
            $relative = substr($file->getPathname(), strlen(lang_path('en')) + 1);
            $this->assertFileExists(lang_path('sq/'.$relative));
            $en = Arr::dot(require $file->getPathname());
            $sq = Arr::dot(require lang_path('sq/'.$relative));
            $this->assertEqualsCanonicalizing(array_keys($en), array_keys($sq), $relative);
            foreach ($en as $key => $value) {
                if (!is_string($value)) continue;
                $this->assertNotSame('', trim($sq[$key]), "$relative.$key");
                preg_match_all('/:[a-zA-Z_][a-zA-Z_0-9]*/', $value, $englishParameters);
                preg_match_all('/:[a-zA-Z_][a-zA-Z_0-9]*/', $sq[$key], $albanianParameters);
                $this->assertEqualsCanonicalizing($englishParameters[0], $albanianParameters[0], "$relative.$key");
            }
        }
    }

    public function test_all_literal_application_translation_keys_exist_without_fallback(): void
    {
        foreach ([resource_path('views'), app_path('Http')] as $directory) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
                if ($file->getExtension() !== 'php') continue;
                preg_match_all('/(?:__|trans|trans_choice|@lang)\(\s*([\x27\x22])([^\x27\x22]+)\1\s*[,)]/', file_get_contents($file->getPathname()), $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    if (str_contains($match[2], '::')) continue;
                    foreach (['en', 'sq'] as $locale) {
                        $this->assertTrue($this->app['translator']->hasForLocale($match[2], $locale), "$locale: {$match[2]} in {$file->getPathname()}");
                    }
                }
            }
        }
    }

    public function test_saved_locale_applies_to_form_posts_and_explicit_urls_override_it(): void
    {
        foreach ([['POST', '/admin/application_testing', 'en', 'en'], ['PATCH', '/admin/application_testing/1', 'sq', 'sq'], ['GET', '/en/applications', 'sq', 'en'], ['GET', '/sq/applications', 'en', 'sq']] as [$method, $url, $saved, $expected]) {
            $request = Request::create($url, $method);
            $session = $this->app['session']->driver();
            $session->put('locale', $saved);
            $request->setLaravelSession($session);
            $response = (new ApplySelectedLocale)->handle($request, function () use ($expected) {
                $this->assertSame($expected, app()->getLocale());
                $validator = Validator::make(['testing_details' => [['comment' => str_repeat('a', 10001)]]], [
                    'testing_details.*.comment' => (new ApplicationTestingRequest)->rules()['testing_details.*.comment'],
                ]);
                $this->assertTrue($validator->fails());
                $message = $validator->errors()->first();
                $this->assertStringContainsString($expected === 'en' ? 'comment' : 'komenti', $message);
                $this->assertStringContainsString($expected === 'en' ? 'characters' : 'karaktere', $message);
                return response($message);
            });
            $this->assertSame($expected, $response->headers->get('Content-Language'));
        }
    }

    public function test_testing_form_and_dynamic_rows_render_in_the_selected_language(): void
    {
        $this->app['events']->forget('composing: *');
        foreach (['en', 'sq'] as $locale) {
            app()->setLocale($locale);
            $html = view('admin.application_testing._form', [
                'errors' => (new ViewErrorBag)->put('default', new MessageBag),
                'applications' => collect(), 'units' => collect(),
                'selectedApplicationId' => '', 'testerName' => '', 'version' => '', 'testDate' => '',
                'detailRows' => [['category' => 'FUNCTIONALITY', 'result' => 'not_passed', 'test_case' => '<script>alert(1)</script>', 'comment' => 'User supplied text']],
                'submitLabel' => __('ui.save_testing'),
            ])->render();
            $this->assertStringContainsString($locale === 'en' ? 'Select module' : 'Zgjidh modulin', $html);
            $this->assertStringContainsString($locale === 'en' ? 'Not Passed' : 'Nuk kaloi', $html);
            $this->assertStringNotContainsString($locale === 'en' ? 'Zgjidh modulin' : '>Passed<', $html);
            $this->assertStringContainsString('value="FUNCTIONALITY"', $html);
            $this->assertStringContainsString('value="not_passed"', $html);
            $this->assertStringContainsString('maxlength="10000"', $html);
            $this->assertStringContainsString('&lt;script&gt;', $html);
            $this->assertStringContainsString('User supplied text', $html);
            $this->assertStringNotContainsString('ui.', strip_tags($html));
            $script = view('admin.application_testing._scripts', ['nextDetailIndex' => 1])->render();
            $this->assertStringNotContainsString('@js', $script);
            $this->assertStringContainsString($locale === 'en' ? 'Select module' : 'Zgjidh modulin', $script);
        }
    }

    public function test_pdf_cache_paths_are_separate_for_each_language_and_content_version(): void
    {
        $controller = new \App\Http\Controllers\DocumentationController;
        $method = new \ReflectionMethod($controller, 'getDocumentationPdfPath');
        $document = new \App\Models\Documentation(['title' => 'Test', 'content' => '<p>Content</p>']);
        $document->id = 1;
        app()->setLocale('en');
        $english = $method->invoke($controller, $document);
        app()->setLocale('sq');
        $albanian = $method->invoke($controller, $document);
        $this->assertNotSame($english, $albanian);
        $document->content = '<p>Changed</p>';
        $this->assertNotSame($albanian, $method->invoke($controller, $document));
    }
}
