<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Tenant\AdminController;
use App\Http\Controllers\Tenant\PortalController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'tenant.subscription',
])->group(function () {
    Route::get('/', [PortalController::class, 'city'])->name('tenant.city');
    Route::get('/red', [PortalController::class, 'globalTopology'])->name('tenant.topology');
    Route::post('/red/relacion', [PortalController::class, 'storeRelationJson']);
    Route::put('/red/relacion/{relation}', [PortalController::class, 'updateRelationJson']);
    Route::delete('/red/relacion/{relation}', [PortalController::class, 'destroyRelationJson']);
    Route::post('/red/nodos', [PortalController::class, 'storeNodeJson']);
    Route::get('/red/nodos/{node}', [PortalController::class, 'showNodeJson']);
    Route::put('/red/nodos/{node}', [PortalController::class, 'updateNodeJson']);
    Route::delete('/red/nodos/{node}', [PortalController::class, 'destroyNodeJson']);
    Route::get('/red/nodos/{node}/software', [PortalController::class, 'nodeSoftwareJson']);
    Route::post('/red/software', [PortalController::class, 'storeSoftwareJson']);
    Route::put('/red/software/{software}', [PortalController::class, 'updateSoftwareJson']);
    Route::delete('/red/software/{software}', [PortalController::class, 'destroySoftwareJson']);
    Route::post('/red/layout', [PortalController::class, 'saveGlobalLayout']);
    Route::get('/red/resumen', [PortalController::class, 'topologySummary'])->name('tenant.topology.summary');
    Route::get('/red/memoria-mnemotecnica/vista', [PortalController::class, 'mnemonicMemoryView'])->name('tenant.topology.mnemonic.memory.view');
    Route::get('/red/memoria-mnemotecnica', [PortalController::class, 'mnemonicMemory'])->name('tenant.topology.mnemonic.memory');
    Route::get('/sede/{branch}', [PortalController::class, 'branch'])->name('tenant.branch');
    Route::get('/sede/{branch}/red', [PortalController::class, 'network']);
    Route::post('/sede/{branch}/red/layout', [PortalController::class, 'saveNetworkLayout']);
    Route::get('/sede/{branch}/estado', [PortalController::class, 'branchNodeStatus']);
    Route::get('/nodos/{node}', [PortalController::class, 'node']);
    Route::get('/nodos/{node}/metricas', [PortalController::class, 'nodeMetrics'])->name('tenant.node.metrics');
    Route::get('/nodos/{node}/sondeo', [PortalController::class, 'nodeProbe'])->name('tenant.node.probe');
    Route::post('/agent/heartbeat', [AdminController::class, 'ingestAgentHeartbeat']);
    Route::post('/agent/network-observations', [AdminController::class, 'ingestAgentNetworkObservations']);

    Route::middleware('auth')->prefix('admin')->group(function () {
        Route::post('/my-signature', [AdminController::class, 'saveMySignature']);

        Route::middleware('tenant.can:topology.view')->group(function () {
            Route::get('/', [AdminController::class, 'dashboard']);
            Route::get('/panel-admin-legacy', [AdminController::class, 'dashboardLegacy']);
            Route::get('/panel-admin-1', [AdminController::class, 'dashboardPanel1']);
            Route::get('/panel-admin-2', [AdminController::class, 'dashboardPanel2']);
            Route::get('/panel-admin-3', [AdminController::class, 'dashboardPanel3']);
            Route::get('/floor-plans/{floorPlan}', [AdminController::class, 'floorPlanView']);
            Route::get('/floor-plans/{floorPlan}/data', [AdminController::class, 'floorPlanData']);
        });

        Route::middleware('tenant.can:monitoring.view')->group(function () {
            Route::get('/monitoring/overview', [AdminController::class, 'monitoringOverview']);
            Route::get('/monitoring/asset/{id}', [AdminController::class, 'monitoringAssetDetail']);
        });

        Route::middleware('tenant.can:inventory.view')->group(function () {
            Route::get('/responsiva/verify', [AdminController::class, 'verifyComputerAssetResponsiva']);
            Route::get('/computer-assets/{computerAsset}/responsiva/preview', [AdminController::class, 'previewComputerAssetResponsiva']);
            Route::get('/computer-assets/{computerAsset}/assignment-log', [AdminController::class, 'showComputerAssetAssignmentLog']);
        });

        Route::get('/users', [AdminController::class, 'indexUsers'])->middleware('tenant.can:users.view');
        Route::middleware('tenant.can:users.manage')->group(function () {
            Route::post('/users', [AdminController::class, 'storeUser']);
            Route::put('/users/{user}', [AdminController::class, 'updateUser']);
            Route::delete('/users/{user}', [AdminController::class, 'destroyUser']);
            Route::get('/users/ad-import', [AdminController::class, 'showAdImport'])->name('admin.ad-import');
            Route::post('/users/ad-import/fetch', [AdminController::class, 'fetchAdUsers']);
            Route::post('/users/ad-import/import', [AdminController::class, 'importAdUsers']);
        });
        Route::post('/users/{user}/send-reset-link', [AdminController::class, 'sendPasswordResetLink'])->middleware('tenant.can:users.reset');

        Route::middleware('tenant.can:tenant.admin')->group(function () {
            Route::get('/monitoring/agent-installer', [AdminController::class, 'downloadAgentInstaller']);
            Route::get('/monitoring/agent-installer-zip', [AdminController::class, 'downloadAgentInstallerZip']);
            Route::get('/monitoring/agent-installer-exe', [AdminController::class, 'downloadAgentInstallerExe']);
            Route::get('/monitoring/snmp-targets-template', [AdminController::class, 'downloadSnmpTargetsTemplate']);
        });

        Route::middleware('tenant.can:topology.manage')->group(function () {

            Route::post('/branches', [AdminController::class, 'storeBranch']);
            Route::put('/branches/{branch}', [AdminController::class, 'updateBranch']);
            Route::delete('/branches/{branch}', [AdminController::class, 'destroyBranch']);

            Route::post('/node-types', [AdminController::class, 'storeNodeType']);
            Route::put('/node-types/{nodeType}', [AdminController::class, 'updateNodeType']);
            Route::delete('/node-types/{nodeType}', [AdminController::class, 'destroyNodeType']);

            Route::post('/spaces', [AdminController::class, 'storeSpace']);
            Route::put('/spaces/{space}', [AdminController::class, 'updateSpace']);
            Route::delete('/spaces/{space}', [AdminController::class, 'destroySpace']);

            Route::post('/floor-plans', [AdminController::class, 'storeFloorPlan']);
            Route::post('/floor-plans/{floorPlan}/points', [AdminController::class, 'saveFloorPlanPoints']);
            Route::delete('/floor-plans/{floorPlan}', [AdminController::class, 'destroyFloorPlan']);

            Route::post('/nodes', [AdminController::class, 'storeNode']);
            Route::put('/nodes/{node}', [AdminController::class, 'updateNode']);
            Route::delete('/nodes/{node}', [AdminController::class, 'destroyNode']);

            Route::post('/software', [AdminController::class, 'storeSoftware']);
            Route::put('/software/{software}', [AdminController::class, 'updateSoftware']);
            Route::delete('/software/{software}', [AdminController::class, 'destroySoftware']);

            Route::post('/relations', [AdminController::class, 'storeRelation']);
            Route::put('/relations/{relation}', [AdminController::class, 'updateRelation']);
            Route::delete('/relations/{relation}', [AdminController::class, 'destroyRelation']);
        });

        Route::middleware('tenant.can:inventory.manage')->group(function () {

            Route::post('/computer-assets', [AdminController::class, 'storeComputerAsset']);
            Route::post('/computer-assets/{computerAsset}/reassign', [AdminController::class, 'reassignComputerAsset']);
            Route::post('/computer-assets/{computerAsset}/transfer-requests', [AdminController::class, 'requestComputerAssetTransfer']);
            Route::post('/computer-assets/transfer-requests/{transferRequest}/decision', [AdminController::class, 'decideComputerAssetTransferRequest']);
            Route::put('/computer-assets/{computerAsset}', [AdminController::class, 'updateComputerAsset']);
            Route::delete('/computer-assets/{computerAsset}', [AdminController::class, 'destroyComputerAsset']);
            Route::get('/computer-assets/{computerAsset}/responsiva', [AdminController::class, 'downloadComputerAssetResponsiva']);

            Route::post('/equipment-brands', [AdminController::class, 'storeEquipmentBrand']);
            Route::put('/equipment-brands/{brand}', [AdminController::class, 'updateEquipmentBrand']);
            Route::delete('/equipment-brands/{brand}', [AdminController::class, 'destroyEquipmentBrand']);

            Route::post('/equipment-models', [AdminController::class, 'storeEquipmentModel']);
            Route::put('/equipment-models/{equipmentModel}', [AdminController::class, 'updateEquipmentModel']);
            Route::delete('/equipment-models/{equipmentModel}', [AdminController::class, 'destroyEquipmentModel']);
        });

        Route::middleware('tenant.can:inventory.catalogs.manage')->group(function () {
            Route::post('/asset-equipment-type-catalogs', [AdminController::class, 'storeAssetEquipmentTypeCatalog']);
            Route::put('/asset-equipment-type-catalogs/{catalog}', [AdminController::class, 'updateAssetEquipmentTypeCatalog']);
            Route::delete('/asset-equipment-type-catalogs/{catalog}', [AdminController::class, 'destroyAssetEquipmentTypeCatalog']);

            Route::post('/asset-status-catalogs', [AdminController::class, 'storeAssetStatusCatalog']);
            Route::put('/asset-status-catalogs/{catalog}', [AdminController::class, 'updateAssetStatusCatalog']);
            Route::delete('/asset-status-catalogs/{catalog}', [AdminController::class, 'destroyAssetStatusCatalog']);
        });
    });
});
