<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Documentation;
use App\Services\TestDocuDocumentationImport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TestDocuDocumentationImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Storage::fake('public');
        Schema::create('applications', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->timestamps();
        });
        Schema::create('units', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('application_id'); $table->string('name'); $table->timestamps();
        });
        Schema::create('documentations', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('unit_id'); $table->string('title');
            $table->longText('content'); $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable(); $table->string('status'); $table->timestamps();
        });
        Schema::create('documentation_worker_histories', function (Blueprint $table) {
            $table->id(); $table->integer('user_id'); $table->integer('application_id');
            $table->integer('unit_id'); $table->integer('documentation_id');
            $table->string('event'); $table->text('metadata'); $table->timestamp('occurred_at'); $table->timestamps();
        });
        DB::table('applications')->insert(['id' => 50, 'name' => 'TestDocu']);
    }

    public function test_import_preserves_completed_modules_and_is_repeatable(): void
    {
        $project = Application::findOrFail(50);
        foreach (['Auth', 'Cart-Checkout', 'Catalog-products', 'Catalog-rules pricing'] as $name) {
            $unit = $project->units()->create(['name' => $name]);
            $unit->documentations()->create(['title' => 'README', 'content' => 'Original', 'status' => 'completed']);
        }
        $service = new TestDocuDocumentationImport;
        $this->assertSame(['modules' => 19, 'documents' => 203, 'skipped' => 0], $service->import($project, 1));
        $this->assertSame(23, $project->units()->count());
        $this->assertSame(4, Documentation::where('content', 'Original')->where('status', 'completed')->count());
        $this->assertSame(['modules' => 0, 'documents' => 0, 'skipped' => 203], $service->import($project, 1));
        $this->assertSame(203, DB::table('documentation_worker_histories')->count());
        $this->assertStringContainsString('<h1>', Documentation::where('content', '!=', 'Original')->where('title', 'README')->firstOrFail()->content);
    }

    public function test_existing_module_and_document_names_are_matched_without_overwriting(): void
    {
        $project = Application::findOrFail(50);
        $unit = $project->units()->create(['name' => 'Channels locales']);
        $doc = $unit->documentations()->create(['title' => 'api.md', 'content' => 'Hand edited', 'status' => 'completed']);
        $result = (new TestDocuDocumentationImport)->import($project, 1);
        $this->assertSame(['modules' => 18, 'documents' => 202, 'skipped' => 1], $result);
        $this->assertSame('Hand edited', $doc->fresh()->content);
    }

    public function test_wrong_project_is_rejected_without_writes(): void
    {
        $project = Application::findOrFail(50);
        $project->name = 'Different project';
        try {
            (new TestDocuDocumentationImport)->import($project, 1);
            $this->fail('Expected project guard');
        } catch (\RuntimeException $exception) {
            $this->assertSame(0, DB::table('units')->count());
        }
    }

    public function test_import_route_is_post_only_and_rejects_guests(): void
    {
        $route = app('router')->getRoutes()->getByName('applications.import-TestDocu-documentation');
        $this->assertSame(['POST'], $route->methods());
        $this->postJson('/applications/50/import-TestDocu-documentation')->assertForbidden();
        $this->assertSame(0, DB::table('units')->count());
    }

    public function test_ambiguous_modules_roll_back_the_entire_import(): void
    {
        $project = Application::findOrFail(50);
        $project->units()->create(['name' => 'Tax']);
        $project->units()->create(['name' => 'tax']);
        try {
            (new TestDocuDocumentationImport)->import($project, 1);
            $this->fail('Expected duplicate module guard');
        } catch (\RuntimeException $exception) {
            $this->assertSame(2, DB::table('units')->count());
            $this->assertSame(0, Documentation::count());
            $this->assertSame(0, DB::table('documentation_worker_histories')->count());
        }
    }
}
