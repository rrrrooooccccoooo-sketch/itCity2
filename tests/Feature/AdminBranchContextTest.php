<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\AdminController;
use App\Models\Branch;
use App\Models\ComputerAsset;
use App\Models\InvoiceVendorProfile;
use App\Models\InvoiceVendorProfileAudit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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

    public function test_store_computer_asset_saves_purchase_order_number(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $request = $this->makeRequest('/admin/computer-assets', [
            'asset_branch_id' => (string) $andares->id,
            'asset_equipment_type' => 'desktop',
            'asset_status' => 'pending_assignment',
            'asset_hostname' => 'pc-oc-01',
            'asset_purchase_order_number' => 'OC-2026-001',
        ], $user, [], 'POST');

        $response = app(AdminController::class)->storeComputerAsset($request);

        $this->assertInstanceOf(
            \Illuminate\Http\RedirectResponse::class,
            $response
        );

        $asset = ComputerAsset::query()->where('hostname', 'pc-oc-01')->first();
        $this->assertNotNull($asset);
        $this->assertSame('OC-2026-001', data_get($asset?->details, 'procurement.purchase_order_number'));
        $this->assertSame('pending_assignment', $asset?->status);
    }

    public function test_bulk_import_creates_pending_assignment_assets_from_csv(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $csv = implode("\n", [
            'branch_id,equipment_type,asset_tag,hostname,serial_number,brand,model,cpu,ram_gb,storage_type,storage_gb,operating_system,office_version,purchase_order_number,supplier,purchase_date,warranty_expires_at,notes',
            $andares->id . ',desktop,INV-2001,pc-import-01,SN-2001,Dell,OptiPlex 7010,Intel Core i5,16,ssd,512,Windows 11 Pro,Microsoft 365 Apps,OC-2026-2001,Proveedor Demo,2026-05-01,2027-05-01,Importado desde layout',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/import', [
            'asset_import_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_import_file' => UploadedFile::fake()->createWithContent('assets.csv', $csv),
        ]);

        $response = app(AdminController::class)->importComputerAssets($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $asset = ComputerAsset::query()->where('hostname', 'pc-import-01')->first();
        $this->assertNotNull($asset);
        $this->assertSame('pending_assignment', $asset?->status);
        $this->assertSame('OC-2026-2001', data_get($asset?->details, 'procurement.purchase_order_number'));
        $this->assertSame('Proveedor Demo', data_get($asset?->details, 'procurement.supplier'));
    }

    public function test_invoice_draft_import_creates_pending_assignment_assets(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $payload = [
            'file_name' => 'factura-demo.pdf',
            'supplier' => 'Proveedor IA',
            'invoice_folio' => 'FAC-2026-998',
            'purchase_order_number' => 'OC-2026-998',
            'invoice_date' => '2026-05-26',
            'items' => [
                [
                    'branch_id' => $andares->id,
                    'equipment_type' => 'laptop',
                    'description' => 'Laptop Dell Latitude 5440',
                    'serial_number' => 'SER-5440-01',
                    'brand' => 'Dell',
                    'model' => 'Latitude 5440',
                    'confidence' => 0.88,
                ],
            ],
        ];

        $request = $this->makeRequest('/admin/computer-assets/invoice/import', [
            'asset_invoice_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST');

        $response = app(AdminController::class)->importComputerAssetsFromInvoiceDraft($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $asset = ComputerAsset::query()->where('serial_number', 'SER-5440-01')->first();
        $this->assertNotNull($asset);
        $this->assertSame('pending_assignment', $asset?->status);
        $this->assertSame('OC-2026-998', data_get($asset?->details, 'procurement.purchase_order_number'));
        $this->assertSame('FAC-2026-998', data_get($asset?->details, 'procurement.invoice_folio'));
        $this->assertSame('invoice_ai_mvp', data_get($asset?->details, 'import.source'));
    }

    public function test_invoice_analyzer_detects_serials_from_nombre_column_with_commas(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $txt = implode("\n", [
            'Proveedor: Tech Supplier SA de CV',
            'Folio Factura: FAC-2026-120',
            'Orden de compra: OC-2026-120',
            'Nombre, Cantidad',
            'Dell Latitude 5440, SERIE: ABCD1234, EFGH5678, 2',
            'Subtotal: 10000',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/analyze', [
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_invoice_file' => UploadedFile::fake()->createWithContent('factura.txt', $txt),
        ]);

        $response = app(AdminController::class)->analyzeComputerAssetInvoice($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $draft = $response->getSession()?->get('assetInvoiceDraft');
        $this->assertIsArray($draft);
        $this->assertSame('OC-2026-120', data_get($draft, 'purchase_order_number'));
        $this->assertCount(2, data_get($draft, 'items', []));
        $this->assertSame('ABCD1234', data_get($draft, 'items.0.serial_number'));
        $this->assertSame('EFGH5678', data_get($draft, 'items.1.serial_number'));
        $this->assertSame('validada', data_get($draft, 'items.0.serial_status'));
    }

    public function test_invoice_analyzer_ignores_specs_and_keeps_consistent_length_serials(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $txt = implode("\n", [
            'Proveedor: Equipo Demo',
            'Nombre, Cantidad',
            'HP ProBook 440 G9, Intel Core i7, 16GB RAM, SSD 512GB, ZXCV123456, QWER654321, 2',
            'Total: 100',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/analyze', [
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_invoice_file' => UploadedFile::fake()->createWithContent('factura-especificaciones.txt', $txt),
        ]);

        $response = app(AdminController::class)->analyzeComputerAssetInvoice($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $draft = $response->getSession()?->get('assetInvoiceDraft');
        $this->assertIsArray($draft);
        $this->assertCount(2, data_get($draft, 'items', []));
        $serials = collect(data_get($draft, 'items', []))->pluck('serial_number')->values()->all();
        $this->assertSame(['ZXCV123456', 'QWER654321'], $serials);
    }

    public function test_invoice_analyzer_keeps_mixed_length_serials_after_marker(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $txt = implode("\n", [
            'Proveedor: Equipo Demo',
            'Nombre, Cantidad',
            'Lenovo ThinkPad T14, SERIE: A1B2C3D4, XX-1234567890, ABCD-12345678-XY, 3',
            'Total: 100',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/analyze', [
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_invoice_file' => UploadedFile::fake()->createWithContent('factura-mixta.txt', $txt),
        ]);

        $response = app(AdminController::class)->analyzeComputerAssetInvoice($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $draft = $response->getSession()?->get('assetInvoiceDraft');
        $this->assertIsArray($draft);
        $serials = collect(data_get($draft, 'items', []))->pluck('serial_number')->values()->all();
        $this->assertSame(['A1B2C3D4', 'XX-1234567890', 'ABCD-12345678-XY'], $serials);
        $this->assertTrue(collect(data_get($draft, 'items', []))->every(fn ($item) => data_get($item, 'serial_status') === 'validada'));
    }

    public function test_invoice_analyzer_detects_serials_from_pipe_separated_table_rows(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $txt = implode("\n", [
            'Proveedor: Tabla Demo',
            'Nombre | Cantidad | Series',
            'Dell OptiPlex 7010 | 2 | SERIE: PPLC123456, PPLC654321',
            'Total | 2 |',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/analyze', [
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_invoice_file' => UploadedFile::fake()->createWithContent('factura-tabla-pipe.txt', $txt),
        ]);

        $response = app(AdminController::class)->analyzeComputerAssetInvoice($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $draft = $response->getSession()?->get('assetInvoiceDraft');
        $this->assertIsArray($draft);
        $serials = collect(data_get($draft, 'items', []))->pluck('serial_number')->values()->all();
        $this->assertSame(['PPLC123456', 'PPLC654321'], $serials);
    }

    public function test_invoice_analyzer_detects_serials_when_they_are_in_the_next_line(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $txt = implode("\n", [
            'Proveedor: Tabla Demo',
            'Nombre | Cantidad',
            'Dell OptiPlex 7010 | 2',
            'SERIE: PPLC123456 PPLC654321',
            'Total | 2',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/analyze', [
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_invoice_file' => UploadedFile::fake()->createWithContent('factura-series-siguiente-linea.txt', $txt),
        ]);

        $response = app(AdminController::class)->analyzeComputerAssetInvoice($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $draft = $response->getSession()?->get('assetInvoiceDraft');
        $this->assertIsArray($draft);
        $this->assertCount(2, data_get($draft, 'items', []));
        $serials = collect(data_get($draft, 'items', []))->pluck('serial_number')->values()->all();
        $this->assertSame(['PPLC123456', 'PPLC654321'], $serials);
    }

    public function test_invoice_analyzer_detects_serials_after_series_marker_in_english_plural(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $txt = implode("\n", [
            'Proveedor: Tabla Demo',
            'Nombre | Cantidad | Series',
            'Lenovo ThinkPad T14 | 3 | Series: A1B2C3D4, XX-1234567890, ABCD-12345678-XY',
            'Total | 3 |',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/analyze', [
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_invoice_file' => UploadedFile::fake()->createWithContent('factura-series-english-plural.txt', $txt),
        ]);

        $response = app(AdminController::class)->analyzeComputerAssetInvoice($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $draft = $response->getSession()?->get('assetInvoiceDraft');
        $this->assertIsArray($draft);
        $this->assertCount(3, data_get($draft, 'items', []));
        $serials = collect(data_get($draft, 'items', []))->pluck('serial_number')->values()->all();
        $this->assertSame(['A1B2C3D4', 'XX-1234567890', 'ABCD-12345678-XY'], $serials);
    }

    public function test_invoice_analyzer_collects_remaining_serials_from_following_lines_until_quantity_is_met(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $txt = implode("\n", [
            'Proveedor: Tabla Demo',
            'Nombre | Cantidad | Series',
            'Dell Latitude 5440 | 4 | Series: ABCD1234,',
            'EFGH5678, IJKL9012, MNOP3456',
            'Total | 4 |',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/analyze', [
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_invoice_file' => UploadedFile::fake()->createWithContent('factura-series-multilinea-cantidad.txt', $txt),
        ]);

        $response = app(AdminController::class)->analyzeComputerAssetInvoice($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $draft = $response->getSession()?->get('assetInvoiceDraft');
        $this->assertIsArray($draft);
        $this->assertCount(4, data_get($draft, 'items', []));
        $serials = collect(data_get($draft, 'items', []))->pluck('serial_number')->values()->all();
        $this->assertSame(['ABCD1234', 'EFGH5678', 'IJKL9012', 'MNOP3456'], $serials);
    }

    public function test_invoice_analyzer_extracts_all_serials_from_generic_series_block_without_nombre_header(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $txt = implode("\n", [
            'Proveedor: Demo Supplies',
            'Factura: FAC-2026-9001',
            'Series: PF5MMQJ6, PF5MMQJ7, PF5MMQJ8, PF5MMQJ9, PF5MMQK0,',
            'PF5MMQK1, PF5MMQK2, PF5MMQK3, PF5MMQK4, PF5MMQK5',
            'Total: 10',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/analyze', [
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_invoice_file' => UploadedFile::fake()->createWithContent('factura-series-bloque-generico.txt', $txt),
        ]);

        $response = app(AdminController::class)->analyzeComputerAssetInvoice($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $draft = $response->getSession()?->get('assetInvoiceDraft');
        $this->assertIsArray($draft);
        $this->assertCount(10, data_get($draft, 'items', []));
        $serials = collect(data_get($draft, 'items', []))->pluck('serial_number')->values()->all();
        $this->assertSame([
            'PF5MMQJ6',
            'PF5MMQJ7',
            'PF5MMQJ8',
            'PF5MMQJ9',
            'PF5MMQK0',
            'PF5MMQK1',
            'PF5MMQK2',
            'PF5MMQK3',
            'PF5MMQK4',
            'PF5MMQK5',
        ], $serials);
    }

    public function test_invoice_analyzer_infers_description_brand_and_model_from_context_when_using_series_block_fallback(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $txt = implode("\n", [
            'Proveedor: Demo Supplies',
            'Equipo: Dell Latitude 5440',
            'Factura: FAC-2026-9010',
            'Series: PF5MMQJ6, PF5MMQJ7, PF5MMQJ8',
            'Total: 3',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/analyze', [
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_invoice_file' => UploadedFile::fake()->createWithContent('factura-series-con-contexto-equipo.txt', $txt),
        ]);

        $response = app(AdminController::class)->analyzeComputerAssetInvoice($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $draft = $response->getSession()?->get('assetInvoiceDraft');
        $this->assertIsArray($draft);
        $this->assertCount(3, data_get($draft, 'items', []));
        $this->assertSame('Dell', data_get($draft, 'items.0.brand'));
        $this->assertSame('Latitude 5440', data_get($draft, 'items.0.model'));
        $this->assertSame('Equipo: Dell Latitude 5440', data_get($draft, 'items.0.description'));
        $this->assertSame('alta', data_get($draft, 'items.0.field_confidence.brand.status'));
        $this->assertSame('alta', data_get($draft, 'items.0.field_confidence.model.status'));
    }

    public function test_invoice_analyzer_extracts_model_family_from_noisy_context_line(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $txt = implode("\n", [
            'Proveedor: Demo Supplies',
            'Descripcion: Lenovo ThinkPad T14 Gen 3 Intel Core i7 16GB RAM SSD 512GB',
            'Factura: FAC-2026-9011',
            'Series: LNVX100001, LNVX100002',
            'Total: 2',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/analyze', [
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_invoice_file' => UploadedFile::fake()->createWithContent('factura-series-contexto-ruido-thinkpad.txt', $txt),
        ]);

        $response = app(AdminController::class)->analyzeComputerAssetInvoice($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $draft = $response->getSession()?->get('assetInvoiceDraft');
        $this->assertIsArray($draft);
        $this->assertCount(2, data_get($draft, 'items', []));
        $this->assertSame('Lenovo', data_get($draft, 'items.0.brand'));
        $this->assertSame('Thinkpad T14', data_get($draft, 'items.0.model'));
    }

    public function test_invoice_analyzer_extracts_hp_compact_model_when_family_word_is_missing(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $txt = implode("\n", [
            'Proveedor: Demo Supplies',
            'Equipo: HP 240 G9 Intel Core i5 8GB RAM SSD 256GB',
            'Factura: FAC-2026-9012',
            'Series: HPG900001, HPG900002',
            'Total: 2',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/analyze', [
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_invoice_file' => UploadedFile::fake()->createWithContent('factura-series-contexto-hp-240g9.txt', $txt),
        ]);

        $response = app(AdminController::class)->analyzeComputerAssetInvoice($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $draft = $response->getSession()?->get('assetInvoiceDraft');
        $this->assertIsArray($draft);
        $this->assertCount(2, data_get($draft, 'items', []));
        $this->assertSame('HP', data_get($draft, 'items.0.brand'));
        $this->assertSame('240 G9', data_get($draft, 'items.0.model'));
    }

    public function test_invoice_analyzer_infers_laptop_type_from_enterprise_family_name(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $txt = implode("\n", [
            'Proveedor: Demo Supplies',
            'Equipo: Lenovo ThinkPad T14 Gen 3',
            'Series: LNVX300001, LNVX300002',
            'Total: 2',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/analyze', [
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_invoice_file' => UploadedFile::fake()->createWithContent('factura-tipo-thinkpad.txt', $txt),
        ]);

        $response = app(AdminController::class)->analyzeComputerAssetInvoice($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $draft = $response->getSession()?->get('assetInvoiceDraft');
        $this->assertIsArray($draft);
        $this->assertSame('laptop', data_get($draft, 'items.0.equipment_type'));
    }

    public function test_invoice_analyzer_infers_desktop_type_from_optiplex_family_name(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $txt = implode("\n", [
            'Proveedor: Demo Supplies',
            'Equipo: Dell OptiPlex 7010',
            'Series: DELL700001, DELL700002',
            'Total: 2',
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/analyze', [
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST', [
            'asset_invoice_file' => UploadedFile::fake()->createWithContent('factura-tipo-optiplex.txt', $txt),
        ]);

        $response = app(AdminController::class)->analyzeComputerAssetInvoice($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $draft = $response->getSession()?->get('assetInvoiceDraft');
        $this->assertIsArray($draft);
        $this->assertSame('desktop', data_get($draft, 'items.0.equipment_type'));
    }

    public function test_invoice_import_learns_supplier_profile(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        $payload = [
            'file_name' => 'factura-aprendizaje.pdf',
            'supplier' => 'Proveedor Aprendizaje SA',
            'invoice_folio' => 'FAC-2026-777',
            'purchase_order_number' => 'OC-2026-777',
            'invoice_date' => '2026-05-26',
            'items' => [
                [
                    'branch_id' => $andares->id,
                    'equipment_type' => 'desktop',
                    'description' => 'Dell OptiPlex 7010',
                    'serial_number' => 'PPLC123456',
                    'brand' => 'Dell',
                    'model' => 'OptiPlex 7010',
                    'confidence' => 0.9,
                ],
                [
                    'branch_id' => $andares->id,
                    'equipment_type' => 'laptop',
                    'description' => 'Lenovo ThinkPad T14',
                    'serial_number' => 'LNVX998877',
                    'brand' => 'Lenovo',
                    'model' => 'ThinkPad T14',
                    'confidence' => 0.9,
                ],
            ],
        ];

        $request = $this->makeRequest('/admin/computer-assets/invoice/import', [
            'asset_invoice_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'asset_invoice_branch_id' => (string) $andares->id,
        ], $user, [], 'POST');

        $response = app(AdminController::class)->importComputerAssetsFromInvoiceDraft($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $profile = InvoiceVendorProfile::query()->where('supplier_name', 'Proveedor Aprendizaje SA')->first();
        $this->assertNotNull($profile);
        $this->assertContains('Dell', $profile?->known_brands ?? []);
        $this->assertContains('Lenovo', $profile?->known_brands ?? []);
        $this->assertContains('PPLC', $profile?->serial_prefixes ?? []);
        $this->assertContains('LNVX', $profile?->serial_prefixes ?? []);
        $this->assertDatabaseHas('invoice_vendor_profile_audits', [
            'supplier_key' => 'proveedor-aprendizaje-sa',
            'action' => 'created_from_import',
        ]);
    }

    public function test_invoice_vendor_profile_can_be_reset_manually(): void
    {
        [, $andares] = $this->createBranches();
        $user = $this->createAdminUser($andares->id);

        InvoiceVendorProfile::query()->create([
            'supplier_key' => 'proveedor-aprendizaje-sa',
            'supplier_name' => 'Proveedor Aprendizaje SA',
            'known_brands' => ['Dell'],
            'known_models' => ['OptiPlex 7010'],
            'serial_prefixes' => ['PPLC'],
            'last_used_at' => now(),
        ]);

        $request = $this->makeRequest('/admin/computer-assets/invoice/vendor-profile/reset', [
            'asset_invoice_supplier_name' => 'Proveedor Aprendizaje SA',
        ], $user, [], 'POST');

        $response = app(AdminController::class)->resetInvoiceVendorProfile($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertDatabaseCount('invoice_vendor_profiles', 0);
        $this->assertDatabaseHas('invoice_vendor_profile_audits', [
            'supplier_key' => 'proveedor-aprendizaje-sa',
            'action' => 'reset',
        ]);
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

    private function makeRequest(string $uri, array $query, User $user, array $sessionData = [], string $method = 'GET', array $files = []): Request
    {
        $request = Request::create($uri, $method, $query, [], $files);
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