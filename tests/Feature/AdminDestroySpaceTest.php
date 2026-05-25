<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\AdminController;
use App\Models\Branch;
use App\Models\Node;
use App\Models\NodeType;
use App\Models\PhysicalSpace;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDestroySpaceTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-tenant-space-'.uniqid('', true).'.sqlite');

        if (file_exists($this->sqlitePath)) {
            unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);
        config()->set('database.connections.sqlite.foreign_key_constraints', true);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Artisan::call('migrate:fresh', [
            '--database' => 'sqlite',
            '--path' => database_path('migrations/tenant'),
            '--realpath' => true,
            '--force' => true,
        ]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_destroy_space_unassigns_nodes_before_delete(): void
    {
        $branch = Branch::query()->create([
            'name' => 'QA Branch',
        ]);

        $nodeType = NodeType::query()->create([
            'name' => 'Switch',
            'slug' => 'switch',
        ]);

        $space = PhysicalSpace::query()->create([
            'branch_id' => $branch->id,
            'name' => 'IDF Room',
            'space_type' => 'idf',
        ]);

        $node = Node::query()->create([
            'branch_id' => $branch->id,
            'node_type_id' => $nodeType->id,
            'physical_space_id' => $space->id,
            'name' => 'Core Switch',
            'status' => 'active',
        ]);

        $response = app(AdminController::class)->destroySpace($space);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertDatabaseMissing('physical_spaces', ['id' => $space->id], 'sqlite');

        $node->refresh();
        $this->assertNull($node->physical_space_id);
        $this->assertDatabaseHas('nodes', [
            'id' => $node->id,
            'physical_space_id' => null,
        ], 'sqlite');
    }
}
