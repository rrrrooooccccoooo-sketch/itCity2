<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\AdminController;
use App\Models\Branch;
use App\Models\ComputerAsset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Session\Store;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class AdminBranchContextTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-admin-branch-context-'.uniqid('', true).'.sqlite');

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

    public function test_users_keeps_explicit_andares_context_even_with_old_portal_session(): void
    {
        [$tepic, $andares] = $this->createBranches();
        $user = $this->createAdminUser($tepic->id);

        $request = $this->makeRequest('/admin/users', [
            'branch_id' => (string) $andares->id,
        ], $user, [
            'tenant_portal_context_branch_id' => $tepic->id,
            'tenant_admin_context_branch_id' => $andares->id,
        ]);

        $response = app(AdminController::class)->indexUsers($request);

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertSame($andares->id, $response->getData()['currentContextBranchId']);
        $this->assertSame($andares->id, $request->session()->get('tenant_admin_context_branch_id'));
    }

    public function test_inventory_keeps_andares_assets_when_old_portal_session_points_to_tepic(): void
    {
        [$tepic, $andares] = $this->createBranches();
        $user = $this->createAdminUser($tepic->id);

        ComputerAsset::query()->create([
            'branch_id' => $andares->id,
            'equipment_type' => 'desktop',
            'status' => 'in_use',
            'hostname' => 'andares-pc-01',
        ]);
        ComputerAsset::query()->create([
            'branch_id' => $andares->id,
            'equipment_type' => 'desktop',
            'status' => 'in_use',
            'hostname' => 'andares-pc-02',
        ]);
        ComputerAsset::query()->create([
            'branch_id' => $andares->id,
            'equipment_type' => 'laptop',
            'status' => 'in_use',
            'hostname' => 'andares-pc-03',
        ]);
        ComputerAsset::query()->create([
            'branch_id' => $tepic->id,
            'equipment_type' => 'desktop',
            'status' => 'in_use',
            'hostname' => 'tepic-pc-01',
        ]);

        $request = $this->makeRequest('/admin', [
            'branch_id' => (string) $andares->id,
        ], $user, [
            'tenant_portal_context_branch_id' => $tepic->id,
            'tenant_admin_context_branch_id' => $andares->id,
        ]);

        $response = app(AdminController::class)->dashboardPanel1($request);

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertSame($andares->id, $response->getData()['currentContextBranchId']);
        $this->assertCount(3, $response->getData()['computerAssets']);
        $this->assertTrue(
            collect($response->getData()['computerAssets'])->every(fn (ComputerAsset $asset) => (int) $asset->branch_id === (int) $andares->id)
        );

        $html = $response->render();
        $this->assertStringContainsString('/admin/users?branch_id=' . $andares->id, $html);
    }

    public function test_users_view_renders_back_link_with_andares_context(): void
    {
        [$tepic, $andares] = $this->createBranches();
        $user = $this->createAdminUser($tepic->id);

        $request = $this->makeRequest('/admin/users', [
            'branch_id' => (string) $andares->id,
        ], $user, [
            'tenant_portal_context_branch_id' => $tepic->id,
            'tenant_admin_context_branch_id' => $andares->id,
        ]);

        $response = app(AdminController::class)->indexUsers($request);

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $html = $response->render();
        $this->assertStringContainsString('/admin?branch_id=' . $andares->id, $html);
    }

    public function test_branch_page_sidebar_prefers_current_branch_over_stale_admin_session(): void
    {
        [$corporativo, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $request = $this->makeRequest('/sede/' . $corporativo->id, [], $user, [
            'tenant_portal_context_branch_id' => $corporativo->id,
            'tenant_admin_context_branch_id' => $andares->id,
        ]);

        app()->instance('request', $request);

        $html = Blade::render(
            <<<'BLADE'
@extends('tenant.layouts.app')

@section('title', $branch->name)
@section('page_title', $branch->name)
@section('content')
<div>Branch page</div>
@endsection
BLADE,
            [
                'branch' => $corporativo,
                'path' => 'sede/' . $corporativo->id,
                'navPortal' => '',
                'navBranch' => 'active',
                'navNetwork' => '',
                'navAdmin' => '',
                'navNode' => '',
            ]
        );

        $this->assertStringContainsString('/admin?branch_id=' . $corporativo->id, $html);
    }

    /**
     * @return array{0: Branch, 1: Branch}
     */
    private function createBranches(): array
    {
        $tepic = Branch::query()->create(['name' => 'Tepic']);
        $andares = Branch::query()->create(['name' => 'Andares']);

        return [$tepic, $andares];
    }

    private function createAdminUser(?int $branchId = null): User
    {
        return User::query()->create([
            'name' => 'Admin QA',
            'email' => 'admin.qa@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'branch_id' => $branchId,
            'auth_source' => 'local',
            'is_active' => true,
        ]);
    }

    private function makeRequest(string $uri, array $query, User $user, array $sessionData = []): Request
    {
        $request = Request::create($uri, 'GET', $query);
        $request->setLaravelSession($this->makeSession($sessionData));
        $request->setUserResolver(fn () => $user);
        Auth::setUser($user);
        app('view')->share('errors', new ViewErrorBag());

        return $request;
    }

    private function makeSession(array $data = []): Store
    {
        $session = new Store('testing', new ArraySessionHandler(120));
        $session->start();

        foreach ($data as $key => $value) {
            $session->put($key, $value);
        }

        return $session;
    }
}