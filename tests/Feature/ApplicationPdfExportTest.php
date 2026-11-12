<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Documentation;
use App\Services\ApplicationPdfExport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use PDF;
use Sentinel;
use Tests\TestCase;

class ApplicationPdfExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'cache.stores.file.driver' => 'array']);
        DB::purge('sqlite');
        Storage::fake('local');
        // The global dashboard composer is unrelated to PDF exports.
        $this->app['events']->forget('composing: *');
        Schema::create('applications', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->timestamps();
        });
        Schema::create('units', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('application_id'); $table->string('name'); $table->timestamps();
        });
        Schema::create('documentations', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('unit_id'); $table->string('title');
            $table->longText('content'); $table->timestamps();
        });
        DB::table('applications')->insert([['id' => 50, 'name' => 'TestDocu'], ['id' => 51, 'name' => 'Other']]);
        DB::table('units')->insert([
            ['id' => 1, 'application_id' => 50, 'name' => 'Auth'],
            ['id' => 2, 'application_id' => 50, 'name' => 'Empty module'],
            ['id' => 3, 'application_id' => 51, 'name' => 'Private'],
        ]);
        DB::table('documentations')->insert([
            ['id' => 1, 'unit_id' => 1, 'title' => 'README', 'content' => '<h1>Authentication</h1><p>Example documentation.</p>'],
            ['id' => 2, 'unit_id' => 3, 'title' => 'Private', 'content' => '<p>Other project</p>'],
        ]);
        Sentinel::shouldReceive('check')->andReturn(false);
        Sentinel::shouldReceive('getUser')->andReturn(new \App\Models\User)->byDefault();
        Sentinel::shouldReceive('inRole')->with('admin')->andReturn(true);
    }

    public function test_first_request_returns_progress_without_rendering_a_pdf(): void
    {
        PDF::shouldReceive('loadView')->never();
        $this->get('/applications/50/pdf')->assertOk()->assertSee('pdf-progress');
        $manifest = $this->getJson('/applications/50/pdf?manifest=1')->assertOk()->json();
        $this->assertCount(2, $manifest['parts']);
        $this->assertSame(['Auth / README', 'Empty module'], array_column($manifest['parts'], 'label'));
        $this->assertStringNotContainsString('Example documentation', json_encode($manifest));
    }

    public function test_part_renders_a_real_pdf_and_reuses_the_private_cache(): void
    {
        $part = (new ApplicationPdfExport)->manifest(Application::findOrFail(50))['parts'][0];
        $response = $this->get($part['url'])->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $path = $response->baseResponse->getFile()->getPathname();
        $this->assertStringStartsWith('%PDF-', file_get_contents($path));
        $this->assertGreaterThan(1000, filesize($path));
        PDF::shouldReceive('loadView')->never();
        $this->get($part['url'])->assertOk();
    }

    public function test_content_changes_reject_stale_part_urls_and_change_the_manifest(): void
    {
        $export = new ApplicationPdfExport;
        $project = Application::findOrFail(50);
        $before = $export->manifest($project);
        Documentation::findOrFail(1)->update(['content' => '<p>Changed</p>']);
        $after = $export->manifest($project);
        $this->assertNotSame($before['revision'], $after['revision']);
        $this->getJson($before['parts'][0]['url'])->assertStatus(409);
        DB::table('documentations')->insert(['id' => 3, 'unit_id' => 1, 'title' => 'New', 'content' => 'New']);
        $this->assertNotSame($after['revision'], $export->manifest($project)['revision']);
    }

    public function test_part_cannot_read_a_unit_or_document_from_another_project(): void
    {
        PDF::shouldReceive('loadView')->never();
        $this->getJson('/applications/50/pdf/parts/3/2?revision=anything')->assertNotFound();
        $this->getJson('/applications/50/pdf/parts/1/2?revision=anything')->assertNotFound();
    }

    public function test_exports_and_cached_parts_require_permission(): void
    {
        Sentinel::shouldReceive('getUser')->andReturn(null);
        $this->getJson('/applications/50/pdf')->assertForbidden();
        $this->getJson('/applications/50/pdf/parts/1/1?revision=anything')->assertForbidden();
    }

    public function test_empty_project_has_a_cover_part(): void
    {
        DB::table('applications')->insert(['id' => 52, 'name' => 'Empty project']);
        $manifest = (new ApplicationPdfExport)->manifest(Application::findOrFail(52));
        $this->assertCount(1, $manifest['parts']);
        $this->get($manifest['parts'][0]['url'])->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_large_imported_document_renders_as_a_separate_part(): void
    {
        $source = file_get_contents(resource_path('imports/TestDocu/modules/settings-system/README.md'));
        Documentation::findOrFail(1)->update([
            'content' => (string) \Illuminate\Support\Str::markdown($source),
        ]);
        $part = (new ApplicationPdfExport)->manifest(Application::findOrFail(50))['parts'][0];
        $response = $this->get($part['url'])->assertOk();
        $bytes = file_get_contents($response->baseResponse->getFile()->getPathname());
        $this->assertStringStartsWith('%PDF-', $bytes);
        $this->assertGreaterThan(1, preg_match_all('#/Type\s*/Page\b#', $bytes));
    }
}
