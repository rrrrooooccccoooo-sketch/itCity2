@extends('tenant.layouts.app')

@section('content')
@php
    $canInventoryManage = auth()->user()?->hasTenantPermission('inventory.manage') ?? false;
    $canInventoryView   = $canInventoryManage || (auth()->user()?->hasTenantPermission('inventory.view') ?? false);
    $canTopologyManage  = auth()->user()?->hasTenantPermission('topology.manage') ?? false;
    $canTopologyView    = $canTopologyManage || (auth()->user()?->hasTenantPermission('topology.view') ?? false);
    $canMonitoringView  = auth()->user()?->hasTenantPermission('monitoring.view') ?? false;
    $canInventoryCatalogsView = (auth()->user()?->hasTenantPermission('inventory.catalogs.view') ?? false) || $canInventoryManage;
    $canInventoryCatalogsManage = (auth()->user()?->hasTenantPermission('inventory.catalogs.manage') ?? false) || $canInventoryManage;
    $incomingTransferPendingCount = isset($incomingTransferRequests) ? (int) $incomingTransferRequests->count() : 0;
    $outgoingTransferPendingCount = isset($outgoingTransferRequests) ? (int) $outgoingTransferRequests->count() : 0;
    $transferHistoryCount = isset($transferRequestHistory) ? (int) $transferRequestHistory->count() : 0;
    $showTransferPendingPanel = $incomingTransferPendingCount > 0 || $outgoingTransferPendingCount > 0;
    $assetInvoiceDraft = session('assetInvoiceDraft');
    $hasInvoiceDraft = is_array($assetInvoiceDraft) && !empty(data_get($assetInvoiceDraft, 'items', []));
    $autoOpenInvoiceDraft = (bool) session('assetInvoiceAutoOpenDraft', false);
    $hasInvoiceActionErrors = $errors->has('asset_invoice_file') || $errors->has('asset_invoice_payload') || $errors->has('asset_invoice_branch_id');
    $activateAssetsTab = $hasInvoiceDraft || $hasInvoiceActionErrors;
@endphp
<div class="container-fluid py-4">

    {{-- Panel Header --}}
    <div class="admin-hero mb-3">
        <div>
            <h1 class="admin-hero-title mb-1">Panel Administrativo Ejecutivo</h1>
            <p class="admin-hero-subtitle mb-0">Gestión operativa centralizada</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm admin-signature-btn" data-bs-toggle="modal" data-bs-target="#modalMySignature">
                <i class="bi bi-pen"></i> Mi firma
                @if (!empty($currentUserHasSignature))
                    <span class="badge text-bg-success ms-1">Activa</span>
                @endif
            </button>
            <span class="badge text-bg-light border">Modo operativo</span>
        </div>
    </div>

    @if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    @endif

    @if ($hasInvoiceActionErrors)
    <div class="alert alert-danger" role="alert">
        <strong>No se pudo analizar/importar la factura.</strong>
        <ul class="mb-0 mt-2">
            @foreach (collect($errors->get('asset_invoice_file'))->merge($errors->get('asset_invoice_payload'))->merge($errors->get('asset_invoice_branch_id')) as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Navigation Tabs --}}
    <div class="sticky-navigation mb-3">
        <div class="sticky-navigation-inner">
            <span class="sticky-navigation-label">Módulos</span>
            <ul class="nav nav-pills flex-nowrap gap-1" role="tablist">
            <li class="nav-item">
                @if ($canMonitoringView)
                <a href="#section-monitoring" class="nav-link {{ $activateAssetsTab ? '' : 'active' }} small" data-bs-toggle="tab">
                    <i class="bi bi-speedometer2"></i> Monitoreo
                </a>
                @endif
            </li>
            <li class="nav-item">
                @if ($canInventoryView)
                <a href="#section-assets" class="nav-link {{ $activateAssetsTab ? 'active' : '' }} small" data-bs-toggle="tab">
                    <i class="bi bi-hdd"></i> Inventario
                    @if ($canInventoryManage && $incomingTransferPendingCount > 0)
                        <span class="badge text-bg-danger ms-1">{{ $incomingTransferPendingCount }}</span>
                    @endif
                </a>
                @endif
            </li>
            <li class="nav-item">
                @if ($canTopologyView)
                <a href="#section-nodes" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-hdd-network"></i> Nodos
                </a>
                @endif
            </li>
            <li class="nav-item">
                @if ($canTopologyView)
                <a href="#section-spaces" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-door-closed"></i> Espacios físicos
                </a>
                @endif
            </li>
            <li class="nav-item">
                @if ($canTopologyView)
                <a href="#section-branches" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-diagram-3"></i> Sedes
                </a>
                @endif
            </li>
            <li class="nav-item">
                @if ($canTopologyView)
                <a href="#section-node-types" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-diagram-2"></i> Tipos de nodo
                </a>
                @endif
            </li>
            <li class="nav-item">
                @if ($canTopologyView)
                <a href="#section-floor-plans" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-diagram-3"></i> Planos
                </a>
                @endif
            </li>
            <li class="nav-item">
                @if ($canInventoryCatalogsView)
                <a href="#section-equipment-brands" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-box2"></i> Marcas
                </a>
                @endif
            </li>
            <li class="nav-item">
                @if ($canInventoryCatalogsView)
                <a href="#section-equipment-models" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-boxes"></i> Modelos
                </a>
                @endif
            </li>
            <li class="nav-item">
                @if ($canInventoryCatalogsView)
                <a href="#section-software" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-app-indicator"></i> Sistemas
                </a>
                @endif
            </li>
            <li class="nav-item">
                @if ($canTopologyManage)
                <a href="#section-relations" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-share"></i> Relaciones
                </a>
                @endif
            </li>
            </ul>
        </div>
    </div>

    {{-- TAB CONTENT --}}
    <div class="tab-content">

        {{-- ===== PHYSICAL SPACES SECTION (1) ===== --}}
        @if ($canTopologyView)
        <div class="tab-pane fade" id="section-spaces">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Espacios Físicos</h3>
                    <p class="text-muted small">Salas, pisos y áreas donde se encuentran los nodos de red</p>
                </div>
                @if ($canTopologyManage)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSpace">
                    <i class="bi bi-plus-circle"></i> Nuevo espacio
                </button>
                @endif
            </div>

            @php
                $spaceFilterTypes = $spaces->pluck('space_type')->filter()->unique()->sort()->values();
            @endphp

            <div class="row g-2 mb-2">
                <div class="col-md-5">
                    <input type="text" id="spacesFilterInput" class="form-control form-control-sm" placeholder="Filtrar espacios por nombre, sede o tipo...">
                </div>
                <div class="col-md-4">
                    <select id="spacesFilterBranch" class="form-select form-select-sm">
                        <option value="">Todas las sedes</option>
                        @foreach ($branches as $branch)
                            <option value="{{ Str::lower($branch->name) }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="spacesFilterType" class="form-select form-select-sm">
                        <option value="">Todos los tipos</option>
                        @foreach ($spaceFilterTypes as $spaceType)
                            <option value="{{ Str::lower($spaceType) }}">{{ ucfirst(str_replace('_', ' ', $spaceType)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="button" id="spacesFilterReset" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                </div>
            </div>

            @php
                $monitoringMetricThresholds = [
                    'cpu' => ['warning' => 80, 'danger' => null, 'normal' => 'info'],
                    'ram' => ['warning' => 80, 'danger' => null, 'normal' => 'info'],
                    'disco' => ['warning' => null, 'danger' => 80, 'normal' => 'info'],
                ];
                $monitoringBadgeClass = function (string $metric, $value) use ($monitoringMetricThresholds): string {
                    $config = $monitoringMetricThresholds[$metric] ?? ['warning' => null, 'danger' => null, 'normal' => 'secondary'];
                    $numeric = is_numeric($value) ? (float) $value : null;

                    if ($numeric !== null && $config['danger'] !== null && $numeric > $config['danger']) {
                        return 'danger';
                    }

                    if ($numeric !== null && $config['warning'] !== null && $numeric > $config['warning']) {
                        return 'warning';
                    }

                    return $config['normal'];
                };
            @endphp
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tableSpaces">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Sede</th>
                                <th>Tipo</th>
                                <th class="text-center">Nodos</th>
                                <th style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($spaces->take(5) as $space)
                                <tr
                                    data-space-branch="{{ Str::lower(optional($space->branch)->name ?? '') }}"
                                    data-space-type="{{ Str::lower($space->space_type ?? '') }}"
                                >
                                    <td class="fw-semibold">{{ $space->name }}</td>
                                    <td>{{ optional($space->branch)->name }}</td>
                                    <td><span class="badge bg-secondary">{{ $space->space_type }}</span></td>
                                    <td class="text-center"><span class="badge bg-light text-dark">0</span></td>
                                    <td>
                                        @if ($canTopologyManage)
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalSpace">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('¿Eliminar?')) { }">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                        @else
                                        <span class="text-muted small">Solo lectura</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Sin espacios registrados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== BRANCHES SECTION (2) ===== --}}
        @if ($canTopologyView)
        <div class="tab-pane fade" id="section-branches">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Sedes / Sucursales</h3>
                    <p class="text-muted small">Ubicaciones físicas de tu organización</p>
                </div>
                @if ($canTopologyManage)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalBranch">
                    <i class="bi bi-plus-circle"></i> Nueva sede
                </button>
                @endif
            </div>

            <div class="row g-2 mb-2">
                <div class="col-md-9">
                    <input type="text" id="branchesFilterInput" class="form-control form-control-sm" placeholder="Filtrar sedes por nombre o ciudad...">
                </div>
                <div class="col-md-3 d-grid">
                    <button type="button" id="branchesFilterReset" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tableBranches">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Ciudad</th>
                                <th class="text-center">Espacios</th>
                                <th style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($branches as $branch)
                                <tr>
                                    <td class="fw-semibold">{{ $branch->name }}</td>
                                    <td>{{ $branch->address ?? '—' }}</td>
                                    <td class="text-center"><span class="badge bg-light text-dark">0</span></td>
                                    <td>
                                        @if ($canTopologyManage)
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalBranch">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('¿Eliminar?')) { }">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                        @else
                                        <span class="text-muted small">Solo lectura</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Sin sedes registradas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        {{-- ===== NODE TYPES SECTION (3) ===== --}}
        @if ($canTopologyView)
        <div class="tab-pane fade" id="section-node-types">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Tipos de Nodo</h3>
                    <p class="text-muted small">Define categorías de equipos de red (router, switch, AP, etc.)</p>
                </div>
                @if ($canTopologyManage)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNodeType">
                    <i class="bi bi-plus-circle"></i> Nuevo tipo
                </button>
                @endif
            </div>

            <div class="row g-2 mb-2">
                <div class="col-md-9">
                    <input type="text" id="nodeTypesFilterInput" class="form-control form-control-sm" placeholder="Filtrar tipos de nodo por nombre o slug...">
                </div>
                <div class="col-md-3 d-grid">
                    <button type="button" id="nodeTypesFilterReset" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tableNodeTypes">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Slug</th>
                                <th class="text-center">Nodos</th>
                                <th style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($nodeTypes->take(5) as $nt)
                                <tr>
                                    <td class="fw-semibold">{{ $nt->name }}</td>
                                    <td><code class="text-muted small">{{ $nt->slug }}</code></td>
                                    <td class="text-center"><span class="badge bg-info">{{ $nt->nodes()->count() }}</span></td>
                                    <td>
                                        @if ($canTopologyManage)
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNodeType">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('¿Eliminar?')) { }">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                        @else
                                        <span class="text-muted small">Solo lectura</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Sin tipos de nodo registrados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== NODES SECTION (4) ===== --}}
        @if ($canTopologyView)
        <div class="tab-pane fade" id="section-nodes">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Nodos de Red</h3>
                    <p class="text-muted small">Equipos de red: routers, switches, access points, firewalls, etc.</p>
                </div>
                @if ($canTopologyManage)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNode">
                    <i class="bi bi-plus-circle"></i> Nuevo nodo
                </button>
                @endif
            </div>

            @php
                $nodeFilterStatuses = $nodes->pluck('status')->filter()->unique()->sort()->values();
            @endphp

            <div class="row g-2 mb-2">
                <div class="col-md-5">
                    <input type="text" id="nodesFilterInput" class="form-control form-control-sm" placeholder="Filtrar nodos por código, nombre, tipo o sede...">
                </div>
                <div class="col-md-3">
                    <select id="nodesFilterBranch" class="form-select form-select-sm">
                        <option value="">Todas las sedes</option>
                        @foreach ($branches as $branch)
                            <option value="{{ Str::lower($branch->name) }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="nodesFilterType" class="form-select form-select-sm">
                        <option value="">Todos los tipos</option>
                        @foreach ($nodeTypes as $nodeType)
                            <option value="{{ Str::lower($nodeType->name) }}">{{ $nodeType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="nodesFilterStatus" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        @foreach ($nodeFilterStatuses as $status)
                            <option value="{{ Str::lower($status) }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="button" id="nodesFilterReset" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tableNodes">
                        <thead class="table-light">
                            <tr>
                                <th>Nodo</th>
                                <th>Tipo · Sede</th>
                                <th>Espacio</th>
                                <th>Estado</th>
                                <th style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($nodes->take(5) as $node)
                                <tr
                                    data-node-branch="{{ Str::lower(optional($node->branch)->name ?? '') }}"
                                    data-node-type="{{ Str::lower(optional($node->nodeType)->name ?? '') }}"
                                    data-node-status="{{ Str::lower($node->status ?? '') }}"
                                >
                                    <td class="fw-semibold"><code>{{ $node->code }}</code> {{ $node->name }}</td>
                                    <td>
                                        {{ optional($node->nodeType)->name }} 
                                        <span class="text-muted small">({{ optional($node->branch)->name }})</span>
                                    </td>
                                    <td>{{ optional($node->physicalSpace)->name ?? '—' }}</td>
                                    <td><span class="badge bg-success">{{ $node->status }}</span></td>
                                    <td>
                                        @if ($canTopologyManage)
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNode">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('¿Eliminar?')) { }">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                        @else
                                        <span class="text-muted small">Solo lectura</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Sin nodos registrados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== MONITORING SECTION (5) ===== --}}
        @if ($canMonitoringView)
        <div class="tab-pane fade {{ $activateAssetsTab ? '' : 'show active' }}" id="section-monitoring">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Monitoreo en Vivo</h3>
                    <p class="text-muted small">Estado y métricas de agentes en tiempo real</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/admin/monitoring/agent-installer" class="btn btn-primary btn-sm" download>
                        <i class="bi bi-download"></i> Descargar instalador .ps1
                    </a>
                    <a href="/admin/monitoring/agent-installer-zip" class="btn btn-outline-primary btn-sm" download>
                        <i class="bi bi-file-earmark-zip"></i> Descargar instalador .zip
                    </a>
                    <a href="/admin/monitoring/agent-installer-exe" class="btn btn-outline-primary btn-sm" download>
                        <i class="bi bi-download"></i> Descargar instalador .exe
                    </a>
                </div>
            </div>

            @php
                $monitoringFilterBranches = $monitoringAssets
                    ->map(fn ($asset) => optional($asset->branch)->name)
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();
            @endphp

            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <input type="text" id="monitoringFilterInput" class="form-control form-control-sm" placeholder="Filtrar monitoreo por activo, hostname, sede o estado...">
                </div>
                <div class="col-md-3">
                    <select id="monitoringFilterBranch" class="form-select form-select-sm">
                        <option value="">Todas las sedes</option>
                        @foreach ($monitoringFilterBranches as $branchName)
                            <option value="{{ Str::lower($branchName) }}">{{ $branchName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="monitoringFilterStatus" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                    </select>
                </div>
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="button" id="monitoringFilterReset" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-muted small">Rastreados</h5>
                            <p class="card-text display-6 text-primary">{{ $monitoringSummary['tracked_assets'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-muted small">En línea</h5>
                            <p class="card-text display-6 text-success">{{ $monitoringSummary['online_assets'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-muted small">Críticos</h5>
                            <p class="card-text display-6 text-danger">{{ $monitoringSummary['critical_assets'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-muted small">Offline</h5>
                            <p class="card-text display-6 text-warning">{{ ($monitoringSummary['tracked_assets'] ?? 0) - ($monitoringSummary['online_assets'] ?? 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tableMonitoring">
                        <thead class="table-light">
                            <tr>
                                <th>Activo · Hostname</th>
                                <th>Sede</th>
                                <th>CPU / RAM / Disco</th>
                                <th>Último heartbeat</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="monitoringRows">
                            @forelse ($monitoringAssets->take(10) as $asset)
                                @php $isOnline = $asset->last_seen_at && $asset->last_seen_at->greaterThanOrEqualTo(now()->subMinutes($monitoringOnlineWindowMinutes ?? 10)); @endphp
                                <tr style="cursor:pointer;" data-asset-id="{{ $asset->id }}" data-monitoring-branch="{{ Str::lower(optional($asset->branch)->name ?? '') }}" data-monitoring-status="{{ $isOnline ? 'online' : 'offline' }}" title="Ver detalle en tiempo real" onclick="window.itcityOpenAssetDetail && window.itcityOpenAssetDetail(this.dataset.assetId)">
                                    <td>
                                        <div class="fw-semibold">{{ $asset->asset_tag ?: ($asset->hostname ?: ('Activo #' . $asset->id)) }}</div>
                                        @if($asset->hostname)<div class="text-muted small">{{ $asset->hostname }}</div>@endif
                                    </td>
                                    <td>{{ optional($asset->branch)->name ?? '—' }}</td>
                                    <td class="small">
                                        <span class="badge bg-{{ $monitoringBadgeClass('cpu', $asset->last_cpu_usage_percent) }}">CPU {{ $asset->last_cpu_usage_percent ?? '?' }}%</span>
                                        <span class="badge bg-{{ $monitoringBadgeClass('ram', $asset->last_memory_usage_percent) }}">RAM {{ $asset->last_memory_usage_percent ?? '?' }}%</span>
                                        <span class="badge bg-{{ $monitoringBadgeClass('disco', $asset->last_disk_usage_percent) }}">Disco {{ $asset->last_disk_usage_percent ?? '?' }}%</span>
                                    </td>
                                    <td>{{ $asset->last_seen_at?->diffForHumans() ?? '—' }}</td>
                                    <td>
                                        <span class="monitor-pill {{ $isOnline ? 'online' : 'offline' }}">{{ $isOnline ? 'Online' : 'Offline' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Sin activos con monitoreo activo</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== FLOOR PLANS SECTION (6) ===== --}}
        @if ($canTopologyView)
        <div class="tab-pane fade" id="section-floor-plans">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Planos y Heatmaps</h3>
                    <p class="text-muted small">Visualización geográfica de espacios y cobertura RF</p>
                </div>
                @if ($canTopologyManage)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalFloorPlan">
                    <i class="bi bi-plus-circle"></i> Nuevo plano
                </button>
                @endif
            </div>

            @php
                $floorPlanFilterBranches = $floorPlans
                    ->map(fn ($plan) => optional($plan->branch)->name)
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();
            @endphp

            <div class="row g-2 mb-2">
                <div class="col-md-8">
                    <input type="text" id="floorPlansFilterInput" class="form-control form-control-sm" placeholder="Filtrar planos por nombre, espacio o sede...">
                </div>
                <div class="col-md-4">
                    <select id="floorPlansFilterBranch" class="form-select form-select-sm">
                        <option value="">Todas las sedes</option>
                        @foreach ($floorPlanFilterBranches as $branchName)
                            <option value="{{ Str::lower($branchName) }}">{{ $branchName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="button" id="floorPlansFilterReset" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tableFloorPlans">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Espacio</th>
                                <th>Sede</th>
                                <th>Piso · Sala</th>
                                <th style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($floorPlans->take(8) as $plan)
                                <tr data-floorplan-branch="{{ Str::lower(optional($plan->branch)->name ?? '') }}">
                                    <td class="fw-semibold">{{ $plan->name }}</td>
                                    <td>{{ optional($plan->physicalSpace)->name ?? '—' }}</td>
                                    <td>{{ optional($plan->branch)->name ?? '—' }}</td>
                                    <td class="small">
                                        @if($plan->physicalSpace)
                                            <span class="badge bg-secondary">{{ $plan->physicalSpace->floor ?? 'N/A' }} · {{ $plan->physicalSpace->room ?? 'N/A' }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('/admin/floor-plans/' . $plan->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Sin planos registrados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== EQUIPMENT BRANDS SECTION (7) ===== --}}
        @if ($canInventoryCatalogsView)
        <div class="tab-pane fade" id="section-equipment-brands">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Marcas de Equipo</h3>
                    <p class="text-muted small">Catálogo de fabricantes (Cisco, Fortinet, Ubiquiti, etc.)</p>
                </div>
                @if ($canInventoryManage)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalBrand">
                    <i class="bi bi-plus-circle"></i> Nueva marca
                </button>
                @endif
            </div>

            <div class="row g-2 mb-2">
                <div class="col-md-9">
                    <input type="text" id="brandsFilterInput" class="form-control form-control-sm" placeholder="Filtrar marcas por nombre...">
                </div>
                <div class="col-md-3 d-grid">
                    <button type="button" id="brandsFilterReset" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tableBrands">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th class="text-center">Modelos</th>
                                <th style="width: 300px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($equipmentBrands as $brand)
                                <tr>
                                    <td class="fw-semibold">{{ $brand->name }}</td>
                                    <td class="text-center"><span class="badge bg-secondary">{{ $brand->equipmentModels()->count() }}</span></td>
                                    <td>
                                        @if ($canInventoryManage)
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalBrand">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <form method="POST" action="{{ url('/admin/equipment-brands/' . $brand->id) }}" class="d-inline" data-confirm="¿Eliminar?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-muted small">Solo lectura</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">Sin marcas registradas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== EQUIPMENT MODELS SECTION (8) ===== --}}
        @if ($canInventoryCatalogsView)
        <div class="tab-pane fade" id="section-equipment-models">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Modelos de Equipo</h3>
                    <p class="text-muted small">Especificaciones de modelos por tipo de equipo</p>
                </div>
                @if ($canInventoryManage)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalModel">
                    <i class="bi bi-plus-circle"></i> Nuevo modelo
                </button>
                @endif
            </div>

            @php
                $equipmentModelFilterBrands = $equipmentModels
                    ->map(fn ($model) => optional($model->brand)->name)
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();
                $equipmentModelFilterTypes = $equipmentModels->pluck('equipment_type')->filter()->unique()->sort()->values();
            @endphp

            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <input type="text" id="modelsFilterInput" class="form-control form-control-sm" placeholder="Filtrar modelos por nombre, marca o tipo...">
                </div>
                <div class="col-md-3">
                    <select id="modelsFilterBrand" class="form-select form-select-sm">
                        <option value="">Todas las marcas</option>
                        @foreach ($equipmentModelFilterBrands as $brandName)
                            <option value="{{ Str::lower($brandName) }}">{{ $brandName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="modelsFilterType" class="form-select form-select-sm">
                        <option value="">Todos los tipos</option>
                        @foreach ($equipmentModelFilterTypes as $equipmentType)
                            <option value="{{ Str::lower($equipmentType) }}">{{ $equipmentModelTypes[$equipmentType] ?? $equipmentType }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="button" id="modelsFilterReset" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tableModels">
                        <thead class="table-light">
                            <tr>
                                <th>Modelo</th>
                                <th>Marca · Tipo</th>
                                <th class="text-center">Specs</th>
                                <th style="width: 220px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($equipmentModels as $model)
                                <tr
                                    data-model-brand="{{ Str::lower(optional($model->brand)->name ?? '') }}"
                                    data-model-type="{{ Str::lower($model->equipment_type ?? '') }}"
                                >
                                    <td class="fw-semibold">{{ $model->name }}</td>
                                    <td>
                                        {{ optional($model->brand)->name }}
                                        <span class="text-muted small">({{ $equipmentModelTypes[$model->equipment_type] ?? $model->equipment_type }})</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($model->equipment_type === 'access-point')
                                            <span class="badge bg-info">{{ $model->coverage_radius_min_m ?? '?' }}-{{ $model->coverage_radius_max_m ?? '?' }}m</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($canInventoryManage)
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalModel">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <form method="POST" action="{{ url('/admin/equipment-models/' . $model->id) }}" class="d-inline" data-confirm="¿Eliminar?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-muted small">Solo lectura</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Sin modelos registrados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== ASSETS/INVENTORY SECTION (9) ===== --}}
        @if ($canInventoryView)
        <div class="tab-pane fade {{ $activateAssetsTab ? 'show active' : '' }}" id="section-assets">
            <div class="inventory-compact-toolbar mb-2">
                <div class="small text-muted">Vista compacta: oculta paneles para ver mas equipos en pantalla.</div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @if ($canInventoryManage)
                        <button
                            class="btn btn-sm btn-outline-secondary"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#inventoryTransferRequestsPanel"
                            aria-expanded="false"
                            aria-controls="inventoryTransferRequestsPanel"
                        >
                            Solicitudes pendientes
                            @if (($incomingTransferPendingCount + $outgoingTransferPendingCount) > 0)
                                <span class="badge text-bg-danger ms-1">{{ $incomingTransferPendingCount + $outgoingTransferPendingCount }}</span>
                            @endif
                        </button>
                        <button
                            class="btn btn-sm btn-outline-secondary"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#inventoryTransferHistoryPanel"
                            aria-expanded="false"
                            aria-controls="inventoryTransferHistoryPanel"
                        >
                            Historial traslados
                            <span class="badge text-bg-light border ms-1">{{ $transferHistoryCount }}</span>
                        </button>
                    @endif
                    <button
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#inventoryFiltersPanel"
                        aria-expanded="false"
                        aria-controls="inventoryFiltersPanel"
                    >
                        Filtros de busqueda
                    </button>
                    @if ($canInventoryCatalogsView)
                    <button
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#inventoryCatalogsPanel"
                        aria-expanded="false"
                        aria-controls="inventoryCatalogsPanel"
                    >
                        Catalogos
                    </button>
                    @endif
                    @if ($canInventoryManage)
                    <button id="btnOpenInvoiceAnalyzerModal" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalAssetInvoiceAnalyzer">
                        <i class="bi bi-file-earmark-text"></i> Analizar factura
                    </button>
                    @if ($hasInvoiceDraft)
                    <button id="btnOpenInvoiceDraftModal" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAssetInvoiceDraft">
                        <i class="bi bi-magic"></i> Ver borrador detectado
                    </button>
                    @endif
                    <button id="btnOpenImportAssetsModal" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAssetImport">
                        <i class="bi bi-upload"></i> Carga masiva
                    </button>
                    <button id="btnOpenNewAssetModal" class="btn btn-primary btn-sm ms-md-auto" data-bs-toggle="modal" data-bs-target="#modalAsset">
                        <i class="bi bi-plus-circle"></i> Nuevo activo
                    </button>
                    @endif
                </div>
            </div>

            @if ($canInventoryManage && $hasInvoiceDraft)
            <div class="alert alert-primary d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <strong>Borrador detectado desde factura.</strong>
                    <span class="small">Archivo: {{ data_get($assetInvoiceDraft, 'file_name', 'N/A') }} · Ítems detectados: {{ count(data_get($assetInvoiceDraft, 'items', [])) }}</span>
                </div>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAssetInvoiceDraft">
                    <i class="bi bi-eye"></i> Abrir borrador
                </button>
            </div>
            @endif

            @if ($canInventoryManage)
            <div id="inventoryTransferRequestsPanel" class="collapse">
            <div class="row g-3 mb-3">
                <div class="col-xl-6">
                    <div class="card h-100">
                        <div class="card-header bg-white fw-semibold">Solicitudes recibidas (pendientes)</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Activo</th>
                                            <th>Origen → Destino</th>
                                            <th>Prioridad</th>
                                            <th>Motivo</th>
                                            <th style="width: 170px;">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($incomingTransferRequests as $transferRequest)
                                        @php
                                            $assetLabel = trim((string) (($transferRequest->computerAsset?->asset_tag ?? '') . ' ' . ($transferRequest->computerAsset?->hostname ?? '')));
                                            $priorityKey = Str::lower((string) ($transferRequest->priority ?: 'normal'));
                                            $priorityBadgeClass = match ($priorityKey) {
                                                'urgent' => 'danger',
                                                'high' => 'warning text-dark',
                                                default => 'secondary',
                                            };
                                            $priorityLabel = match ($priorityKey) {
                                                'urgent' => 'URGENTE',
                                                'high' => 'ALTA',
                                                default => 'NORMAL',
                                            };
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $assetLabel !== '' ? $assetLabel : ('Activo #' . $transferRequest->computer_asset_id) }}</div>
                                                <div class="small text-muted">Solicitud #{{ $transferRequest->id }} · {{ optional($transferRequest->requested_at)->format('d/m H:i') }}</div>
                                            </td>
                                            <td class="small">
                                                <div>{{ $transferRequest->requested_by_name ?: 'Sistema' }}</div>
                                                <div class="text-muted">{{ optional($transferRequest->requestedFromBranch)->name ?: 'N/A' }} → {{ optional($transferRequest->requestedToBranch)->name ?: 'N/A' }}</div>
                                            </td>
                                            <td><span class="badge text-bg-{{ $priorityBadgeClass }}">{{ $priorityLabel }}</span></td>
                                            <td class="small">{{ $transferRequest->reason }}</td>
                                            <td>
                                                <form method="POST" action="{{ url('/admin/computer-assets/transfer-requests/' . $transferRequest->id . '/decision') }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="accepted">
                                                    <button type="submit" class="btn btn-sm btn-success">Aceptar</button>
                                                </form>
                                                <form method="POST" action="{{ url('/admin/computer-assets/transfer-requests/' . $transferRequest->id . '/decision') }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="rejected">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Rechazar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-3">No tienes solicitudes pendientes por atender.</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card h-100">
                        <div class="card-header bg-white fw-semibold">Solicitudes enviadas (pendientes)</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Activo</th>
                                            <th>Destino</th>
                                            <th>Prioridad</th>
                                            <th>Motivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($outgoingTransferRequests as $transferRequest)
                                        @php
                                            $assetLabel = trim((string) (($transferRequest->computerAsset?->asset_tag ?? '') . ' ' . ($transferRequest->computerAsset?->hostname ?? '')));
                                            $priorityKey = Str::lower((string) ($transferRequest->priority ?: 'normal'));
                                            $priorityBadgeClass = match ($priorityKey) {
                                                'urgent' => 'danger',
                                                'high' => 'warning text-dark',
                                                default => 'secondary',
                                            };
                                            $priorityLabel = match ($priorityKey) {
                                                'urgent' => 'URGENTE',
                                                'high' => 'ALTA',
                                                default => 'NORMAL',
                                            };
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $assetLabel !== '' ? $assetLabel : ('Activo #' . $transferRequest->computer_asset_id) }}</div>
                                                <div class="small text-muted">Solicitud #{{ $transferRequest->id }} · {{ optional($transferRequest->requested_at)->format('d/m H:i') }}</div>
                                            </td>
                                            <td class="small">
                                                <div>{{ $transferRequest->requested_to_user_name ?: 'Sin agente destino' }}</div>
                                                <div class="text-muted">{{ optional($transferRequest->requestedToBranch)->name ?: 'N/A' }}</div>
                                            </td>
                                            <td><span class="badge text-bg-{{ $priorityBadgeClass }}">{{ $priorityLabel }}</span></td>
                                            <td class="small">{{ $transferRequest->reason }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">No has enviado solicitudes pendientes.</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <div id="inventoryTransferHistoryPanel" class="collapse mb-3">
            <div class="card mb-0">
                <div class="card-header bg-white fw-semibold">Historial de solicitudes de traslado</div>
                <div class="card-body">
                    <div class="row g-2 align-items-end mb-2">
                        <div class="col-md-7">
                            <label class="form-label small mb-1">Buscar en historial</label>
                            <input type="text" id="transferHistorySearchInput" class="form-control form-control-sm" placeholder="Activo, agente, sede o motivo...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Estado</label>
                            <select id="transferHistoryStatusFilter" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="pending">Pendiente</option>
                                <option value="accepted">Aceptada</option>
                                <option value="rejected">Rechazada</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="button" id="transferHistoryFilterReset" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha solicitud</th>
                                    <th>Activo</th>
                                    <th>Origen → Destino</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                    <th>Decisión</th>
                                    <th>Motivo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="transferHistoryTableBody">
                            @forelse ($transferRequestHistory as $transferRequest)
                                @php
                                    $assetLabel = trim((string) (($transferRequest->computerAsset?->asset_tag ?? '') . ' ' . ($transferRequest->computerAsset?->hostname ?? '')));
                                    $statusKey = (string) $transferRequest->status;
                                    $priorityKey = Str::lower((string) ($transferRequest->priority ?: 'normal'));
                                    $statusBadgeClass = match ($statusKey) {
                                        'pending' => 'warning text-dark',
                                        'accepted' => 'success',
                                        'rejected' => 'danger',
                                        default => 'secondary',
                                    };
                                    $priorityBadgeClass = match ($priorityKey) {
                                        'urgent' => 'danger',
                                        'high' => 'warning text-dark',
                                        default => 'secondary',
                                    };
                                    $priorityLabel = match ($priorityKey) {
                                        'urgent' => 'URGENTE',
                                        'high' => 'ALTA',
                                        default => 'NORMAL',
                                    };
                                    $searchText = Str::lower(collect([
                                        $assetLabel,
                                        $transferRequest->requested_by_name,
                                        $transferRequest->requested_to_user_name,
                                        optional($transferRequest->requestedFromBranch)->name,
                                        optional($transferRequest->requestedToBranch)->name,
                                        $transferRequest->reason,
                                        $transferRequest->note,
                                        $transferRequest->decision_note,
                                        $statusKey,
                                        $priorityKey,
                                    ])->filter()->join(' '));
                                @endphp
                                <tr data-transfer-row="1" data-transfer-status="{{ Str::lower($statusKey) }}" data-transfer-search="{{ $searchText }}">
                                    <td class="small">{{ optional($transferRequest->requested_at)->format('d/m/Y H:i') ?: 'N/A' }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $assetLabel !== '' ? $assetLabel : ('Activo #' . $transferRequest->computer_asset_id) }}</div>
                                        <div class="small text-muted">Solicitud #{{ $transferRequest->id }}</div>
                                    </td>
                                    <td class="small">
                                        <div>{{ $transferRequest->requested_by_name ?: 'Sistema' }} → {{ $transferRequest->requested_to_user_name ?: 'N/A' }}</div>
                                        <div class="text-muted">{{ optional($transferRequest->requestedFromBranch)->name ?: 'N/A' }} → {{ optional($transferRequest->requestedToBranch)->name ?: 'N/A' }}</div>
                                    </td>
                                    <td><span class="badge text-bg-{{ $statusBadgeClass }}">{{ strtoupper($statusKey) }}</span></td>
                                    <td><span class="badge text-bg-{{ $priorityBadgeClass }}">{{ $priorityLabel }}</span></td>
                                    <td class="small">
                                        @if ($transferRequest->decided_at)
                                            <div>{{ optional($transferRequest->decided_at)->format('d/m/Y H:i') }}</div>
                                            <div class="text-muted">por {{ $transferRequest->decided_by_name ?: 'Sistema' }}</div>
                                        @else
                                            <span class="text-muted">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        <div>{{ $transferRequest->reason }}</div>
                                        @if ($transferRequest->decision_note)
                                            <div class="text-muted">Nota: {{ $transferRequest->decision_note }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($transferRequest->computerAsset)
                                            <a href="{{ url('/admin/computer-assets/' . $transferRequest->computerAsset->id . '/assignment-log') }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                                                Bitácora
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-3">Sin solicitudes registradas.</td></tr>
                            @endforelse
                            <tr id="transferHistoryNoResults" class="d-none">
                                <td colspan="8" class="text-center text-muted py-3">No hay resultados para este filtro.</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            </div>
            @endif

            @if ($canInventoryCatalogsView)
            <div id="crud-asset-catalogs" class="mb-3 inventory-catalogs-anchor"></div>
            <div id="inventoryCatalogsPanel" class="collapse mb-3">
                <div class="row g-3">
                    <div class="col-xl-6">
                        <div class="card h-100">
                            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                                <span>Catalogo de tipos de equipo</span>
                                @if ($canInventoryCatalogsManage)
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateEquipmentTypeCatalog">
                                        <i class="bi bi-plus-circle"></i> Nuevo tipo
                                    </button>
                                @endif
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Clave</th>
                                                <th>Etiqueta</th>
                                                <th>Orden</th>
                                                <th>Activo</th>
                                                @if ($canInventoryCatalogsManage)
                                                    <th style="width: 180px;">Acciones</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($assetEquipmentTypeCatalogs as $catalogItem)
                                                <tr>
                                                    <td><code>{{ $catalogItem->key }}</code></td>
                                                    <td>{{ $catalogItem->label }}</td>
                                                    <td>{{ $catalogItem->sort_order }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $catalogItem->is_active ? 'success' : 'secondary' }}">
                                                            {{ $catalogItem->is_active ? 'Si' : 'No' }}
                                                        </span>
                                                    </td>
                                                    @if ($canInventoryCatalogsManage)
                                                    <td>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-primary js-edit-equipment-type-catalog"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalEditEquipmentTypeCatalog"
                                                            data-catalog-id="{{ $catalogItem->id }}"
                                                            data-catalog-key="{{ $catalogItem->key }}"
                                                            data-catalog-label="{{ $catalogItem->label }}"
                                                            data-catalog-description="{{ $catalogItem->description ?? '' }}"
                                                            data-catalog-sort-order="{{ $catalogItem->sort_order }}"
                                                            data-catalog-is-active="{{ $catalogItem->is_active ? '1' : '0' }}"
                                                        >
                                                            Editar
                                                        </button>
                                                        <form method="POST" action="{{ url('/admin/asset-equipment-type-catalogs/' . $catalogItem->id) }}" class="d-inline" data-confirm="¿Eliminar tipo de equipo {{ $catalogItem->label }}?">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                        </form>
                                                    </td>
                                                    @endif
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted py-3">Sin tipos configurados.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card h-100">
                            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                                <span>Catalogo de estados de activo</span>
                                @if ($canInventoryCatalogsManage)
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateAssetStatusCatalog">
                                        <i class="bi bi-plus-circle"></i> Nuevo estado
                                    </button>
                                @endif
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Clave</th>
                                                <th>Etiqueta</th>
                                                <th>Orden</th>
                                                <th>Activo</th>
                                                @if ($canInventoryCatalogsManage)
                                                    <th style="width: 180px;">Acciones</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($assetStatusCatalogs as $catalogItem)
                                                <tr>
                                                    <td><code>{{ $catalogItem->key }}</code></td>
                                                    <td>{{ $catalogItem->label }}</td>
                                                    <td>{{ $catalogItem->sort_order }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $catalogItem->is_active ? 'success' : 'secondary' }}">
                                                            {{ $catalogItem->is_active ? 'Si' : 'No' }}
                                                        </span>
                                                    </td>
                                                    @if ($canInventoryCatalogsManage)
                                                    <td>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-primary js-edit-asset-status-catalog"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalEditAssetStatusCatalog"
                                                            data-catalog-id="{{ $catalogItem->id }}"
                                                            data-catalog-key="{{ $catalogItem->key }}"
                                                            data-catalog-label="{{ $catalogItem->label }}"
                                                            data-catalog-description="{{ $catalogItem->description ?? '' }}"
                                                            data-catalog-sort-order="{{ $catalogItem->sort_order }}"
                                                            data-catalog-is-active="{{ $catalogItem->is_active ? '1' : '0' }}"
                                                        >
                                                            Editar
                                                        </button>
                                                        <form method="POST" action="{{ url('/admin/asset-status-catalogs/' . $catalogItem->id) }}" class="d-inline" data-confirm="¿Eliminar estado {{ $catalogItem->label }}?">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                        </form>
                                                    </td>
                                                    @endif
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted py-3">Sin estados configurados.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @php
                $assetFilterTypes = $computerAssets->pluck('equipment_type')->filter()->unique()->sort()->values();
                $assetFilterStatuses = $computerAssets->pluck('status')->filter()->unique()->sort()->values();
                $assetFilterBranches = $computerAssets->map(fn ($asset) => optional($asset->branch)->name)->filter()->unique()->sort()->values();
                $assetFilterBrands = $computerAssets->pluck('brand')->filter()->unique()->sort()->values();
                $assetFilterModels = $computerAssets->pluck('model')->filter()->unique()->sort()->values();
            @endphp

            <div id="inventoryFiltersPanel" class="collapse">
            <div class="inventory-filter-panel mb-3">
                <div class="p-3">
                    <div class="small fw-semibold text-muted mb-2">Filtros de búsqueda</div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Buscar equipo</label>
                            <input
                                type="text"
                                id="inventoryAssetFilterSearch"
                                class="form-control form-control-sm"
                                placeholder="Etiqueta, hostname, IP, serie, software..."
                            >
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Factura</label>
                            <input
                                type="text"
                                id="inventoryAssetFilterInvoiceFolio"
                                class="form-control form-control-sm"
                                placeholder="Número de factura..."
                            >
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Tipo</label>
                            <select id="inventoryAssetFilterType" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                @foreach ($assetFilterTypes as $equipmentType)
                                    <option value="{{ Str::lower($equipmentType) }}">{{ $assetEquipmentTypes[$equipmentType] ?? $equipmentType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Estado</label>
                            <select id="inventoryAssetFilterStatus" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                @foreach ($assetFilterStatuses as $status)
                                    <option value="{{ Str::lower($status) }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Sede</label>
                            <select id="inventoryAssetFilterBranch" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                @foreach ($assetFilterBranches as $branchName)
                                    <option value="{{ Str::lower($branchName) }}">{{ $branchName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 d-grid">
                            <button type="button" id="inventoryAssetFilterReset" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                        </div>
                    </div>
                    <div class="row g-2 align-items-end mt-1">
                        <div class="col-md-4">
                            <label class="form-label small mb-1 d-flex align-items-center gap-1">
                                <span>Software</span>
                                <button
                                    type="button"
                                    class="btn btn-link btn-sm p-0 text-muted"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-title="Contiene: office · Exacta: Microsoft Office 2019 · Empieza con: adobe"
                                    aria-label="Ayuda de filtros de software"
                                >
                                    <i class="bi bi-question-circle"></i>
                                </button>
                            </label>
                            <input
                                type="text"
                                id="inventoryAssetFilterSoftware"
                                class="form-control form-control-sm"
                                placeholder="Office, antivirus, sistema operativo..."
                            >
                            <select id="inventoryAssetFilterSoftwareMode" class="form-select form-select-sm mt-1">
                                <option value="contains" selected>Coincidencia: contiene</option>
                                <option value="exact">Coincidencia: exacta</option>
                                <option value="starts_with">Coincidencia: empieza con</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Marca</label>
                            <select id="inventoryAssetFilterBrand" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                @foreach ($assetFilterBrands as $brand)
                                    <option value="{{ Str::lower($brand) }}">{{ $brand }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Modelo</label>
                            <select id="inventoryAssetFilterModel" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                @foreach ($assetFilterModels as $model)
                                    <option value="{{ Str::lower($model) }}">{{ $model }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 align-items-end mt-1">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">RAM mínima (GB)</label>
                            <input type="number" min="0" step="1" id="inventoryAssetFilterRamMin" class="form-control form-control-sm" placeholder="Ej. 8">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Almacenamiento mínimo (GB)</label>
                            <input type="number" min="0" step="1" id="inventoryAssetFilterStorageMin" class="form-control form-control-sm" placeholder="Ej. 256">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Último reporte desde</label>
                            <input type="date" id="inventoryAssetFilterSeenFrom" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Último reporte hasta</label>
                            <input type="date" id="inventoryAssetFilterSeenTo" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="small text-muted mt-2">
                        Mostrando <span id="inventoryAssetFilterVisibleCount">0</span> de <span id="inventoryAssetFilterTotalCount">0</span> activos
                    </div>
                    <div id="inventoryAssetActiveFilters" class="d-flex flex-wrap gap-2 mt-2"></div>
                </div>
            </div>
            </div>

            <div class="card inventory-results-card">
                <div class="table-responsive" id="inventoryAssetsTableWrapper">
                    <table class="table table-sm table-hover mb-0" id="inventoryAssetsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Etiqueta / Hostname</th>
                                <th>Tipo</th>
                                <th>Sede</th>
                                <th>Estado</th>
                                <th>Último reporte</th>
                                <th style="width: 220px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryAssetsTableBody">
                            @forelse ($computerAssets as $asset)
                                @php
                                    $assetSoftwareExactTerms = collect([
                                        $asset->office_version,
                                        $asset->operating_system,
                                        $asset->antivirus_summary,
                                    ])->merge(
                                        collect(data_get($asset->details, 'inventory.software.installed_programs', []))
                                            ->take(200)
                                            ->flatMap(function ($program) {
                                                return [
                                                    data_get($program, 'name'),
                                                    collect([
                                                        data_get($program, 'name'),
                                                        data_get($program, 'version'),
                                                    ])->filter()->join(' '),
                                                ];
                                            })
                                    )->filter()->unique()->values();
                                    $inventoryScope = data_get($asset->details, 'inventory.capture_scope');
                                    $inventoryScopeLabel = $inventoryScope === 'extended'
                                        ? 'Inventario extendido'
                                        : ($inventoryScope === 'lightweight' ? 'Inventario ligero' : null);
                                    $lastExtendedRaw = data_get($asset->details, 'inventory.last_extended_captured_at');
                                    $lastExtendedLabel = null;
                                    $hardwareSummary = collect([
                                        $asset->cpu ? 'CPU ' . $asset->cpu : null,
                                        $asset->ram_gb ? 'RAM ' . $asset->ram_gb . ' GB' : null,
                                        $asset->storage_gb ? 'Disco ' . $asset->storage_gb . ' GB' : null,
                                        $asset->primary_gpu ? 'GPU ' . $asset->primary_gpu : null,
                                    ])->filter()->join(' · ');
                                    $softwareSummary = collect([
                                        $asset->operating_system ? 'SO ' . $asset->operating_system : null,
                                        $asset->office_version ? 'Office ' . $asset->office_version : null,
                                        $asset->antivirus_summary ? 'AV ' . $asset->antivirus_summary : null,
                                        $asset->installed_programs_count !== null ? $asset->installed_programs_count . ' programas' : null,
                                    ])->filter()->join(' · ');
                                    $topInstalledPrograms = collect(data_get($asset->details, 'inventory.software.installed_programs', []))
                                        ->pluck('name')
                                        ->filter()
                                        ->take(3)
                                        ->values()
                                        ->join(', ');
                                    if ($lastExtendedRaw) {
                                        try {
                                            $lastExtendedLabel = 'Último extendido ' . \Illuminate\Support\Carbon::parse($lastExtendedRaw)->format('d/m H:i');
                                        } catch (\Throwable $e) {
                                            $lastExtendedLabel = 'Último extendido registrado';
                                        }
                                    }
                                    $inventoryMetaSummary = collect([
                                        $asset->inventory_last_captured_at ? 'Inv. ' . $asset->inventory_last_captured_at->format('d/m H:i') : null,
                                        $inventoryScopeLabel,
                                        $lastExtendedLabel,
                                        $asset->operating_system_build ? 'Build ' . $asset->operating_system_build : null,
                                    ])->filter()->join(' ') ?: 'Sin inventario extendido';
                                    $assetSoftwareIndex = Str::lower(collect([
                                        $asset->office_version,
                                        $asset->operating_system,
                                        $asset->antivirus_summary,
                                        collect(data_get($asset->details, 'inventory.software.installed_programs', []))
                                            ->take(200)
                                            ->map(function ($program) {
                                                return collect([
                                                    data_get($program, 'name'),
                                                    data_get($program, 'version'),
                                                    data_get($program, 'publisher'),
                                                ])->filter()->join(' ');
                                            })
                                            ->filter()
                                            ->join(' '),
                                    ])->filter()->join(' '));
                                    $latestAssignmentLog = collect(data_get($asset->details, 'assignment_log', []))
                                        ->filter(fn ($entry) => is_array($entry))
                                        ->values()
                                        ->last() ?? [];
                                    $assetInvoiceFolio = trim((string) data_get($asset->details, 'procurement.invoice_folio', '')) ?: trim((string) data_get($latestAssignmentLog, 'invoice_folio', ''));
                                    $assetSearchIndex = Str::lower(collect([
                                        $asset->asset_tag,
                                        $asset->hostname,
                                        $asset->brand,
                                        $asset->model,
                                        $asset->serial_number,
                                        $asset->domain_name,
                                        $asset->primary_ip_address,
                                        $asset->primary_mac_address,
                                        optional($asset->branch)->name,
                                        $assetEquipmentTypes[$asset->equipment_type] ?? $asset->equipment_type,
                                        $asset->status,
                                        $assetInvoiceFolio,
                                        $assetSoftwareIndex,
                                    ])->filter()->join(' '));
                                    $hasInvoiceDocument = trim((string) data_get($asset->details, 'procurement.invoice_document.path', '')) !== '';
                                @endphp
                                <tr
                                    data-filter-row="asset"
                                    data-asset-id="{{ $asset->id }}"
                                    data-search="{{ $assetSearchIndex }}"
                                    data-type="{{ Str::lower($asset->equipment_type ?? '') }}"
                                    data-status="{{ Str::lower($asset->status ?? '') }}"
                                    data-branch="{{ Str::lower(optional($asset->branch)->name ?? '') }}"
                                    data-brand="{{ Str::lower($asset->brand ?? '') }}"
                                    data-model="{{ Str::lower($asset->model ?? '') }}"
                                    data-software="{{ $assetSoftwareIndex }}"
                                    data-software-exact="{{ $assetSoftwareExactTerms->join('||') }}"
                                    data-ram="{{ $asset->ram_gb ?? '' }}"
                                    data-storage="{{ $asset->storage_gb ?? '' }}"
                                    data-last-seen="{{ $asset->last_seen_at?->toIso8601String() ?? '' }}"
                                    data-asset-branch-id="{{ $asset->branch_id ?? '' }}"
                                    data-asset-branch-name="{{ optional($asset->branch)->name ?? '' }}"
                                    data-asset-node-id="{{ $asset->node_id ?? '' }}"
                                    data-asset-equipment-type="{{ $asset->equipment_type ?? '' }}"
                                    data-asset-hostname="{{ $asset->hostname ?? '' }}"
                                    data-asset-tag="{{ $asset->asset_tag ?? '' }}"
                                    data-asset-assigned-user="{{ $asset->assigned_user ?? '' }}"
                                    data-asset-notes="{{ $asset->notes ?? '' }}"
                                    data-asset-responsiva-reference="{{ data_get($asset->details, 'responsiva.reference', '') }}"
                                    data-asset-purchase-order-number="{{ data_get($asset->details, 'procurement.purchase_order_number', '') }}"
                                    data-asset-invoice-folio="{{ $assetInvoiceFolio }}"
                                    data-asset-assignment-invoice-folio="{{ data_get($latestAssignmentLog, 'invoice_folio', '') }}"
                                    data-asset-assignment-supplier="{{ data_get($latestAssignmentLog, 'supplier', '') }}"
                                    data-asset-assignment-delivery-date="{{ data_get($latestAssignmentLog, 'assigned_at', '') }}"
                                    data-asset-assignment-received-by="{{ data_get($latestAssignmentLog, 'received_by', '') }}"
                                    data-asset-assignment-change-reason="{{ data_get($latestAssignmentLog, 'change_reason', '') }}"
                                    data-asset-inventory-meta="{{ $inventoryMetaSummary }}"
                                    data-asset-hardware-summary="{{ $hardwareSummary }}"
                                    data-asset-software-summary="{{ $softwareSummary }}"
                                    data-asset-programs-summary="{{ $topInstalledPrograms }}"
                                    data-asset-admin-history='@json(collect(data_get($asset->details, "admin_history", []))->take(-20)->values())'
                                >
                                    <td class="fw-semibold">
                                        @if($asset->asset_tag)
                                            {{ $asset->asset_tag }}
                                        @endif
                                        {{ $asset->hostname }}
                                        <div class="text-muted small">
                                            {{ collect([
                                                $asset->brand && $asset->model ? $asset->brand . ' ' . $asset->model : ($asset->brand ?: $asset->model),
                                                $asset->serial_number ? 'S/N ' . $asset->serial_number : null,
                                                $asset->domain_name ? 'Dominio ' . $asset->domain_name : null,
                                                $asset->primary_ip_address ? 'IP ' . $asset->primary_ip_address : null,
                                            ])->filter()->join(' · ') ?: 'Sin resumen de inventario' }}
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark">{{ $assetEquipmentTypes[$asset->equipment_type] ?? $asset->equipment_type }}</span></td>
                                    <td>{{ optional($asset->branch)->name ?? '—' }}</td>
                                    @php
                                        $assetStatusBadgeClass = match ($asset->status) {
                                            'in_use' => 'success',
                                            'stock' => 'primary',
                                            'repair' => 'danger',
                                            'retired' => 'secondary',
                                            default => 'warning',
                                        };
                                    @endphp
                                    <td><span class="badge bg-{{ $assetStatusBadgeClass }}">{{ $asset->statusLabel() }}</span></td>
                                    <td>
                                        @php
                                            $hasHeartbeat = $asset->last_seen_at !== null;
                                            $isMonitoringActive = $hasHeartbeat && $asset->last_seen_at->greaterThan(now()->subMinutes($monitoringOnlineWindowMinutes ?? 10));
                                            $cpuUsage = $asset->last_cpu_usage_percent;
                                            $memoryUsage = $asset->last_memory_usage_percent;
                                            $diskUsage = $asset->last_disk_usage_percent;
                                            $hasMonitoringMetrics = $cpuUsage !== null || $memoryUsage !== null || $diskUsage !== null;
                                            $isCriticalMetric = collect([$cpuUsage, $memoryUsage, $diskUsage])->filter(fn ($value) => $value !== null)->contains(fn ($value) => (float) $value >= 90);
                                            $monitorStateClass = !$hasHeartbeat
                                                ? 'offline'
                                                : ($isCriticalMetric ? 'critical' : ($isMonitoringActive ? 'online' : 'offline'));
                                            $monitorStateLabel = !$hasHeartbeat
                                                ? 'Sin agente'
                                                : ($isCriticalMetric ? 'Crítico' : ($isMonitoringActive ? 'Monitoreando' : 'Sin señal'));
                                            $inventoryMetaCompact = \Illuminate\Support\Str::limit((string) $inventoryMetaSummary, 48);
                                        @endphp
                                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                                <span>{{ $asset->last_seen_at?->format('d/m H:i') ?? '—' }}</span>
                                                <span class="monitor-pill {{ $monitorStateClass }}">{{ $monitorStateLabel }}</span>
                                            </div>
                                            @if ($hasHeartbeat && $hasMonitoringMetrics)
                                                <div class="inventory-telemetry-badges mt-1">
                                                    <span class="badge bg-{{ $monitoringBadgeClass('cpu', $cpuUsage) }}" data-bs-toggle="tooltip" title="Uso de CPU">CPU {{ $cpuUsage !== null ? number_format((float) $cpuUsage, 1) . '%' : 'N/A' }}</span>
                                                    <span class="badge bg-{{ $monitoringBadgeClass('ram', $memoryUsage) }}" data-bs-toggle="tooltip" title="Uso de RAM">RAM {{ $memoryUsage !== null ? number_format((float) $memoryUsage, 1) . '%' : 'N/A' }}</span>
                                                    <span class="badge bg-{{ $monitoringBadgeClass('disco', $diskUsage) }}" data-bs-toggle="tooltip" title="Uso de disco">DSK {{ $diskUsage !== null ? number_format((float) $diskUsage, 1) . '%' : 'N/A' }}</span>
                                                </div>
                                            @elseif ($hasHeartbeat)
                                                <div class="small mt-1 text-muted">Sin métricas</div>
                                            @endif
                                            <div class="text-muted small" data-bs-toggle="tooltip" title="{{ $inventoryMetaSummary }}">
                                                {{ $inventoryMetaCompact }}
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1 inventory-action-btn js-open-asset-detail" aria-label="Info adicional" data-bs-toggle="tooltip" title="Info adicional">
                                                <i class="bi bi-info-circle" aria-hidden="true"></i>
                                            </button>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1 inventory-actions">
                                            <a href="{{ url('/admin/computer-assets/' . $asset->id . '/responsiva/preview') }}" class="btn btn-sm btn-outline-secondary inventory-action-btn" target="_blank" rel="noopener" aria-label="Ver responsiva" data-bs-toggle="tooltip" title="Ver responsiva">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                            </a>
                                            <a href="{{ url('/admin/computer-assets/' . $asset->id . '/responsiva') }}" class="btn btn-sm btn-outline-secondary inventory-action-btn" aria-label="Descargar responsiva" data-bs-toggle="tooltip" title="Descargar responsiva">
                                                <i class="bi bi-filetype-pdf" aria-hidden="true"></i>
                                            </a>
                                            <a href="{{ url('/admin/computer-assets/' . $asset->id . '/assignment-log') }}" class="btn btn-sm btn-outline-secondary inventory-action-btn" target="_blank" rel="noopener" aria-label="Abrir bitácora" data-bs-toggle="tooltip" title="Bitácora">
                                                <i class="bi bi-journal-text" aria-hidden="true"></i>
                                            </a>
                                            @if ($hasInvoiceDocument)
                                            <a href="{{ url('/admin/computer-assets/' . $asset->id . '/invoice-document') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary inventory-action-btn" aria-label="Ver factura" data-bs-toggle="tooltip" title="Ver factura">
                                                <i class="bi bi-receipt" aria-hidden="true"></i>
                                            </a>
                                            @endif
                                            @if ($canInventoryManage)
                                            <button type="button" class="btn btn-sm btn-outline-info js-request-transfer-asset inventory-action-btn" data-bs-toggle="modal" data-bs-target="#modalAssetTransferRequest" aria-label="Solicitar traslado" title="Solicitar traslado">
                                                <i class="bi bi-send" aria-hidden="true"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning js-reassign-asset inventory-action-btn" data-bs-toggle="modal" data-bs-target="#modalAssetReassign" aria-label="Reasignar activo" title="Reasignar">
                                                <i class="bi bi-arrow-left-right text-dark" aria-hidden="true"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary js-edit-asset inventory-action-btn" data-bs-toggle="modal" data-bs-target="#modalAsset" aria-label="Editar activo" title="Editar">
                                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Sin activos registrados</td></tr>
                            @endforelse
                            <tr id="inventoryAssetsNoResults" class="d-none">
                                <td colspan="6" class="text-center text-muted py-4">No se encontraron activos con esos filtros</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== SOFTWARE SYSTEMS SECTION (10) ===== --}}
        @if ($canInventoryCatalogsView)
        <div class="tab-pane fade" id="section-software">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Sistemas / Aplicaciones</h3>
                    <p class="text-muted small">Sistemas de software implementados en la infraestructura</p>
                </div>
                @if ($canTopologyManage)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSoftware">
                    <i class="bi bi-plus-circle"></i> Nuevo sistema
                </button>
                @endif
            </div>

            <div class="row g-2 mb-2">
                <div class="col-md-9">
                    <input type="text" id="softwareSystemsFilterInput" class="form-control form-control-sm" placeholder="Filtrar sistemas por nombre, versión o estado...">
                </div>
                <div class="col-md-3 d-grid">
                    <button type="button" id="softwareSystemsFilterReset" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tableSoftware">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th class="text-center">Nodos</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($systems->take(8) as $system)
                                <tr>
                                    <td class="fw-semibold">{{ $system->name }}</td>
                                    <td class="small">{{ Str::limit($system->details['description'] ?? '', 50) ?? '—' }}</td>
                                    <td class="text-center"><span class="badge bg-{{ $system->node_id ? 'info' : 'light text-dark' }}">{{ $system->node_id ? $system->node?->name ?? '—' : '—' }}</span></td>
                                    <td><span class="badge bg-info">Instalado</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Sin sistemas registrados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== RELATIONS SECTION (11) ===== --}}
        @if ($canTopologyManage)
        <div class="tab-pane fade" id="section-relations">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Relaciones</h3>
                    <p class="text-muted small">Vínculos entre nodos: enlace primario, respaldo, cascada, etc.</p>
                </div>
                @if ($canTopologyManage)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalRelation">
                    <i class="bi bi-plus-circle"></i> Nueva relación
                </button>
                @endif
            </div>

            @php
                $relationFilterTypes = $relations->pluck('relation_type')->filter()->unique()->sort()->values();
            @endphp

            <div class="row g-2 mb-2">
                <div class="col-md-8">
                    <input type="text" id="relationsFilterInput" class="form-control form-control-sm" placeholder="Filtrar relaciones por nodos o tipo...">
                </div>
                <div class="col-md-4">
                    <select id="relationsFilterType" class="form-select form-select-sm">
                        <option value="">Todos los tipos</option>
                        @foreach ($relationFilterTypes as $relationType)
                            <option value="{{ Str::lower($relationType) }}">{{ ucfirst(str_replace('_', ' ', $relationType)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="button" id="relationsFilterReset" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tableRelations">
                        <thead class="table-light">
                            <tr>
                                <th>Desde · Hacia</th>
                                <th>Tipo de relación</th>
                                <th>Peso</th>
                                <th style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($relations->take(8) as $relation)
                                <tr data-relation-type="{{ Str::lower($relation->relation_type ?? '') }}">
                                    <td class="fw-semibold">
                                        <code class="text-muted small">{{ optional($relation->fromNode)->code }}</code>
                                        {{ optional($relation->fromNode)->name }}
                                        <i class="bi bi-arrow-right text-muted"></i>
                                        <code class="text-muted small">{{ optional($relation->toNode)->code }}</code>
                                        {{ optional($relation->toNode)->name }}
                                    </td>
                                    <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $relation->relation_type)) }}</span></td>
                                    <td><span class="badge bg-light text-dark">{{ $relation->weight ?? '1' }}</span></td>
                                    <td>
                                        @if ($canTopologyManage)
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalRelation">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        @else
                                        <span class="text-muted small">Solo lectura</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Sin relaciones registradas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>

</div>

{{-- ===== MODALS ===== --}}

{{-- MODAL: Asset Detail (monitoring) --}}
<div class="modal fade" id="assetDetailModal" tabindex="-1" aria-labelledby="assetDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assetDetailModalLabel">Detalle del activo</h5>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <span class="monitor-pill" id="adModalStatus">—</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body" id="assetDetailBody">
                <div class="text-center py-4 text-muted">Cargando...</div>
            </div>
            <div class="modal-footer justify-content-between">
                <small class="text-muted" id="adModalUpdated"></small>
                <div class="d-flex align-items-center gap-2">
                    @if ($canInventoryManage)
                    <button type="button" class="btn btn-sm btn-outline-warning" id="adModalReassignBtn">
                        <i class="bi bi-arrow-left-right"></i> Reasignar equipo
                    </button>
                    @endif
                    <button type="button" class="btn btn-sm btn-outline-primary" id="adModalRefreshBtn">Actualizar ahora</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: My Signature --}}
<div class="modal fade" id="modalMySignature" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/my-signature') }}" id="modalMySignatureForm">
                @csrf
                <input type="hidden" name="signature_data_url" id="signatureDataUrlInput" value="">
                <input type="hidden" name="clear_signature" id="signatureClearInput" value="0">
                <div class="modal-header">
                    <h5 class="modal-title">Mi firma digital</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Dibuja tu firma para usarla al generar la responsiva PDF.</p>
                    <div class="border rounded p-2 mb-2 bg-white">
                        <canvas id="signatureCanvas" style="width:100%; height:180px; border:1px dashed #cbd5e1; border-radius:6px;"></canvas>
                    </div>
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="signatureClearCanvasBtn">Limpiar trazo</button>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Confirma con tu contraseña</label>
                        <input type="password" name="current_password" class="form-control" required autocomplete="current-password" placeholder="Contraseña actual">
                    </div>
                    <input type="hidden" id="currentUserSignatureDataUrl" value="{{ $currentUserSignatureDataUrl ?? '' }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" id="signatureDeleteBtn">Eliminar firma</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar firma</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: Branch --}}
@if ($canTopologyManage)
<div class="modal fade" id="modalBranch" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/branches') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nueva sede</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la sede</label>
                        <input type="text" name="branch_name" class="form-control" required placeholder="Ej: Oficina Central">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="branch_address" class="form-control" placeholder="Dirección completa">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL: NodeType --}}
@if ($canTopologyManage)
<div class="modal fade" id="modalNodeType" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/node-types') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo tipo de nodo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej: Router de borde">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug (ID único)</label>
                        <input type="text" name="slug" class="form-control" required placeholder="ej: router-borde">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Physical Space --}}
@if ($canTopologyManage)
<div class="modal fade" id="modalSpace" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/spaces') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo espacio físico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del espacio</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej: Sala de servidores">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sede</label>
                        <select name="branch_id" class="form-select" required>
                            <option value="">Selecciona...</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de espacio</label>
                        <select name="space_type" class="form-select" required>
                            <option value="room">Sala</option>
                            <option value="floor">Piso</option>
                            <option value="area">Área</option>
                            <option value="cabinet">Gabinete</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Node --}}
@if ($canTopologyManage)
<div class="modal fade" id="modalNode" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/nodes') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo nodo de red</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Código / Identificador</label>
                            <input type="text" name="code" class="form-control" required placeholder="R-001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="name" class="form-control" required placeholder="Router principal">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de nodo</label>
                            <select name="node_type_id" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($nodeTypes as $nt)
                                    <option value="{{ $nt->id }}">{{ $nt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sede</label>
                            <select name="branch_id" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Espacio físico</label>
                            <select name="physical_space_id" class="form-select">
                                <option value="">Sin asignar</option>
                                @foreach ($spaces as $space)
                                    <option value="{{ $space->id }}">{{ $space->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Equipment Brand --}}
@if ($canInventoryManage)
<div class="modal fade" id="modalBrand" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/equipment-brands') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nueva marca de equipo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la marca</label>
                        <input type="text" name="brand_name" class="form-control" required maxlength="120" placeholder="Ej: Cisco, Fortinet, Ubiquiti">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Equipment Model --}}
@if ($canInventoryManage)
<div class="modal fade" id="modalModel" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/equipment-models') }}" id="formModel">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo modelo de equipo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Marca</label>
                            <select name="eqmodel_brand_id" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($equipmentBrands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de equipo</label>
                            <select name="eqmodel_equipment_type" class="form-select" id="modalModelType" required>
                                @foreach ($equipmentModelTypes as $etv => $etl)
                                    <option value="{{ $etv }}">{{ $etl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nombre del modelo</label>
                            <input type="text" name="eqmodel_name" class="form-control" maxlength="120" required placeholder="Ej: UAP-AC-PRO">
                        </div>
                        <div id="modalApFields" class="col-12" style="display: none;">
                            <hr>
                            <h6 class="text-muted mb-3"><i class="bi bi-broadcast"></i> Parámetros RF</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Radio mín (m)</label>
                                    <input type="number" name="eqmodel_radius_min" class="form-control form-control-sm" min="0.1" step="0.1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Radio máx (m)</label>
                                    <input type="number" name="eqmodel_radius_max" class="form-control form-control-sm" min="0.1" step="0.1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Señal dBm</label>
                                    <input type="number" name="eqmodel_signal_dbm" class="form-control form-control-sm" min="-120" max="0" step="1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Altura (m)</label>
                                    <input type="number" name="eqmodel_mount_height_m" class="form-control form-control-sm" min="0.1" step="0.1">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea name="eqmodel_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Floor Plan --}}
@if ($canTopologyManage)
<div class="modal fade" id="modalFloorPlan" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/floor-plans') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="floor_plan_mode" value="upload">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo plano</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Sede</label>
                            <select name="floor_plan_branch_id" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Espacio (opcional)</label>
                            <select name="floor_plan_space_id" class="form-select">
                                <option value="">Sin asignar</option>
                                @foreach ($spaces as $space)
                                    <option value="{{ $space->id }}">{{ $space->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nombre del plano</label>
                            <input type="text" name="floor_plan_name" class="form-control" required maxlength="140" placeholder="Ej: Plano oficina central">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Archivo del plano</label>
                            <input type="file" name="floor_plan_file" class="form-control" accept=".png,.pdf,.dwg,.dxf,.svg" required>
                            <small class="text-muted">Formatos permitidos: PNG, PDF, DWG, DXF, SVG (máx. 40 MB).</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ancho (px)</label>
                            <input type="number" name="floor_plan_blank_width" class="form-control" min="400" max="6000" value="1400">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alto (px)</label>
                            <input type="number" name="floor_plan_blank_height" class="form-control" min="300" max="4000" value="900">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Asset --}}
@if ($canInventoryManage)
<div class="modal fade" id="modalAsset" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/computer-assets') }}" id="modalAssetForm">
                @csrf
                <input type="hidden" name="_method" id="modalAssetMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAssetTitle">Nuevo activo TI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Sede</label>
                            <select name="asset_branch_id" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nodo (opcional)</label>
                            <select name="asset_node_id" class="form-select">
                                <option value="">Sin asignar</option>
                                @foreach ($nodes as $node)
                                    <option value="{{ $node->id }}">{{ $node->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo</label>
                            <select name="asset_equipment_type" class="form-select" required>
                                @foreach ($assetEquipmentTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select name="asset_status" class="form-select" required>
                                @foreach ($assetStatusOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hostname</label>
                            <input type="text" name="asset_hostname" class="form-control" maxlength="120">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Etiqueta</label>
                            <input type="text" name="asset_tag" class="form-control" maxlength="120">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Responsable</label>
                            <input type="text" name="asset_assigned_user" class="form-control" maxlength="120" placeholder="Nombre del resguardante">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Responsiva / folio</label>
                            <input type="text" name="asset_responsiva_reference" class="form-control" maxlength="120" placeholder="Ej. RESP-2026-014">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Orden de compra</label>
                            <input type="text" name="asset_purchase_order_number" class="form-control" maxlength="120" placeholder="Ej. OC-2026-001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Folio factura</label>
                            <input type="text" name="asset_assignment_invoice_folio" class="form-control" maxlength="120" placeholder="Ej. FAC-2026-00122">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Proveedor</label>
                            <input type="text" name="asset_assignment_supplier" class="form-control" maxlength="160" placeholder="Nombre del proveedor">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha entrega / asignación</label>
                            <input type="date" name="asset_assignment_delivery_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quien recibe</label>
                            <input type="text" name="asset_assignment_received_by" class="form-control" maxlength="120" placeholder="Nombre de quien recibe">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Causa de cambio de asignación</label>
                            <textarea name="asset_assignment_change_reason" rows="2" class="form-control" placeholder="Motivo del movimiento o reasignación"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas del activo</label>
                            <textarea name="asset_notes" rows="2" class="form-control" placeholder="Observaciones generales del equipo"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Interacción / movimiento (se guarda en historial)</label>
                            <textarea name="asset_interaction_note" rows="2" class="form-control" placeholder="Ej. Se asigna temporalmente al área de Contabilidad"></textarea>
                        </div>
                        <div class="col-12" id="modalAssetHistoryWrap" style="display:none;">
                            <label class="form-label mb-1">Historial administrativo reciente</label>
                            <div id="modalAssetHistory" class="small border rounded p-2 bg-light" style="max-height:180px; overflow:auto;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="modalAssetSubmitButton">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Asset Bulk Import --}}
@if ($canInventoryManage)
<div class="modal fade" id="modalAssetImport" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/computer-assets/import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Carga masiva de activos TI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        Puedes cargar CSV o Excel. El sistema toma <strong>numero_serie</strong> como llave primaria: si existe, actualiza; si no existe, crea un nuevo activo.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Sede por defecto</label>
                            <select name="asset_import_branch_id" class="form-select">
                                <option value="">Usar la sede indicada en el layout</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (string) old('asset_import_branch_id', $currentContextBranchId ?? '') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <a href="{{ url('/admin/computer-assets/import-template') }}" class="btn btn-outline-secondary w-100">
                                Descargar layout base
                            </a>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Archivo de layout</label>
                            <input type="file" name="asset_import_file" class="form-control" accept=".csv,.txt,.xlsx,.xls,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                            <div class="form-text">Columnas sugeridas: sede_id, tipo_equipo, etiqueta, hostname, numero_serie, marca, modelo, procesador, ram_gb, tipo_almacenamiento, almacenamiento_gb, sistema_operativo, version_office, numero_orden_compra, proveedor, fecha_compra, garantia_hasta, observaciones. Las columnas adicionales se guardan como metadatos del activo.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Factura física (opcional, se vincula a todos los equipos del archivo)</label>
                            <input type="file" name="asset_import_invoice_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp">
                            <div class="form-text">Después podrás consultarla desde cada equipo contenido en esta carga.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Número de factura (opcional)</label>
                            <input type="text" name="asset_import_invoice_folio" class="form-control" maxlength="120" placeholder="Ej. FAC-2026-00122">
                            <div class="form-text">Se guardará en los equipos importados y podrá filtrarse en el inventario.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Importar activos</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Invoice Analyzer --}}
@if ($canInventoryManage)
<div class="modal fade" id="modalAssetInvoiceAnalyzer" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/computer-assets/invoice/analyze') }}" enctype="multipart/form-data" id="assetInvoiceAnalyzerForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Analizar factura (PDF/TXT)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        MVP gratuito local: extrae datos de facturas con texto digital. Si la factura es imagen/escaneo, el resultado puede ser parcial.
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Sede por defecto (opcional)</label>
                            <select name="asset_invoice_branch_id" class="form-select">
                                <option value="">Sin sede predefinida</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Archivo de factura</label>
                            <input type="file" name="asset_invoice_file" class="form-control" accept=".pdf,.txt,application/pdf,text/plain" required>
                            <div class="form-text">Se detectan automáticamente folio, proveedor, orden de compra y equipos candidatos.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark" id="assetInvoiceAnalyzerSubmitButton">
                        <span class="js-analyzer-submit-label">Analizar factura</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Invoice Draft Preview --}}
@if ($canInventoryManage && $hasInvoiceDraft)
<div class="modal fade" id="modalAssetInvoiceDraft" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Borrador detectado desde factura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 small mb-3">
                    <div class="col-md-3"><strong>Proveedor:</strong> {{ data_get($assetInvoiceDraft, 'supplier', 'N/A') }}</div>
                    <div class="col-md-3"><strong>Folio:</strong> {{ data_get($assetInvoiceDraft, 'invoice_folio', 'N/A') }}</div>
                    <div class="col-md-3"><strong>Orden compra:</strong> {{ data_get($assetInvoiceDraft, 'purchase_order_number', 'N/A') }}</div>
                    <div class="col-md-3"><strong>Fecha:</strong> {{ data_get($assetInvoiceDraft, 'invoice_date', 'N/A') }}</div>
                </div>

                <div class="alert alert-light border small mb-3">
                    Revisa los datos detectados. Si están correctos, importa y los activos quedarán en estado <strong>Pendiente de asignación</strong>.
                </div>

                @php
                    $draftProfileMatched = (bool) data_get($assetInvoiceDraft, 'profile_matched', false);
                    $draftKnownBrands = collect(data_get($assetInvoiceDraft, 'supplier_profile.known_brands', []))->filter()->values();
                    $draftKnownModels = collect(data_get($assetInvoiceDraft, 'supplier_profile.known_models', []))->filter()->values();
                    $draftSerialPrefixes = collect(data_get($assetInvoiceDraft, 'supplier_profile.serial_prefixes', []))->filter()->values();
                    $draftProfileLastUsedAt = data_get($assetInvoiceDraft, 'supplier_profile.last_used_at');
                    $draftProfileAudits = collect(data_get($assetInvoiceDraft, 'supplier_profile.audits', []))->filter(fn ($item) => is_array($item))->values();
                @endphp
                <div class="card border mb-3">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <div class="small fw-semibold">Perfil aprendido del proveedor</div>
                                <div class="small text-muted">
                                    Estado: {{ $draftProfileMatched ? 'Encontrado' : 'No encontrado' }}
                                    @if ($draftProfileLastUsedAt)
                                        · Último uso: {{ \Illuminate\Support\Carbon::parse($draftProfileLastUsedAt)->format('d/m/Y H:i') }}
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    Marcas: {{ $draftKnownBrands->take(8)->join(', ') ?: 'N/A' }}
                                </div>
                                <div class="small text-muted">
                                    Prefijos serie: {{ $draftSerialPrefixes->take(10)->join(', ') ?: 'N/A' }}
                                </div>
                                <div class="small text-muted">
                                    Modelos aprendidos: {{ $draftKnownModels->count() }}
                                </div>
                            </div>
                            <form method="POST" action="{{ url('/admin/computer-assets/invoice/vendor-profile/reset') }}" class="m-0">
                                @csrf
                                <input type="hidden" name="asset_invoice_supplier_name" value="{{ data_get($assetInvoiceDraft, 'supplier', '') }}">
                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Reiniciar el perfil aprendido de este proveedor?');">
                                    Reiniciar perfil proveedor
                                </button>
                            </form>
                        </div>
                        @if ($draftProfileAudits->isNotEmpty())
                        <hr class="my-2">
                        <div class="small fw-semibold mb-1">Historial reciente del perfil</div>
                        <div class="small text-muted">
                            @foreach ($draftProfileAudits as $audit)
                                <div>
                                    <span class="badge text-bg-light border">{{ data_get($audit, 'action', 'N/A') }}</span>
                                    {{ data_get($audit, 'changed_by_name', 'Sistema') }}
                                    · {{ data_get($audit, 'created_at') ? \Illuminate\Support\Carbon::parse((string) data_get($audit, 'created_at'))->format('d/m/Y H:i') : 'N/A' }}
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Incluir</th>
                                <th>Descripción</th>
                                <th>Tipo</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Serie</th>
                                <th>Estado serie</th>
                                <th>Confianza</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceDraftItemsTableBody">
                            @foreach (data_get($assetInvoiceDraft, 'items', []) as $index => $draftItem)
                            @php
                                $rowSerialStatus = Str::lower((string) data_get($draftItem, 'serial_status', 'dudosa'));
                            @endphp
                            <tr data-draft-index="{{ $index }}" class="{{ $rowSerialStatus === 'dudosa' ? 'table-warning' : '' }}">
                                <td>
                                    <input type="checkbox" class="form-check-input js-draft-include" checked>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm js-draft-description" value="{{ data_get($draftItem, 'description', '') }}">
                                </td>
                                <td>
                                    <select class="form-select form-select-sm js-draft-equipment-type">
                                        @foreach ($assetEquipmentTypes as $typeKey => $typeLabel)
                                            <option value="{{ $typeKey }}" @selected((string) data_get($draftItem, 'equipment_type', 'desktop') === (string) $typeKey)>{{ $typeLabel }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm js-draft-brand" value="{{ data_get($draftItem, 'brand', '') }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm js-draft-model" value="{{ data_get($draftItem, 'model', '') }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm js-draft-serial" value="{{ data_get($draftItem, 'serial_number', '') }}">
                                </td>
                                <td>
                                    @php
                                        $serialStatus = Str::lower((string) data_get($draftItem, 'serial_status', 'dudosa'));
                                        $serialBadgeClass = $serialStatus === 'validada' ? 'success' : 'warning text-dark';
                                        $serialStatusLabel = (string) data_get($draftItem, 'serial_status_label', 'Serie dudosa');
                                        $fieldConfidence = data_get($draftItem, 'field_confidence', []);
                                        $fieldBadgeClass = function (string $status): string {
                                            return match (Str::lower($status)) {
                                                'alta' => 'success',
                                                'media' => 'warning text-dark',
                                                default => 'danger',
                                            };
                                        };
                                    @endphp
                                    <span class="badge text-bg-{{ $serialBadgeClass }} js-draft-serial-status" data-status="{{ $serialStatus }}">{{ $serialStatusLabel }}</span>
                                </td>
                                <td>
                                    <div>{{ (float) data_get($draftItem, 'confidence', 0) }}</div>
                                    <div class="small mt-1 d-flex flex-wrap gap-1">
                                        @php
                                            $descStatus = (string) data_get($fieldConfidence, 'description.status', 'baja');
                                            $typeStatus = (string) data_get($fieldConfidence, 'equipment_type.status', 'baja');
                                            $brandStatus = (string) data_get($fieldConfidence, 'brand.status', 'baja');
                                            $modelStatus = (string) data_get($fieldConfidence, 'model.status', 'baja');
                                        @endphp
                                        <span class="badge text-bg-{{ $fieldBadgeClass($descStatus) }} js-draft-field-confidence" data-field="description">Desc {{ data_get($fieldConfidence, 'description.label', 'Baja') }}</span>
                                        <span class="badge text-bg-{{ $fieldBadgeClass($typeStatus) }} js-draft-field-confidence" data-field="equipment_type">Tipo {{ data_get($fieldConfidence, 'equipment_type.label', 'Baja') }}</span>
                                        <span class="badge text-bg-{{ $fieldBadgeClass($brandStatus) }} js-draft-field-confidence" data-field="brand">Marca {{ data_get($fieldConfidence, 'brand.label', 'Baja') }}</span>
                                        <span class="badge text-bg-{{ $fieldBadgeClass($modelStatus) }} js-draft-field-confidence" data-field="model">Modelo {{ data_get($fieldConfidence, 'model.label', 'Baja') }}</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <details>
                    <summary class="small text-primary" style="cursor:pointer;">Ver texto detectado</summary>
                    <div class="small text-muted border rounded p-2 bg-light mt-2" style="white-space: pre-wrap; max-height: 240px; overflow:auto;">{{ data_get($assetInvoiceDraft, 'raw_excerpt', '') }}</div>
                </details>
            </div>
            <div class="modal-footer">
                <form method="POST" action="{{ url('/admin/computer-assets/invoice/import') }}" class="row g-2 align-items-end w-100 m-0" id="invoiceDraftImportForm" data-base-draft='@json($assetInvoiceDraft)'>
                    @csrf
                    <input type="hidden" name="asset_invoice_payload" value='@json($assetInvoiceDraft)' id="assetInvoicePayloadInput">
                    <div class="col-md-5">
                        <label class="form-label small mb-1">Sede destino (opcional)</label>
                        <select name="asset_invoice_branch_id" class="form-select form-select-sm">
                            <option value="">Usar sede detectada en borrador</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-check-circle"></i> Importar borrador de factura
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Asset Reassign --}}
@if ($canInventoryManage)
<div class="modal fade" id="modalAssetReassign" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/computer-assets/0/reassign') }}" id="modalAssetReassignForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reasignar activo TI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border small mb-3" id="modalAssetReassignSummary">Selecciona un activo para reasignar.</div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Empleado actual</label>
                            <input type="text" class="form-control" id="modalReassignCurrentUser" value="" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nuevo empleado asignado</label>
                            <input type="text" name="asset_assigned_user" class="form-control" maxlength="120" placeholder="Nombre del nuevo resguardante" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha entrega / asignación</label>
                            <input type="date" name="asset_assignment_delivery_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quien recibe</label>
                            <input type="text" name="asset_assignment_received_by" class="form-control" maxlength="120" placeholder="Nombre de quien recibe">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Folio factura (opcional)</label>
                            <input type="text" name="asset_assignment_invoice_folio" class="form-control" maxlength="120">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Proveedor (opcional)</label>
                            <input type="text" name="asset_assignment_supplier" class="form-control" maxlength="160">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Causa de cambio</label>
                            <textarea name="asset_assignment_change_reason" rows="2" class="form-control" placeholder="Ej. Cambio de área a Soporte" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nota interna (opcional)</label>
                            <textarea name="asset_interaction_note" rows="2" class="form-control" placeholder="Comentario adicional para historial administrativo"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Confirmar reasignación</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Asset Transfer Request --}}
@if ($canInventoryManage)
<div class="modal fade" id="modalAssetTransferRequest" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/computer-assets/0/transfer-requests') }}" id="modalAssetTransferRequestForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Solicitar traslado de activo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border small mb-3" id="modalAssetTransferRequestSummary">Selecciona un activo para solicitar traslado.</div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Sede destino</label>
                            <select name="transfer_to_branch_id" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Agente destino</label>
                            <select name="transfer_to_user_id" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($transferAgents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}{{ $agent->email ? ' · ' . $agent->email : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Prioridad</label>
                            <select name="transfer_priority" class="form-select" required>
                                <option value="normal" selected>Normal</option>
                                <option value="high">Alta</option>
                                <option value="urgent">Urgente</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Motivo del traslado</label>
                            <textarea name="transfer_reason" rows="2" class="form-control" maxlength="1000" required placeholder="Ej. Renovación de equipo para mesa de soporte en sede Sur"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas adicionales (opcional)</label>
                            <textarea name="transfer_note" rows="2" class="form-control" maxlength="1000" placeholder="Información de entrega, empaquetado o prioridad"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info">Enviar solicitud</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if ($canInventoryCatalogsManage)
<div class="modal fade" id="modalCreateEquipmentTypeCatalog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/asset-equipment-type-catalogs') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo tipo de equipo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Clave</label>
                        <input type="text" name="catalog_key" class="form-control" maxlength="60" placeholder="ej: tablet" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Etiqueta</label>
                        <input type="text" name="catalog_label" class="form-control" maxlength="120" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Orden</label>
                        <input type="number" name="catalog_sort_order" class="form-control" value="100" min="0" max="9999">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="catalog_is_active" class="form-select">
                            <option value="1" selected>Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripcion (opcional)</label>
                        <textarea name="catalog_description" rows="2" class="form-control" maxlength="255"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear tipo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditEquipmentTypeCatalog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/asset-equipment-type-catalogs/0') }}" id="modalEditEquipmentTypeCatalogForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar tipo de equipo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Clave</label>
                        <input type="text" id="editEquipmentTypeCatalogKey" class="form-control" readonly>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Etiqueta</label>
                        <input type="text" name="catalog_label" id="editEquipmentTypeCatalogLabel" class="form-control" maxlength="120" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Orden</label>
                        <input type="number" name="catalog_sort_order" id="editEquipmentTypeCatalogSortOrder" class="form-control" min="0" max="9999">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="catalog_is_active" id="editEquipmentTypeCatalogIsActive" class="form-select">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripcion (opcional)</label>
                        <textarea name="catalog_description" id="editEquipmentTypeCatalogDescription" rows="2" class="form-control" maxlength="255"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCreateAssetStatusCatalog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/asset-status-catalogs') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo estado de activo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Clave</label>
                        <input type="text" name="catalog_key" class="form-control" maxlength="60" placeholder="ej: maintenance" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Etiqueta</label>
                        <input type="text" name="catalog_label" class="form-control" maxlength="120" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Orden</label>
                        <input type="number" name="catalog_sort_order" class="form-control" value="100" min="0" max="9999">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="catalog_is_active" class="form-select">
                            <option value="1" selected>Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripcion (opcional)</label>
                        <textarea name="catalog_description" rows="2" class="form-control" maxlength="255"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear estado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditAssetStatusCatalog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/asset-status-catalogs/0') }}" id="modalEditAssetStatusCatalogForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar estado de activo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Clave</label>
                        <input type="text" id="editAssetStatusCatalogKey" class="form-control" readonly>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Etiqueta</label>
                        <input type="text" name="catalog_label" id="editAssetStatusCatalogLabel" class="form-control" maxlength="120" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Orden</label>
                        <input type="number" name="catalog_sort_order" id="editAssetStatusCatalogSortOrder" class="form-control" min="0" max="9999">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="catalog_is_active" id="editAssetStatusCatalogIsActive" class="form-select">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripcion (opcional)</label>
                        <textarea name="catalog_description" id="editAssetStatusCatalogDescription" rows="2" class="form-control" maxlength="255"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Software --}}
@if ($canTopologyManage)
<div class="modal fade" id="modalSoftware" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/software') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo sistema</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="software_name" class="form-control" maxlength="120" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Versión</label>
                        <input type="text" name="software_version" class="form-control" maxlength="80">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nodo (opcional)</label>
                        <select name="software_node_id" class="form-select">
                            <option value="">Sin asignar</option>
                            @foreach ($nodes as $node)
                                <option value="{{ $node->id }}">{{ $node->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Relation --}}
@if ($canTopologyManage)
<div class="modal fade" id="modalRelation" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/relations') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nueva relación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nodo origen</label>
                        <select name="relation_from_node_id" class="form-select" required>
                            <option value="">Selecciona...</option>
                            @foreach ($nodes as $node)
                                <option value="{{ $node->id }}">{{ $node->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nodo destino</label>
                        <select name="relation_to_node_id" class="form-select" required>
                            <option value="">Selecciona...</option>
                            @foreach ($nodes as $node)
                                <option value="{{ $node->id }}">{{ $node->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de relación</label>
                        <input type="text" name="relation_type" class="form-control" maxlength="80" required placeholder="Ej: linked_to">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Peso preferido (opcional)</label>
                        <input type="number" name="relation_preferred_weight" class="form-control" min="1" max="999">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modelTypeSelect = document.getElementById('modalModelType');
    const apFields = document.getElementById('modalApFields');
    if (modelTypeSelect && apFields) {
        modelTypeSelect.addEventListener('change', function () {
            apFields.style.display = this.value === 'access-point' ? '' : 'none';
        });
    }
});
</script>

<style>
    .admin-hero {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: .6rem .85rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        background: #fff;
    }

    .admin-hero-title {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.2;
        color: #0f172a;
    }

    .admin-hero-subtitle {
        font-size: .85rem;
        color: #64748b;
    }

    .admin-signature-btn {
        color: #0f172a;
        border: 1px solid #cbd5e1;
        background: #ffffff;
    }

    .admin-signature-btn:hover,
    .admin-signature-btn:focus {
        color: #0f172a;
        border-color: #94a3b8;
        background: #f8fafc;
    }

    .sticky-navigation {
        position: sticky;
        top: 0;
        background: white;
        z-index: 100;
        padding: .45rem .6rem;
        border-bottom: 1px solid #dee2e6;
        border-radius: 8px;
    }

    .sticky-navigation-inner {
        display: flex;
        align-items: center;
        gap: .6rem;
    }

    .sticky-navigation-label {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #64748b;
        white-space: nowrap;
        padding: .2rem .45rem;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .sticky-navigation .nav {
        overflow-x: auto;
        flex-wrap: nowrap !important;
        white-space: nowrap;
        scrollbar-width: thin;
        -webkit-overflow-scrolling: touch;
        padding-bottom: .15rem;
    }

    .nav-pills .nav-link {
        border-radius: 6px;
        padding: 0.34rem 0.6rem;
        color: #495057;
        font-weight: 600;
        font-size: .78rem;
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
        background: #fff;
    }

    .nav-pills .nav-link:hover {
        background-color: #e7f1ff;
        color: #0c63e4;
    }

    .nav-pills .nav-link.active {
        background-color: #0c63e4;
        color: white;
        border-color: #0c63e4;
    }

    @media (max-width: 992px) {
        .sticky-navigation {
            padding: .35rem .5rem;
        }

        .sticky-navigation-label {
            display: none;
        }

        .nav-pills .nav-link {
            padding: .3rem .5rem;
            font-size: .75rem;
        }

        .nav-pills .nav-link i {
            display: none;
        }
    }

    @media (max-width: 576px) {
        .sticky-navigation {
            margin-bottom: .75rem !important;
        }

        .admin-hero {
            padding: .5rem .65rem;
        }

        .admin-hero-title {
            font-size: 1.05rem;
        }

        .admin-hero-subtitle {
            font-size: .78rem;
        }
    }

    .table thead {
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .badge {
        font-weight: 600;
        padding: 0.4rem 0.6rem;
    }

    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .modal-title {
        font-weight: 600;
        color: #0f172a;
    }

    .form-label {
        font-weight: 500;
        color: #334155;
        margin-bottom: 0.5rem;
    }

    .btn-sm {
        font-size: 0.875rem;
    }

    .tab-pane {
        animation: fadeIn 0.2s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    h3 {
        color: #0f172a;
        font-weight: 700;
    }

    .text-muted {
        color: #64748b !important;
    }

    code {
        background-color: #f1f5f9;
        padding: 0.2rem 0.4rem;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
    }

    .monitor-pill {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .monitor-pill.online   { background: #dcfce7; color: #166534; }
    .monitor-pill.offline  { background: #f1f5f9; color: #64748b; }
    .monitor-pill.critical { background: #fee2e2; color: #991b1b; }

    .mini-table { font-size: 0.8rem; }
    .mini-table th, .mini-table td { padding: 0.25rem 0.5rem; }

    .inventory-filter-panel {
        border: 1px solid #dbe4f0;
        border-radius: 12px;
        background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
    }

    .inventory-compact-toolbar {
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        background: #f8fafc;
        padding: .65rem .75rem;
    }

    .inventory-compact-toolbar .btn {
        border-radius: 999px;
    }

    .inventory-results-card {
        border: 1px solid #dbe4f0;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    }

    #inventoryAssetsTableWrapper {
        max-height: 64vh;
        overflow: auto;
    }

    #inventoryAssetsTable thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #f8f9fa;
        padding-top: .42rem;
        padding-bottom: .42rem;
        font-size: .8rem;
    }

    #inventoryAssetsTable tbody td {
        padding-top: .42rem;
        padding-bottom: .42rem;
        font-size: .83rem;
        line-height: 1.25;
    }

    #inventoryAssetsTable .btn.btn-sm {
        padding: .2rem .45rem;
        font-size: .76rem;
    }

    .inventory-actions {
        align-items: center;
    }

    .inventory-action-btn {
        width: 30px;
        height: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .inventory-telemetry-badges {
        display: flex;
        flex-wrap: wrap;
        gap: .2rem;
    }

    .inventory-telemetry-badges .badge {
        padding: .15rem .35rem;
        font-size: .68rem;
        line-height: 1.1;
    }

    @media (max-width: 576px) {
        #inventoryAssetsTable tbody tr .inventory-telemetry-badges {
            display: none;
        }

        #inventoryAssetsTable tbody tr:hover .inventory-telemetry-badges,
        #inventoryAssetsTable tbody tr:focus-within .inventory-telemetry-badges {
            display: flex;
        }

        #inventoryAssetsTable tbody tr .inventory-action-btn,
        #inventoryAssetsTable tbody tr .js-open-asset-detail.inventory-action-btn {
            width: 28px;
            height: 28px;
        }
    }

    @media (max-width: 992px) {
        #inventoryAssetsTableWrapper {
            max-height: 58vh;
        }
    }
</style>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const metricThresholds = @json($monitoringMetricThresholds);
    const rowsEl  = document.getElementById('monitoringRows');
    const adModal = document.getElementById('assetDetailModal');
    const adModalLabel      = document.getElementById('assetDetailModalLabel');
    const adModalStatus     = document.getElementById('adModalStatus');
    const adModalBody       = document.getElementById('assetDetailBody');
    const adModalUpdated    = document.getElementById('adModalUpdated');
    const adModalRefreshBtn = document.getElementById('adModalRefreshBtn');
    const adModalReassignBtn = document.getElementById('adModalReassignBtn');
    const modalMySignature = document.getElementById('modalMySignature');
    const modalMySignatureForm = document.getElementById('modalMySignatureForm');
    const signatureCanvas = document.getElementById('signatureCanvas');
    const signatureDataUrlInput = document.getElementById('signatureDataUrlInput');
    const signatureClearInput = document.getElementById('signatureClearInput');
    const signatureClearCanvasBtn = document.getElementById('signatureClearCanvasBtn');
    const signatureDeleteBtn = document.getElementById('signatureDeleteBtn');
    const currentUserSignatureDataUrlInput = document.getElementById('currentUserSignatureDataUrl');
    let adActiveAssetId = null;
    let adRefreshTimer  = null;
    let signatureDrawing = false;
    let signatureHasStroke = false;

    // ── Helpers ────────────────────────────────────────────────────────────────
    const esc = (value) => String(value ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    const toText = (value, fallback = 'N/A') => {
        if (value === null || value === undefined) return fallback;
        if (Array.isArray(value)) {
            const items = value.map((i) => toText(i, '')).filter((i) => i && i !== 'N/A');
            return items.length ? items.join(', ') : fallback;
        }
        const text = String(value).trim();
        return text === '' ? fallback : text;
    };

    const toHtml = (value, fallback = 'N/A') => esc(toText(value, fallback));

    const fmtIsoDate = (value, fallback = 'N/A') => {
        if (!value) return fallback;
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? toText(value, fallback) : date.toLocaleString();
    };

    const fmtPct   = (v) => (v !== null && v !== undefined) ? `${Number(v).toFixed(1)}%` : 'N/A';
    const fmtUptime = (s) => {
        if (!s) return 'N/A';
        const d = Math.floor(s / 86400), h = Math.floor((s % 86400) / 3600), m = Math.floor((s % 3600) / 60);
        return d > 0 ? `${d}d ${h}h ${m}m` : h > 0 ? `${h}h ${m}m` : `${m}m`;
    };

    const metricBarColor = (label, pct) => {
        const val = (pct !== null && pct !== undefined) ? Math.min(100, Math.max(0, Number(pct))) : 0;
        const normalized = String(label || '').toLowerCase();
        const config = metricThresholds[normalized] || null;

        if (config) {
            if (config.danger !== null && val > config.danger) return 'bg-danger';
            if (config.warning !== null && val > config.warning) return 'bg-warning';
            return `bg-${config.normal || 'secondary'}`;
        }

        if (val >= 90) return 'bg-danger';
        if (val >= 70) return 'bg-warning';
        return 'bg-success';
    };

    const gauge = (label, pct) => {
        const val = (pct !== null && pct !== undefined) ? Math.min(100, Math.max(0, Number(pct))) : 0;
        const color = metricBarColor(label, val);
        return `<div class="mb-2">
            <div class="d-flex justify-content-between small mb-1"><span>${label}</span><span class="fw-semibold">${fmtPct(pct)}</span></div>
            <div class="progress" style="height:10px">
                <div class="progress-bar ${color}" role="progressbar" style="width:${val}%" aria-valuenow="${val}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>`;
    };

    const renderFactList = (items) => items.map((item) => `
        <div class="small mb-1"><span class="text-muted">${esc(item.label)}:</span> ${toHtml(item.value)}</div>
    `).join('');

    const renderProgramRows = (programs) => {
        if (!Array.isArray(programs) || programs.length === 0) {
            return '<tr><td colspan="4" class="text-center text-muted">Sin inventario de software aún</td></tr>';
        }
        return programs.map((program) => `
            <tr>
                <td>${toHtml(program.name)}</td>
                <td>${toHtml(program.version)}</td>
                <td>${toHtml(program.publisher)}</td>
                <td>${toHtml(program.install_date)}</td>
            </tr>
        `).join('');
    };

    const renderBadgeList = (items, emptyLabel = 'N/A') => {
        if (!Array.isArray(items) || items.length === 0) {
            return `<span class="text-muted small">${esc(emptyLabel)}</span>`;
        }
        return items.map((item) => `<span class="badge text-bg-light border me-1 mb-1">${toHtml(item)}</span>`).join('');
    };

    const setupSignatureCanvas = () => {
        if (!signatureCanvas) return null;
        const ctx = signatureCanvas.getContext('2d');
        if (!ctx) return null;

        const resizeCanvas = () => {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const rect = signatureCanvas.getBoundingClientRect();
            signatureCanvas.width = Math.floor(rect.width * ratio);
            signatureCanvas.height = Math.floor(rect.height * ratio);
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, rect.width, rect.height);
            ctx.strokeStyle = '#0f172a';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        };

        const pointerPos = (event) => {
            const rect = signatureCanvas.getBoundingClientRect();
            const source = event.touches?.[0] || event;
            return {
                x: source.clientX - rect.left,
                y: source.clientY - rect.top,
            };
        };

        const drawStart = (event) => {
            event.preventDefault();
            signatureDrawing = true;
            signatureHasStroke = true;
            const { x, y } = pointerPos(event);
            ctx.beginPath();
            ctx.moveTo(x, y);
        };

        const drawMove = (event) => {
            if (!signatureDrawing) return;
            event.preventDefault();
            const { x, y } = pointerPos(event);
            ctx.lineTo(x, y);
            ctx.stroke();
        };

        const drawEnd = (event) => {
            if (!signatureDrawing) return;
            event.preventDefault();
            signatureDrawing = false;
            ctx.closePath();
        };

        signatureCanvas.addEventListener('mousedown', drawStart);
        signatureCanvas.addEventListener('mousemove', drawMove);
        signatureCanvas.addEventListener('mouseup', drawEnd);
        signatureCanvas.addEventListener('mouseleave', drawEnd);
        signatureCanvas.addEventListener('touchstart', drawStart, { passive: false });
        signatureCanvas.addEventListener('touchmove', drawMove, { passive: false });
        signatureCanvas.addEventListener('touchend', drawEnd, { passive: false });

        return {
            ctx,
            resizeCanvas,
            clear: () => {
                const rect = signatureCanvas.getBoundingClientRect();
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, rect.width, rect.height);
                signatureHasStroke = false;
            },
            loadDataUrl: (dataUrl) => {
                if (!dataUrl) return;
                const image = new Image();
                image.onload = () => {
                    const rect = signatureCanvas.getBoundingClientRect();
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, rect.width, rect.height);

                    const maxW = rect.width - 16;
                    const maxH = rect.height - 16;
                    const ratio = Math.min(maxW / image.width, maxH / image.height, 1);
                    const drawW = image.width * ratio;
                    const drawH = image.height * ratio;
                    const x = (rect.width - drawW) / 2;
                    const y = (rect.height - drawH) / 2;
                    ctx.drawImage(image, x, y, drawW, drawH);
                    signatureHasStroke = true;
                };
                image.src = dataUrl;
            },
        };
    };

    const signaturePad = setupSignatureCanvas();

    const renderDetailBody = (data) => {
        const a = data.asset;
        const metrics = data.metrics || [];
        const inventory = data.inventory || {};
        const summary = inventory.summary || {};
        const hardware = inventory.hardware || {};
        const software = inventory.software || {};
        const processors       = Array.isArray(hardware.processors)        ? hardware.processors : [];
        const memoryModules    = Array.isArray(hardware.memory_modules)    ? hardware.memory_modules : [];
        const physicalDisks    = Array.isArray(hardware.physical_disks)    ? hardware.physical_disks : [];
        const logicalDisks     = Array.isArray(hardware.logical_disks)     ? hardware.logical_disks : [];
        const networkAdapters  = Array.isArray(hardware.network_adapters)  ? hardware.network_adapters : [];
        const videoControllers = Array.isArray(hardware.video_controllers) ? hardware.video_controllers : [];
        const antivirus        = Array.isArray(software.antivirus)         ? software.antivirus : [];
        const installedPrograms = Array.isArray(software.installed_programs) ? software.installed_programs : [];
        const inventoryCapturedAt = inventory.captured_at ? fmtIsoDate(inventory.captured_at) : 'Pendiente';
        const inventoryScopeLabel = inventory.capture_scope === 'extended'
            ? 'Extendido'
            : (inventory.capture_scope === 'lightweight' ? 'Ligero' : null);
        const inventoryLastExtendedAt = inventory.last_extended_captured_at
            ? fmtIsoDate(inventory.last_extended_captured_at)
            : null;

        adModalLabel.textContent = a.name || 'Activo';
        adModalStatus.className  = `monitor-pill ${a.online ? 'online' : 'offline'}`;
        adModalStatus.textContent = a.online ? 'Online' : 'Offline';
        adModalUpdated.textContent = `Actualizado: ${new Date().toLocaleTimeString()}`;
        if (adModalReassignBtn) {
            const assignedUserLabel = String(a.assigned_user || '').trim() || 'Sin asignar';
            adModalReassignBtn.innerHTML = `<i class="bi bi-arrow-left-right"></i> Reasignar: ${escapeHtml(assignedUserLabel)}`;
        }

        const lastMetric = metrics[0] || null;

        adModalBody.innerHTML = `
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="border rounded-3 p-3">
                        <div class="small text-muted mb-1">Hostname</div>
                        <div class="fw-semibold">${toHtml(a.hostname)}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3">
                        <div class="small text-muted mb-1">Sede</div>
                        <div class="fw-semibold">${toHtml(a.branch)}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3">
                        <div class="small text-muted mb-1">Último heartbeat</div>
                        <div class="fw-semibold">${toHtml(a.last_seen_human)}</div>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-lg-7">
                    <div class="border rounded-3 p-3">
                        <div class="small text-muted mb-2 fw-semibold">Rendimiento actual</div>
                        ${gauge('CPU', a.cpu)}
                        ${gauge('RAM', a.memory)}
                        ${gauge('Disco', a.disk)}
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Resumen del equipo</div>
                        ${renderFactList([
                            { label: 'Marca',          value: summary.brand },
                            { label: 'Modelo',         value: summary.model },
                            { label: 'Serie',          value: summary.serial },
                            { label: 'Tipo',           value: summary.equipment_type_label },
                            { label: 'CPU',            value: summary.cpu },
                            { label: 'RAM',            value: summary.ram_gb ? `${summary.ram_gb} GB` : null },
                            { label: 'Almacenamiento', value: summary.storage_gb ? `${summary.storage_gb} GB ${toText(summary.storage_type_label, '')}`.trim() : summary.storage_type_label },
                            { label: 'SO',             value: summary.operating_system },
                            { label: 'Office',         value: summary.office_version },
                            { label: 'Inventario',     value: inventoryCapturedAt },
                            { label: 'Tipo inventario',value: inventoryScopeLabel },
                            { label: 'Último extendido', value: inventoryLastExtendedAt },
                            { label: 'Uptime',         value: fmtUptime(a.uptime) },
                        ])}
                        ${lastMetric ? `
                        <hr class="my-2">
                        <div class="small mb-1"><span class="text-muted">Red ↓:</span> ${lastMetric.net_rx_kbps !== null ? Number(lastMetric.net_rx_kbps).toFixed(1) + ' kbps' : 'N/A'}</div>
                        <div class="small mb-1"><span class="text-muted">Red ↑:</span> ${lastMetric.net_tx_kbps !== null ? Number(lastMetric.net_tx_kbps).toFixed(1) + ' kbps' : 'N/A'}</div>
                        <div class="small mb-1"><span class="text-muted">Procesos:</span> ${lastMetric.process_count ?? 'N/A'}</div>
                        ` : ''}
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Sistema</div>
                        ${renderFactList([
                            { label: 'Fabricante',      value: hardware.system?.manufacturer },
                            { label: 'Modelo',          value: hardware.system?.model },
                            { label: 'Dominio',         value: hardware.system?.domain },
                            { label: 'Memoria física',  value: hardware.system?.total_physical_memory_gb ? `${hardware.system.total_physical_memory_gb} GB` : null },
                            { label: 'PCSystemType',    value: hardware.system?.pc_system_type },
                        ])}
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Firmware</div>
                        ${renderFactList([
                            { label: 'BIOS fabricante', value: hardware.bios?.manufacturer },
                            { label: 'BIOS versión',    value: hardware.bios?.version },
                            { label: 'BIOS serial',     value: hardware.bios?.serial_number },
                            { label: 'BIOS fecha',      value: hardware.bios?.release_date ? fmtIsoDate(hardware.bios.release_date) : null },
                            { label: 'Board fabricante',value: hardware.motherboard?.manufacturer },
                            { label: 'Board producto',  value: hardware.motherboard?.product },
                            { label: 'Board serial',    value: hardware.motherboard?.serial_number },
                        ])}
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Software base</div>
                        ${renderFactList([
                            { label: 'SO',              value: software.operating_system?.caption || summary.operating_system },
                            { label: 'Versión',         value: software.operating_system?.version },
                            { label: 'Build',           value: software.operating_system?.build_number },
                            { label: 'Instalado',       value: software.operating_system?.install_date ? fmtIsoDate(software.operating_system.install_date) : null },
                            { label: 'Último arranque', value: software.operating_system?.last_boot_up_time ? fmtIsoDate(software.operating_system.last_boot_up_time) : null },
                            { label: 'Office',          value: software.office_version || summary.office_version },
                            { label: 'Hotfixes',        value: software.hotfix_count },
                        ])}
                        <div class="small text-muted mt-3 mb-1 fw-semibold">Antivirus</div>
                        ${antivirus.length === 0
                            ? '<div class="small text-muted">Sin datos reportados</div>'
                            : antivirus.map((product) => `<div class="small mb-2"><div class="fw-semibold">${toHtml(product.display_name)}</div><div class="text-muted">Estado: ${toHtml(product.product_state)}${product.timestamp ? ` · ${toHtml(product.timestamp)}` : ''}</div></div>`).join('')}
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Procesadores (${processors.length})</div>
                        ${processors.length === 0
                            ? '<div class="small text-muted">Sin datos</div>'
                            : processors.map((p) => `<div class="small mb-2"><div class="fw-semibold">${toHtml(p.name)}</div><div class="text-muted">${toHtml(p.manufacturer)} · ${toHtml(p.cores)} cores · ${toHtml(p.logical_processors)} hilos · ${toHtml(p.max_clock_mhz)} MHz</div></div>`).join('')}
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Memoria instalada (${memoryModules.length})</div>
                        ${memoryModules.length === 0
                            ? '<div class="small text-muted">Sin datos</div>'
                            : memoryModules.map((m) => `<div class="small mb-2"><div class="fw-semibold">${toHtml(m.bank_label)}</div><div class="text-muted">${toHtml(m.capacity_gb ? `${m.capacity_gb} GB` : null)} · ${toHtml(m.speed_mhz ? `${m.speed_mhz} MHz` : null)} · ${toHtml(m.manufacturer)}${m.part_number ? ` · ${toHtml(m.part_number)}` : ''}</div></div>`).join('')}
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Discos físicos (${physicalDisks.length})</div>
                        ${physicalDisks.length === 0
                            ? '<div class="small text-muted">Sin datos</div>'
                            : physicalDisks.map((d) => `<div class="small mb-2"><div class="fw-semibold">${toHtml(d.model)}</div><div class="text-muted">${toHtml(d.media_type)} · ${toHtml(d.interface_type)} · ${toHtml(d.size_gb ? `${d.size_gb} GB` : null)}${d.serial_number ? ` · SN ${toHtml(d.serial_number)}` : ''}</div></div>`).join('')}
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Unidades lógicas (${logicalDisks.length})</div>
                        ${logicalDisks.length === 0
                            ? '<div class="small text-muted">Sin datos</div>'
                            : logicalDisks.map((d) => `<div class="small mb-2"><div class="fw-semibold">${toHtml(d.device_id)}${d.volume_name ? ` · ${toHtml(d.volume_name)}` : ''}</div><div class="text-muted">${toHtml(d.file_system)} · ${toHtml(d.size_gb ? `${d.size_gb} GB` : null)} · Libre ${toHtml(d.free_gb ? `${d.free_gb} GB` : null)}</div></div>`).join('')}
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Video (${videoControllers.length})</div>
                        ${videoControllers.length === 0
                            ? '<div class="small text-muted">Sin datos</div>'
                            : videoControllers.map((v) => `<div class="small mb-2"><div class="fw-semibold">${toHtml(v.name)}</div><div class="text-muted">${toHtml(v.adapter_ram_gb ? `${v.adapter_ram_gb} GB VRAM` : null)}${v.driver_version ? ` · Driver ${toHtml(v.driver_version)}` : ''}</div></div>`).join('')}
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Adaptadores de red (${networkAdapters.length})</div>
                        ${networkAdapters.length === 0
                            ? '<div class="small text-muted">Sin datos</div>'
                            : networkAdapters.map((a) => `<div class="small mb-2"><div class="fw-semibold">${toHtml(a.description)}</div><div class="text-muted mb-1">MAC ${toHtml(a.mac_address)} · DHCP ${toHtml(a.dhcp_enabled ? 'Sí' : 'No')}</div><div class="mb-1">${renderBadgeList(a.ip_addresses, 'Sin IP')}</div><div class="text-muted">GW: ${toHtml(a.default_gateway)}${a.dns_servers ? ` · DNS: ${toHtml(a.dns_servers)}` : ''}</div></div>`).join('')}
                    </div>
                </div>
            </div>
            <div class="small text-muted fw-semibold mb-1">Historial reciente (últimas ${metrics.length} lecturas)</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mini-table mb-0">
                    <thead><tr><th>Hace</th><th>CPU</th><th>RAM</th><th>Disco</th><th>Red ↓/↑</th><th>Procesos</th></tr></thead>
                    <tbody>
                        ${metrics.length === 0
                            ? '<tr><td colspan="6" class="text-center text-muted">Sin historial aún</td></tr>'
                            : metrics.map((m) => `<tr>
                                <td>${m.captured_human}</td>
                                <td>${fmtPct(m.cpu)}</td>
                                <td>${fmtPct(m.memory)}</td>
                                <td>${fmtPct(m.disk)}</td>
                                <td>${m.net_rx_kbps !== null ? Number(m.net_rx_kbps).toFixed(0) : 'N/A'} / ${m.net_tx_kbps !== null ? Number(m.net_tx_kbps).toFixed(0) : 'N/A'} kbps</td>
                                <td>${m.process_count ?? 'N/A'}</td>
                            </tr>`).join('')
                        }
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4 mb-1">
                <div class="small text-muted fw-semibold">Software instalado (${installedPrograms.length})</div>
                <div class="small text-muted">Inventario extendido enviado por el agente</div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mini-table mb-0">
                    <thead><tr><th>Programa</th><th>Versión</th><th>Proveedor</th><th>Fecha</th></tr></thead>
                    <tbody>${renderProgramRows(installedPrograms)}</tbody>
                </table>
            </div>`;
    };

    const loadAssetDetail = async (id) => {
        if (!adModalBody) return;
        try {
            const response = await fetch(`{{ url('/admin/monitoring/asset') }}/${id}`, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            if (!response.ok || !data.ok) {
                adModalBody.innerHTML = '<div class="text-center py-4 text-danger">Error cargando datos.</div>';
                return;
            }
            renderDetailBody(data);
        } catch {
            adModalBody.innerHTML = '<div class="text-center py-4 text-danger">Sin conexión con el servidor.</div>';
        }
    };

    if (adModal) {
        adModal.addEventListener('show.bs.modal', () => {
            if (!adActiveAssetId) return;
            adModalBody.innerHTML = '<div class="text-center py-4 text-muted">Cargando...</div>';
            loadAssetDetail(adActiveAssetId);
            adRefreshTimer = window.setInterval(() => loadAssetDetail(adActiveAssetId), 10000);
        });
        adModal.addEventListener('hidden.bs.modal', () => {
            clearInterval(adRefreshTimer);
            adRefreshTimer = null;
            adActiveAssetId = null;
            if (adModalReassignBtn) {
                adModalReassignBtn.innerHTML = '<i class="bi bi-arrow-left-right"></i> Reasignar equipo';
            }
        });
        if (adModalRefreshBtn) adModalRefreshBtn.addEventListener('click', () => loadAssetDetail(adActiveAssetId));
    }

    const openAssetDetailModal = (assetId) => {
        if (!assetId || !adModal) return;
        adActiveAssetId = assetId;
        const bsModal = window.bootstrap?.Modal?.getOrCreateInstance(adModal);
        if (bsModal) bsModal.show();
    };

    window.itcityOpenAssetDetail = openAssetDetailModal;

    if (rowsEl) {
        rowsEl.addEventListener('click', (event) => {
            const row = event.target.closest('tr[data-asset-id]');
            if (!row || !rowsEl.contains(row)) return;
            const interactive = event.target.closest('a, button, input, select, textarea, label');
            if (interactive) return;
            openAssetDetailModal(row.dataset.assetId);
        });
    }

    const inventoryFilterSearch = document.getElementById('inventoryAssetFilterSearch');
    const inventoryFilterInvoiceFolio = document.getElementById('inventoryAssetFilterInvoiceFolio');
    const inventoryFilterType = document.getElementById('inventoryAssetFilterType');
    const inventoryFilterStatus = document.getElementById('inventoryAssetFilterStatus');
    const inventoryFilterBranch = document.getElementById('inventoryAssetFilterBranch');
    const inventoryFilterSoftware = document.getElementById('inventoryAssetFilterSoftware');
    const inventoryFilterSoftwareMode = document.getElementById('inventoryAssetFilterSoftwareMode');
    const inventoryFilterBrand = document.getElementById('inventoryAssetFilterBrand');
    const inventoryFilterModel = document.getElementById('inventoryAssetFilterModel');
    const inventoryFilterRamMin = document.getElementById('inventoryAssetFilterRamMin');
    const inventoryFilterStorageMin = document.getElementById('inventoryAssetFilterStorageMin');
    const inventoryFilterSeenFrom = document.getElementById('inventoryAssetFilterSeenFrom');
    const inventoryFilterSeenTo = document.getElementById('inventoryAssetFilterSeenTo');
    const inventoryFilterReset = document.getElementById('inventoryAssetFilterReset');
    const inventoryVisibleCount = document.getElementById('inventoryAssetFilterVisibleCount');
    const inventoryTotalCount = document.getElementById('inventoryAssetFilterTotalCount');
    const inventoryActiveFilters = document.getElementById('inventoryAssetActiveFilters');
    const inventoryNoResultsRow = document.getElementById('inventoryAssetsNoResults');
    const inventoryRows = Array.from(document.querySelectorAll('#inventoryAssetsTableBody tr[data-filter-row="asset"]'));
    const modalAssetForm = document.getElementById('modalAssetForm');
    const modalAssetMethod = document.getElementById('modalAssetMethod');
    const modalAssetTitle = document.getElementById('modalAssetTitle');
    const modalAssetSubmitButton = document.getElementById('modalAssetSubmitButton');
    const btnOpenNewAssetModal = document.getElementById('btnOpenNewAssetModal');
    const modalAssetHistoryWrap = document.getElementById('modalAssetHistoryWrap');
    const modalAssetHistory = document.getElementById('modalAssetHistory');
    const modalAssetReassignForm = document.getElementById('modalAssetReassignForm');
    const modalAssetReassignSummary = document.getElementById('modalAssetReassignSummary');
    const modalReassignCurrentUser = document.getElementById('modalReassignCurrentUser');
    const modalAssetTransferRequestForm = document.getElementById('modalAssetTransferRequestForm');
    const modalAssetTransferRequestSummary = document.getElementById('modalAssetTransferRequestSummary');
    const modalAssetInvoiceAnalyzer = document.getElementById('modalAssetInvoiceAnalyzer');
    const assetInvoiceAnalyzerForm = document.getElementById('assetInvoiceAnalyzerForm');
    const assetInvoiceAnalyzerSubmitButton = document.getElementById('assetInvoiceAnalyzerSubmitButton');
    const analyzerSubmitLabel = assetInvoiceAnalyzerSubmitButton?.querySelector('.js-analyzer-submit-label');
    const modalAssetInvoiceDraft = document.getElementById('modalAssetInvoiceDraft');
    const btnOpenInvoiceDraftModal = document.getElementById('btnOpenInvoiceDraftModal');
    const invoiceDraftImportForm = document.getElementById('invoiceDraftImportForm');
    const assetInvoicePayloadInput = document.getElementById('assetInvoicePayloadInput');
    const invoiceDraftRows = Array.from(document.querySelectorAll('#invoiceDraftItemsTableBody tr[data-draft-index]'));
    const hasInvoiceDraft = @json($hasInvoiceDraft);
    const autoOpenInvoiceDraft = @json($autoOpenInvoiceDraft);
    const modalEditEquipmentTypeCatalog = document.getElementById('modalEditEquipmentTypeCatalog');
    const modalEditEquipmentTypeCatalogForm = document.getElementById('modalEditEquipmentTypeCatalogForm');
    const editEquipmentTypeCatalogKey = document.getElementById('editEquipmentTypeCatalogKey');
    const editEquipmentTypeCatalogLabel = document.getElementById('editEquipmentTypeCatalogLabel');
    const editEquipmentTypeCatalogSortOrder = document.getElementById('editEquipmentTypeCatalogSortOrder');
    const editEquipmentTypeCatalogIsActive = document.getElementById('editEquipmentTypeCatalogIsActive');
    const editEquipmentTypeCatalogDescription = document.getElementById('editEquipmentTypeCatalogDescription');
    const modalEditAssetStatusCatalog = document.getElementById('modalEditAssetStatusCatalog');
    const modalEditAssetStatusCatalogForm = document.getElementById('modalEditAssetStatusCatalogForm');
    const editAssetStatusCatalogKey = document.getElementById('editAssetStatusCatalogKey');
    const editAssetStatusCatalogLabel = document.getElementById('editAssetStatusCatalogLabel');
    const editAssetStatusCatalogSortOrder = document.getElementById('editAssetStatusCatalogSortOrder');
    const editAssetStatusCatalogIsActive = document.getElementById('editAssetStatusCatalogIsActive');
    const editAssetStatusCatalogDescription = document.getElementById('editAssetStatusCatalogDescription');
    const transferHistorySearchInput = document.getElementById('transferHistorySearchInput');
    const transferHistoryStatusFilter = document.getElementById('transferHistoryStatusFilter');
    const transferHistoryFilterReset = document.getElementById('transferHistoryFilterReset');
    const transferHistoryNoResults = document.getElementById('transferHistoryNoResults');
    const transferHistoryRows = Array.from(document.querySelectorAll('#transferHistoryTableBody tr[data-transfer-row="1"]'));
    const contextBranchFilterValue = String(@json(Str::lower($currentContextBranchName ?? '')) || '').trim();

    const applyBranchContextToSelect = (selectId) => {
        if (!contextBranchFilterValue) return;
        const selectEl = document.getElementById(selectId);
        if (!selectEl || String(selectEl.value || '').trim() !== '') return;

        const targetOption = Array.from(selectEl.options || []).find((option) => {
            const value = String(option.value || '').trim().toLowerCase();
            const label = String(option.textContent || '').trim().toLowerCase();
            return value === contextBranchFilterValue || label === contextBranchFilterValue;
        });

        if (targetOption) {
            selectEl.value = targetOption.value;
        }
    };

    ['spacesFilterBranch', 'nodesFilterBranch', 'monitoringFilterBranch', 'floorPlansFilterBranch', 'inventoryAssetFilterBranch'].forEach(applyBranchContextToSelect);

    const normalizeFilterValue = (value) => String(value ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

    const bindSectionTextFilter = ({ inputId, tableId, emptyMessage, selectFilters = [], resetButtonId = null }) => {
        const input = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        if (!table) return;

        const normalizedSelectFilters = selectFilters
            .map((filter) => {
                const selectEl = document.getElementById(filter.id);
                if (!selectEl) return null;
                return {
                    selectEl,
                    dataAttribute: filter.dataAttribute || '',
                    mode: filter.mode === 'contains' ? 'contains' : 'exact',
                };
            })
            .filter(Boolean);

        const resetButton = resetButtonId ? document.getElementById(resetButtonId) : null;

        const getActiveFiltersCount = () => {
            let activeCount = 0;
            if (normalizeFilterValue(input?.value || '')) {
                activeCount += 1;
            }

            normalizedSelectFilters.forEach(({ selectEl }) => {
                if (normalizeFilterValue(selectEl.value)) {
                    activeCount += 1;
                }
            });

            return activeCount;
        };

        const updateResetButtonState = () => {
            if (!resetButton) return;
            const activeCount = getActiveFiltersCount();

            resetButton.disabled = activeCount === 0;
            resetButton.textContent = activeCount > 0 ? `Limpiar (${activeCount})` : 'Limpiar';
            resetButton.classList.toggle('btn-secondary', activeCount > 0);
            resetButton.classList.toggle('btn-outline-secondary', activeCount === 0);
        };

        if (!input && !normalizedSelectFilters.length) return;

        const tbody = table.querySelector('tbody');
        const headersCount = table.querySelectorAll('thead th').length || 1;
        if (!tbody) return;

        const helperRow = document.createElement('tr');
        helperRow.dataset.filterHelper = 'true';
        helperRow.classList.add('d-none');
        helperRow.innerHTML = `<td colspan="${headersCount}" class="text-center text-muted py-3">${escapeHtml(emptyMessage || 'Sin resultados con ese filtro')}</td>`;
        tbody.appendChild(helperRow);

        const getDataRows = () => Array.from(tbody.querySelectorAll('tr')).filter((row) => row.dataset.filterHelper !== 'true');

        const apply = () => {
            const query = normalizeFilterValue(input?.value || '');
            let visibleRows = 0;

            getDataRows().forEach((row) => {
                const rowText = normalizeFilterValue(row.textContent || '');
                const matchesText = !query || rowText.includes(query);
                const matchesSelects = normalizedSelectFilters.every(({ selectEl, dataAttribute, mode }) => {
                    const selectedValue = normalizeFilterValue(selectEl.value);
                    if (!selectedValue) return true;

                    const rowValue = normalizeFilterValue(row.dataset[dataAttribute] || '');
                    if (!rowValue) return false;

                    return mode === 'contains'
                        ? rowValue.includes(selectedValue)
                        : rowValue === selectedValue;
                });

                const matches = matchesText && matchesSelects;
                row.classList.toggle('d-none', !matches);
                if (matches) visibleRows += 1;
            });

            const hasActiveSelect = normalizedSelectFilters.some(({ selectEl }) => normalizeFilterValue(selectEl.value));
            helperRow.classList.toggle('d-none', (!query && !hasActiveSelect) || visibleRows > 0);
            updateResetButtonState();
        };

        if (input) {
            input.addEventListener('input', apply);
            input.addEventListener('change', apply);
        }

        normalizedSelectFilters.forEach(({ selectEl }) => {
            selectEl.addEventListener('change', apply);
        });

        if (resetButton) {
            resetButton.addEventListener('click', () => {
                if (input) input.value = '';
                normalizedSelectFilters.forEach(({ selectEl }) => {
                    selectEl.value = '';
                });
                apply();
            });
        }

        apply();
    };

    const toNumberOrNull = (value) => {
        if (value === null || value === undefined || value === '') return null;
        const number = Number(value);
        return Number.isFinite(number) ? number : null;
    };

    const toTimeOrNull = (value) => {
        if (!value) return null;
        const time = new Date(value).getTime();
        return Number.isFinite(time) ? time : null;
    };

    const renderAssetAdminHistory = (historyEntries) => {
        if (!modalAssetHistory || !modalAssetHistoryWrap) return;

        const entries = Array.isArray(historyEntries) ? historyEntries.slice().reverse() : [];
        if (!entries.length) {
            modalAssetHistoryWrap.style.display = '';
            modalAssetHistory.innerHTML = '<div class="text-muted">Sin historial registrado</div>';
            return;
        }

        modalAssetHistoryWrap.style.display = '';
        modalAssetHistory.innerHTML = entries.map((entry) => {
            const when = escapeHtml(entry?.at || 'N/A');
            const by = escapeHtml(entry?.by || 'Sistema');
            const note = escapeHtml(entry?.note || '');
            const changes = Array.isArray(entry?.changes) ? entry.changes.map((item) => `<li>${escapeHtml(item)}</li>`).join('') : '';
            return `
                <div class="pb-2 mb-2 border-bottom">
                    <div><strong>${when}</strong> · ${by}</div>
                    ${note ? `<div>${note}</div>` : ''}
                    ${changes ? `<ul class="mb-0 ps-3">${changes}</ul>` : ''}
                </div>
            `;
        }).join('');
    };

    const resetAssetModalToCreate = () => {
        if (!modalAssetForm) return;
        modalAssetForm.action = '{{ url('/admin/computer-assets') }}';
        if (modalAssetMethod) modalAssetMethod.value = 'POST';
        if (modalAssetTitle) modalAssetTitle.textContent = 'Nuevo activo TI';
        if (modalAssetSubmitButton) modalAssetSubmitButton.textContent = 'Guardar';
        modalAssetForm.reset();
        if (modalAssetHistoryWrap) modalAssetHistoryWrap.style.display = 'none';
        if (modalAssetHistory) modalAssetHistory.innerHTML = '';
    };

    const openAssetModalForEdit = (row) => {
        if (!row || !modalAssetForm) return;
        const assetId = row.dataset.assetId;
        if (!assetId) return;

        modalAssetForm.action = `{{ url('/admin/computer-assets') }}/${assetId}`;
        if (modalAssetMethod) modalAssetMethod.value = 'PUT';
        if (modalAssetTitle) modalAssetTitle.textContent = 'Editar activo TI';
        if (modalAssetSubmitButton) modalAssetSubmitButton.textContent = 'Actualizar';

        const setFieldValue = (selector, value) => {
            const field = modalAssetForm.querySelector(selector);
            if (field) field.value = value ?? '';
        };

        setFieldValue('[name="asset_branch_id"]', row.dataset.assetBranchId || '');
        setFieldValue('[name="asset_node_id"]', row.dataset.assetNodeId || '');
        setFieldValue('[name="asset_equipment_type"]', row.dataset.assetEquipmentType || '');
        setFieldValue('[name="asset_status"]', row.dataset.status || '');
        setFieldValue('[name="asset_hostname"]', row.dataset.assetHostname || '');
        setFieldValue('[name="asset_tag"]', row.dataset.assetTag || '');
        setFieldValue('[name="asset_assigned_user"]', row.dataset.assetAssignedUser || '');
        setFieldValue('[name="asset_notes"]', row.dataset.assetNotes || '');
        setFieldValue('[name="asset_responsiva_reference"]', row.dataset.assetResponsivaReference || '');
        setFieldValue('[name="asset_purchase_order_number"]', row.dataset.assetPurchaseOrderNumber || '');
        setFieldValue('[name="asset_assignment_invoice_folio"]', row.dataset.assetAssignmentInvoiceFolio || '');
        setFieldValue('[name="asset_assignment_supplier"]', row.dataset.assetAssignmentSupplier || '');
        setFieldValue('[name="asset_assignment_delivery_date"]', row.dataset.assetAssignmentDeliveryDate || '');
        setFieldValue('[name="asset_assignment_received_by"]', row.dataset.assetAssignmentReceivedBy || '');
        setFieldValue('[name="asset_assignment_change_reason"]', row.dataset.assetAssignmentChangeReason || '');
        setFieldValue('[name="asset_interaction_note"]', '');

        let historyEntries = [];
        try {
            historyEntries = JSON.parse(row.dataset.assetAdminHistory || '[]');
        } catch (error) {
            historyEntries = [];
        }
        renderAssetAdminHistory(historyEntries);
    };

    const openAssetModalForReassign = (row) => {
        if (!row || !modalAssetReassignForm) return;
        const assetId = row.dataset.assetId;
        if (!assetId) return;

        modalAssetReassignForm.action = `{{ url('/admin/computer-assets') }}/${assetId}/reassign`;
        modalAssetReassignForm.reset();

        const assetNameParts = [row.dataset.assetTag, row.dataset.assetHostname].filter(Boolean);
        const assetName = assetNameParts.length ? assetNameParts.join(' · ') : `Activo #${assetId}`;
        const currentUser = row.dataset.assetAssignedUser || 'Sin asignar';

        if (modalAssetReassignSummary) {
            modalAssetReassignSummary.innerHTML = `<strong>${escapeHtml(assetName)}</strong><br><span class="text-muted">Responsable actual: ${escapeHtml(currentUser)}</span>`;
        }

        if (modalReassignCurrentUser) {
            modalReassignCurrentUser.value = currentUser;
        }
    };

    const openAssetModalForTransferRequest = (row) => {
        if (!row || !modalAssetTransferRequestForm) return;
        const assetId = row.dataset.assetId;
        if (!assetId) return;

        modalAssetTransferRequestForm.action = `{{ url('/admin/computer-assets') }}/${assetId}/transfer-requests`;
        modalAssetTransferRequestForm.reset();

        const assetNameParts = [row.dataset.assetTag, row.dataset.assetHostname].filter(Boolean);
        const assetName = assetNameParts.length ? assetNameParts.join(' · ') : `Activo #${assetId}`;
        const currentUser = row.dataset.assetAssignedUser || 'Sin asignar';
        const currentBranch = row.dataset.assetBranchName || 'N/A';

        if (modalAssetTransferRequestSummary) {
            modalAssetTransferRequestSummary.innerHTML = `<strong>${escapeHtml(assetName)}</strong><br><span class="text-muted">Responsable actual: ${escapeHtml(currentUser)} · Sede actual: ${escapeHtml(currentBranch)}</span>`;
        }
    };

    const openReassignFromAssetDetail = () => {
        if (!adActiveAssetId || !modalAssetReassignForm) return;

        const assetId = String(adActiveAssetId);
        const row = document.querySelector(`#inventoryAssetsTableBody tr[data-filter-row="asset"][data-asset-id="${assetId}"]`);
        if (row) {
            openAssetModalForReassign(row);
        } else {
            modalAssetReassignForm.action = `{{ url('/admin/computer-assets') }}/${assetId}/reassign`;
            modalAssetReassignForm.reset();
            if (modalAssetReassignSummary) {
                modalAssetReassignSummary.innerHTML = `<strong>${escapeHtml(adModalLabel?.textContent || `Activo #${assetId}`)}</strong><br><span class="text-muted">Responsable actual: Sin asignar</span>`;
            }
            if (modalReassignCurrentUser) {
                modalReassignCurrentUser.value = 'Sin asignar';
            }
        }

        const detailModalInstance = adModal ? window.bootstrap?.Modal?.getOrCreateInstance(adModal) : null;
        const reassignModalEl = document.getElementById('modalAssetReassign');
        const reassignModalInstance = reassignModalEl ? window.bootstrap?.Modal?.getOrCreateInstance(reassignModalEl) : null;

        if (detailModalInstance && reassignModalInstance && adModal) {
            const showReassign = () => {
                adModal.removeEventListener('hidden.bs.modal', showReassign);
                reassignModalInstance.show();
            };
            adModal.addEventListener('hidden.bs.modal', showReassign);
            detailModalInstance.hide();
            return;
        }

        reassignModalInstance?.show();
    };

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const getSelectedOptionText = (selectEl) => {
        if (!selectEl) return '';
        const selectedOption = selectEl.options?.[selectEl.selectedIndex];
        return selectedOption ? String(selectedOption.textContent || '').trim() : '';
    };

    const getInventoryActiveFilters = () => {
        const filters = [];
        const queryText = String(inventoryFilterSearch?.value || '').trim();
        const softwareText = String(inventoryFilterSoftware?.value || '').trim();
        const softwareMode = String(inventoryFilterSoftwareMode?.value || 'contains');
        const ramMinText = String(inventoryFilterRamMin?.value || '').trim();
        const storageMinText = String(inventoryFilterStorageMin?.value || '').trim();
        const seenFromText = String(inventoryFilterSeenFrom?.value || '').trim();
        const seenToText = String(inventoryFilterSeenTo?.value || '').trim();

        if (queryText) filters.push({ key: 'search', label: `Buscar: ${queryText}` });
        if (inventoryFilterType?.value) filters.push({ key: 'type', label: `Tipo: ${getSelectedOptionText(inventoryFilterType)}` });
        if (inventoryFilterStatus?.value) filters.push({ key: 'status', label: `Estado: ${getSelectedOptionText(inventoryFilterStatus)}` });
        if (inventoryFilterBranch?.value) filters.push({ key: 'branch', label: `Sede: ${getSelectedOptionText(inventoryFilterBranch)}` });
        if (softwareText) {
            filters.push({
                key: 'software',
                label: `Software (${softwareMode === 'exact' ? 'exacta' : (softwareMode === 'starts_with' ? 'empieza con' : 'contiene')}): ${softwareText}`,
            });
        }
        if (inventoryFilterBrand?.value) filters.push({ key: 'brand', label: `Marca: ${getSelectedOptionText(inventoryFilterBrand)}` });
        if (inventoryFilterModel?.value) filters.push({ key: 'model', label: `Modelo: ${getSelectedOptionText(inventoryFilterModel)}` });
        if (ramMinText) filters.push({ key: 'ramMin', label: `RAM ≥ ${ramMinText} GB` });
        if (storageMinText) filters.push({ key: 'storageMin', label: `Almacenamiento ≥ ${storageMinText} GB` });
        if (seenFromText) filters.push({ key: 'seenFrom', label: `Último reporte desde: ${seenFromText}` });
        if (seenToText) filters.push({ key: 'seenTo', label: `Último reporte hasta: ${seenToText}` });

        return filters;
    };

    const activateTabFromHash = () => {
        const hash = String(window.location.hash || '').trim();
        if (!hash) return;

        if (hash === '#crud-asset-catalogs') {
            const assetsNavLink = document.querySelector('.nav-link[data-bs-toggle="tab"][href="#section-assets"]');
            if (assetsNavLink && window.bootstrap?.Tab) {
                const tab = window.bootstrap.Tab.getOrCreateInstance(assetsNavLink);
                tab.show();
            }

            const catalogsPanel = document.getElementById('inventoryCatalogsPanel');
            if (catalogsPanel && window.bootstrap?.Collapse) {
                const collapse = window.bootstrap.Collapse.getOrCreateInstance(catalogsPanel, { toggle: false });
                collapse.show();
            }

            return;
        }

        const navLink = document.querySelector(`.nav-link[data-bs-toggle="tab"][href="${hash}"]`);
        if (!navLink || !window.bootstrap?.Tab) return;

        const tab = window.bootstrap.Tab.getOrCreateInstance(navLink);
        tab.show();
    };

    const focusAssetFromUrl = () => {
        const params = new URLSearchParams(window.location.search || '');
        const focusAssetId = String(params.get('focus_asset') || '').trim();
        if (!focusAssetId) return;

        const targetRow = document.querySelector(`#inventoryAssetsTableBody tr[data-filter-row="asset"][data-asset-id="${focusAssetId}"]`);
        if (!targetRow) return;

        targetRow.classList.remove('d-none');
        targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        targetRow.classList.add('table-warning');
        window.setTimeout(() => targetRow.classList.remove('table-warning'), 2600);
    };

    const isLikelyDraftSerial = (value) => {
        const serial = String(value || '').trim().toUpperCase();
        if (serial.length < 6 || serial.length > 32) return false;
        if (!/[A-Z]/.test(serial) || !/\d/.test(serial)) return false;
        if (/^[0-9\-/]+$/.test(serial)) return false;
        if (/^(I3|I5|I7|I9|DDR4|DDR5|SSD|NVME|HDD|GHZ|MHZ|TB|GB|RAM)$/i.test(serial)) return false;
        if (/\d+(GB|TB|MHZ|GHZ)$/i.test(serial)) return false;
        return true;
    };

    const refreshDraftSerialBadge = (row) => {
        if (!row) return;
        const serialInput = row.querySelector('.js-draft-serial');
        const badge = row.querySelector('.js-draft-serial-status');
        if (!serialInput || !badge) return;

        const valid = isLikelyDraftSerial(serialInput.value);
        badge.textContent = valid ? 'Serie validada' : 'Serie dudosa';
        badge.dataset.status = valid ? 'validada' : 'dudosa';
        badge.classList.remove('text-bg-success', 'text-bg-warning', 'text-dark');
        if (valid) {
            badge.classList.add('text-bg-success');
            row.classList.remove('table-warning');
        } else {
            badge.classList.add('text-bg-warning', 'text-dark');
            row.classList.add('table-warning');
        }
    };

    const scoreToFieldConfidence = (score) => {
        const safeScore = Math.max(0, Math.min(1, Number(score) || 0));
        if (safeScore >= 0.8) {
            return { score: Number(safeScore.toFixed(2)), status: 'alta', label: 'Alta', badgeClass: 'text-bg-success' };
        }

        if (safeScore >= 0.6) {
            return { score: Number(safeScore.toFixed(2)), status: 'media', label: 'Media', badgeClass: 'text-bg-warning text-dark' };
        }

        return { score: Number(safeScore.toFixed(2)), status: 'baja', label: 'Baja', badgeClass: 'text-bg-danger' };
    };

    const assessDraftDescriptionConfidence = (description) => {
        const value = String(description || '').trim();
        if (!value || /^equipo\s+(por\s+validar\s+de\s+)?factura$/i.test(value)) return scoreToFieldConfidence(0.35);
        if (value.length < 8) return scoreToFieldConfidence(0.55);
        if (/\b(laptop|desktop|monitor|servidor|server|latitude|thinkpad|optiplex|probook|elitebook|macbook)\b/i.test(value)) return scoreToFieldConfidence(0.9);
        return scoreToFieldConfidence(0.75);
    };

    const assessDraftEquipmentTypeConfidence = (description, equipmentType) => {
        const text = String(description || '').toLowerCase().trim();
        const type = String(equipmentType || 'desktop').toLowerCase().trim();
        if (!text || text.includes('equipo por validar de factura')) return scoreToFieldConfidence(0.45);

        const map = {
            laptop: /\b(laptop|notebook|latitude|thinkpad|probook|elitebook|zbook|ideapad|macbook|vivobook)\b/i,
            desktop: /\b(desktop|optiplex|prodesk|elitedesk|torre|cpu)\b/i,
            aio: /\b(all\s*in\s*one|aio|imac)\b/i,
            monitor: /\b(monitor|display|pantalla)\b/i,
            server: /\b(server|servidor|poweredge|proliant|thinksystem)\b/i,
        };

        if (map[type] && map[type].test(description)) return scoreToFieldConfidence(0.88);
        return scoreToFieldConfidence(type === 'desktop' ? 0.58 : 0.65);
    };

    const assessDraftBrandConfidence = (brand) => {
        const value = String(brand || '').trim();
        return scoreToFieldConfidence(value ? 0.92 : 0.35);
    };

    const assessDraftModelConfidence = (model) => {
        const value = String(model || '').trim();
        if (!value) return scoreToFieldConfidence(0.35);
        if (value.length < 4) return scoreToFieldConfidence(0.55);
        if (/\b(latitude|thinkpad|optiplex|probook|elitebook|prodesk|zbook|ideapad|macbook|vivobook|\d{3,4}\s*[a-z]\d?)\b/i.test(value)) return scoreToFieldConfidence(0.9);
        return scoreToFieldConfidence(0.74);
    };

    const computeDraftFieldConfidence = ({ description, equipmentType, brand, model }) => {
        const descriptionScore = assessDraftDescriptionConfidence(description);
        const equipmentTypeScore = assessDraftEquipmentTypeConfidence(description, equipmentType);
        const brandScore = assessDraftBrandConfidence(brand);
        const modelScore = assessDraftModelConfidence(model);

        return {
            description: { score: descriptionScore.score, status: descriptionScore.status, label: descriptionScore.label },
            equipment_type: { score: equipmentTypeScore.score, status: equipmentTypeScore.status, label: equipmentTypeScore.label },
            brand: { score: brandScore.score, status: brandScore.status, label: brandScore.label },
            model: { score: modelScore.score, status: modelScore.status, label: modelScore.label },
        };
    };

    const refreshDraftFieldConfidenceBadges = (row) => {
        if (!row) return;

        const description = String(row.querySelector('.js-draft-description')?.value || '').trim();
        const equipmentType = String(row.querySelector('.js-draft-equipment-type')?.value || 'desktop').trim();
        const brand = String(row.querySelector('.js-draft-brand')?.value || '').trim();
        const model = String(row.querySelector('.js-draft-model')?.value || '').trim();

        const result = computeDraftFieldConfidence({ description, equipmentType, brand, model });
        row.querySelectorAll('.js-draft-field-confidence[data-field]').forEach((badge) => {
            const field = String(badge.dataset.field || '').trim();
            const confidence = result[field];
            if (!confidence) return;

            badge.classList.remove('text-bg-success', 'text-bg-warning', 'text-bg-danger', 'text-dark');
            if (confidence.status === 'alta') {
                badge.classList.add('text-bg-success');
            } else if (confidence.status === 'media') {
                badge.classList.add('text-bg-warning', 'text-dark');
            } else {
                badge.classList.add('text-bg-danger');
            }

            const prefix = field === 'description' ? 'Desc'
                : field === 'equipment_type' ? 'Tipo'
                : field === 'brand' ? 'Marca'
                : 'Modelo';
            badge.textContent = `${prefix} ${confidence.label}`;
        });
    };

    const buildEditedInvoicePayload = () => {
        if (!invoiceDraftImportForm) return null;
        const baseRaw = String(invoiceDraftImportForm.dataset.baseDraft || '').trim();
        if (!baseRaw) return null;

        let baseDraft = null;
        try {
            baseDraft = JSON.parse(baseRaw);
        } catch (error) {
            return null;
        }

        if (!baseDraft || typeof baseDraft !== 'object') return null;

        const baseItems = Array.isArray(baseDraft.items) ? baseDraft.items : [];
        const editedItems = [];

        invoiceDraftRows.forEach((row) => {
            const include = row.querySelector('.js-draft-include');
            if (include && !include.checked) return;

            const index = Number(row.dataset.draftIndex || '-1');
            const baseItem = Number.isFinite(index) && index >= 0 ? (baseItems[index] || {}) : {};
            const serialInput = row.querySelector('.js-draft-serial');
            const descriptionInput = row.querySelector('.js-draft-description');
            const typeSelect = row.querySelector('.js-draft-equipment-type');
            const brandInput = row.querySelector('.js-draft-brand');
            const modelInput = row.querySelector('.js-draft-model');

            const serialNumber = String(serialInput?.value || '').trim().toUpperCase();
            const isValidSerial = isLikelyDraftSerial(serialNumber);
            const description = String(descriptionInput?.value || '').trim();
            const equipmentType = String(typeSelect?.value || baseItem.equipment_type || 'desktop').trim();
            const brand = String(brandInput?.value || '').trim() || null;
            const model = String(modelInput?.value || '').trim() || null;
            const fieldConfidence = computeDraftFieldConfidence({
                description,
                equipmentType,
                brand,
                model,
            });
            const serialConfidenceScore = isValidSerial ? 0.92 : 0.35;
            const overallConfidence = Number((((fieldConfidence.description.score || 0)
                + (fieldConfidence.equipment_type.score || 0)
                + (fieldConfidence.brand.score || 0)
                + (fieldConfidence.model.score || 0)
                + serialConfidenceScore) / 5).toFixed(2));

            editedItems.push({
                ...baseItem,
                description,
                equipment_type: equipmentType,
                brand,
                model,
                serial_number: serialNumber || null,
                serial_status: isValidSerial ? 'validada' : 'dudosa',
                serial_status_label: isValidSerial ? 'Serie validada' : 'Serie dudosa',
                field_confidence: fieldConfidence,
                confidence: overallConfidence,
            });
        });

        return {
            ...baseDraft,
            items: editedItems,
        };
    };

    const renderInventoryFilterChips = () => {
        if (!inventoryActiveFilters) return;

        const filters = getInventoryActiveFilters();
        if (!filters.length) {
            inventoryActiveFilters.innerHTML = '<span class="text-muted small">Sin filtros activos</span>';
            return;
        }

        inventoryActiveFilters.innerHTML = filters.map((filter) => (
            `<button type="button" class="btn btn-sm btn-outline-primary" data-filter-key="${filter.key}" aria-label="Quitar filtro ${escapeHtml(filter.label)}">${escapeHtml(filter.label)} <span aria-hidden="true">&times;</span></button>`
        )).join('');
    };

    const clearInventoryFilterByKey = (filterKey) => {
        switch (filterKey) {
            case 'search':
                if (inventoryFilterSearch) inventoryFilterSearch.value = '';
                break;
            case 'type':
                if (inventoryFilterType) inventoryFilterType.value = '';
                break;
            case 'status':
                if (inventoryFilterStatus) inventoryFilterStatus.value = '';
                break;
            case 'branch':
                if (inventoryFilterBranch) inventoryFilterBranch.value = '';
                break;
            case 'software':
                if (inventoryFilterSoftware) inventoryFilterSoftware.value = '';
                break;
            case 'brand':
                if (inventoryFilterBrand) inventoryFilterBrand.value = '';
                break;
            case 'model':
                if (inventoryFilterModel) inventoryFilterModel.value = '';
                break;
            case 'ramMin':
                if (inventoryFilterRamMin) inventoryFilterRamMin.value = '';
                break;
            case 'storageMin':
                if (inventoryFilterStorageMin) inventoryFilterStorageMin.value = '';
                break;
            case 'seenFrom':
                if (inventoryFilterSeenFrom) inventoryFilterSeenFrom.value = '';
                break;
            case 'seenTo':
                if (inventoryFilterSeenTo) inventoryFilterSeenTo.value = '';
                break;
            default:
                break;
        }
    };

    const applyInventoryFilters = () => {
        if (!inventoryRows.length) {
            if (inventoryVisibleCount) inventoryVisibleCount.textContent = '0';
            if (inventoryTotalCount) inventoryTotalCount.textContent = '0';
            return;
        }

        const query = normalizeFilterValue(inventoryFilterSearch?.value || '');
        const type = normalizeFilterValue(inventoryFilterType?.value || '');
        const status = normalizeFilterValue(inventoryFilterStatus?.value || '');
        const branch = normalizeFilterValue(inventoryFilterBranch?.value || '');
        const invoiceFolio = normalizeFilterValue(inventoryFilterInvoiceFolio?.value || '');
        const software = normalizeFilterValue(inventoryFilterSoftware?.value || '');
        const softwareMode = normalizeFilterValue(inventoryFilterSoftwareMode?.value || 'contains');
        const brand = normalizeFilterValue(inventoryFilterBrand?.value || '');
        const model = normalizeFilterValue(inventoryFilterModel?.value || '');
        const ramMin = toNumberOrNull(inventoryFilterRamMin?.value);
        const storageMin = toNumberOrNull(inventoryFilterStorageMin?.value);
        const seenFrom = toTimeOrNull(inventoryFilterSeenFrom?.value);
        const seenTo = toTimeOrNull(inventoryFilterSeenTo?.value ? `${inventoryFilterSeenTo.value}T23:59:59` : '');

        let visible = 0;
        inventoryRows.forEach((row) => {
            const rowSearch = normalizeFilterValue(row.dataset.search || '');
            const rowType = normalizeFilterValue(row.dataset.type || '');
            const rowStatus = normalizeFilterValue(row.dataset.status || '');
            const rowBranch = normalizeFilterValue(row.dataset.branch || '');
            const rowInvoiceFolio = normalizeFilterValue(row.dataset.assetInvoiceFolio || '');
            const rowSoftware = normalizeFilterValue(row.dataset.software || '');
            const rowSoftwareExactTerms = String(row.dataset.softwareExact || '')
                .split('||')
                .map((item) => normalizeFilterValue(item))
                .filter(Boolean);
            const rowBrand = normalizeFilterValue(row.dataset.brand || '');
            const rowModel = normalizeFilterValue(row.dataset.model || '');
            const rowRam = toNumberOrNull(row.dataset.ram);
            const rowStorage = toNumberOrNull(row.dataset.storage);
            const rowSeen = toTimeOrNull(row.dataset.lastSeen);

            const softwareMatches = !software
                || (softwareMode === 'exact'
                    ? rowSoftwareExactTerms.includes(software)
                    : softwareMode === 'starts_with'
                        ? rowSoftwareExactTerms.some((term) => term.startsWith(software))
                        : rowSoftware.includes(software));

            const matches = (!query || rowSearch.includes(query))
                && (!invoiceFolio || rowInvoiceFolio.includes(invoiceFolio))
                && (!type || rowType === type)
                && (!status || rowStatus === status)
                && (!branch || rowBranch === branch)
                && softwareMatches
                && (!brand || rowBrand === brand)
                && (!model || rowModel === model)
                && (ramMin === null || (rowRam !== null && rowRam >= ramMin))
                && (storageMin === null || (rowStorage !== null && rowStorage >= storageMin))
                && (seenFrom === null || (rowSeen !== null && rowSeen >= seenFrom))
                && (seenTo === null || (rowSeen !== null && rowSeen <= seenTo));

            row.classList.toggle('d-none', !matches);
            if (matches) visible += 1;
        });

        if (inventoryVisibleCount) inventoryVisibleCount.textContent = String(visible);
        if (inventoryTotalCount) inventoryTotalCount.textContent = String(inventoryRows.length);
        if (inventoryNoResultsRow) {
            inventoryNoResultsRow.classList.toggle('d-none', visible !== 0);
        }
        renderInventoryFilterChips();
    };

    const applyTransferHistoryFilters = () => {
        if (!transferHistoryRows.length) {
            if (transferHistoryNoResults) transferHistoryNoResults.classList.add('d-none');
            return;
        }

        const query = normalizeFilterValue(transferHistorySearchInput?.value || '');
        const status = normalizeFilterValue(transferHistoryStatusFilter?.value || '');

        let visible = 0;
        transferHistoryRows.forEach((row) => {
            const rowStatus = normalizeFilterValue(row.dataset.transferStatus || '');
            const rowSearch = normalizeFilterValue(row.dataset.transferSearch || '');
            const matches = (!query || rowSearch.includes(query))
                && (!status || rowStatus === status);

            row.classList.toggle('d-none', !matches);
            if (matches) visible += 1;
        });

        if (transferHistoryNoResults) {
            transferHistoryNoResults.classList.toggle('d-none', visible !== 0);
        }
    };

    if (inventoryActiveFilters) {
        inventoryActiveFilters.addEventListener('click', (event) => {
            const chipButton = event.target.closest('button[data-filter-key]');
            if (!chipButton || !inventoryActiveFilters.contains(chipButton)) return;
            clearInventoryFilterByKey(chipButton.dataset.filterKey || '');
            applyInventoryFilters();
        });
    }

    [inventoryFilterSearch, inventoryFilterInvoiceFolio, inventoryFilterType, inventoryFilterStatus, inventoryFilterBranch, inventoryFilterSoftware, inventoryFilterBrand, inventoryFilterModel, inventoryFilterRamMin, inventoryFilterStorageMin, inventoryFilterSeenFrom, inventoryFilterSeenTo].forEach((control) => {
        if (!control) return;
        control.addEventListener('input', applyInventoryFilters);
        control.addEventListener('change', applyInventoryFilters);
    });

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
        if (window.bootstrap?.Tooltip) {
            window.bootstrap.Tooltip.getOrCreateInstance(element);
        }
    });

    if (inventoryFilterSoftwareMode) {
        inventoryFilterSoftwareMode.addEventListener('change', applyInventoryFilters);
    }

    bindSectionTextFilter({
        inputId: 'spacesFilterInput',
        tableId: 'tableSpaces',
        emptyMessage: 'No se encontraron espacios con ese filtro',
        resetButtonId: 'spacesFilterReset',
        selectFilters: [
            { id: 'spacesFilterBranch', dataAttribute: 'spaceBranch' },
            { id: 'spacesFilterType', dataAttribute: 'spaceType' },
        ],
    });
    bindSectionTextFilter({ inputId: 'branchesFilterInput', tableId: 'tableBranches', emptyMessage: 'No se encontraron sedes con ese filtro', resetButtonId: 'branchesFilterReset' });
    bindSectionTextFilter({ inputId: 'nodeTypesFilterInput', tableId: 'tableNodeTypes', emptyMessage: 'No se encontraron tipos de nodo con ese filtro', resetButtonId: 'nodeTypesFilterReset' });
    bindSectionTextFilter({
        inputId: 'nodesFilterInput',
        tableId: 'tableNodes',
        emptyMessage: 'No se encontraron nodos con ese filtro',
        resetButtonId: 'nodesFilterReset',
        selectFilters: [
            { id: 'nodesFilterBranch', dataAttribute: 'nodeBranch' },
            { id: 'nodesFilterType', dataAttribute: 'nodeType' },
            { id: 'nodesFilterStatus', dataAttribute: 'nodeStatus' },
        ],
    });
    bindSectionTextFilter({
        inputId: 'monitoringFilterInput',
        tableId: 'tableMonitoring',
        emptyMessage: 'No se encontraron activos de monitoreo con ese filtro',
        resetButtonId: 'monitoringFilterReset',
        selectFilters: [
            { id: 'monitoringFilterBranch', dataAttribute: 'monitoringBranch' },
            { id: 'monitoringFilterStatus', dataAttribute: 'monitoringStatus' },
        ],
    });
    bindSectionTextFilter({
        inputId: 'floorPlansFilterInput',
        tableId: 'tableFloorPlans',
        emptyMessage: 'No se encontraron planos con ese filtro',
        resetButtonId: 'floorPlansFilterReset',
        selectFilters: [
            { id: 'floorPlansFilterBranch', dataAttribute: 'floorplanBranch' },
        ],
    });
    bindSectionTextFilter({ inputId: 'brandsFilterInput', tableId: 'tableBrands', emptyMessage: 'No se encontraron marcas con ese filtro', resetButtonId: 'brandsFilterReset' });
    bindSectionTextFilter({
        inputId: 'modelsFilterInput',
        tableId: 'tableModels',
        emptyMessage: 'No se encontraron modelos con ese filtro',
        resetButtonId: 'modelsFilterReset',
        selectFilters: [
            { id: 'modelsFilterBrand', dataAttribute: 'modelBrand' },
            { id: 'modelsFilterType', dataAttribute: 'modelType' },
        ],
    });
    bindSectionTextFilter({ inputId: 'softwareSystemsFilterInput', tableId: 'tableSoftware', emptyMessage: 'No se encontraron sistemas con ese filtro', resetButtonId: 'softwareSystemsFilterReset' });
    bindSectionTextFilter({
        inputId: 'relationsFilterInput',
        tableId: 'tableRelations',
        emptyMessage: 'No se encontraron relaciones con ese filtro',
        resetButtonId: 'relationsFilterReset',
        selectFilters: [
            { id: 'relationsFilterType', dataAttribute: 'relationType' },
        ],
    });

    if (inventoryFilterReset) {
        inventoryFilterReset.addEventListener('click', () => {
            if (inventoryFilterSearch) inventoryFilterSearch.value = '';
            if (inventoryFilterInvoiceFolio) inventoryFilterInvoiceFolio.value = '';
            if (inventoryFilterType) inventoryFilterType.value = '';
            if (inventoryFilterStatus) inventoryFilterStatus.value = '';
            if (inventoryFilterBranch) inventoryFilterBranch.value = '';
            if (inventoryFilterSoftware) inventoryFilterSoftware.value = '';
            if (inventoryFilterSoftwareMode) inventoryFilterSoftwareMode.value = 'contains';
            if (inventoryFilterBrand) inventoryFilterBrand.value = '';
            if (inventoryFilterModel) inventoryFilterModel.value = '';
            if (inventoryFilterRamMin) inventoryFilterRamMin.value = '';
            if (inventoryFilterStorageMin) inventoryFilterStorageMin.value = '';
            if (inventoryFilterSeenFrom) inventoryFilterSeenFrom.value = '';
            if (inventoryFilterSeenTo) inventoryFilterSeenTo.value = '';
            applyInventoryFilters();
        });
    }

    [transferHistorySearchInput, transferHistoryStatusFilter].forEach((control) => {
        if (!control) return;
        control.addEventListener('input', applyTransferHistoryFilters);
        control.addEventListener('change', applyTransferHistoryFilters);
    });

    if (transferHistoryFilterReset) {
        transferHistoryFilterReset.addEventListener('click', () => {
            if (transferHistorySearchInput) transferHistorySearchInput.value = '';
            if (transferHistoryStatusFilter) transferHistoryStatusFilter.value = '';
            applyTransferHistoryFilters();
        });
    }

    btnOpenNewAssetModal?.addEventListener('click', () => {
        resetAssetModalToCreate();
    });

    document.querySelectorAll('.js-edit-asset').forEach((button) => {
        button.addEventListener('click', (event) => {
            const row = event.currentTarget.closest('tr[data-asset-id]');
            openAssetModalForEdit(row);
        });
    });

    document.querySelectorAll('.js-reassign-asset').forEach((button) => {
        button.addEventListener('click', (event) => {
            const row = event.currentTarget.closest('tr[data-asset-id]');
            openAssetModalForReassign(row);
        });
    });

    document.querySelectorAll('.js-request-transfer-asset').forEach((button) => {
        button.addEventListener('click', (event) => {
            const row = event.currentTarget.closest('tr[data-asset-id]');
            openAssetModalForTransferRequest(row);
        });
    });

    if (modalEditEquipmentTypeCatalog) {
        modalEditEquipmentTypeCatalog.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            if (!trigger || !modalEditEquipmentTypeCatalogForm) return;

            const catalogId = String(trigger.dataset.catalogId || '').trim();
            modalEditEquipmentTypeCatalogForm.action = `{{ url('/admin/asset-equipment-type-catalogs') }}/${catalogId}`;
            if (editEquipmentTypeCatalogKey) editEquipmentTypeCatalogKey.value = String(trigger.dataset.catalogKey || '');
            if (editEquipmentTypeCatalogLabel) editEquipmentTypeCatalogLabel.value = String(trigger.dataset.catalogLabel || '');
            if (editEquipmentTypeCatalogSortOrder) editEquipmentTypeCatalogSortOrder.value = String(trigger.dataset.catalogSortOrder || '100');
            if (editEquipmentTypeCatalogIsActive) editEquipmentTypeCatalogIsActive.value = String(trigger.dataset.catalogIsActive || '1');
            if (editEquipmentTypeCatalogDescription) editEquipmentTypeCatalogDescription.value = String(trigger.dataset.catalogDescription || '');
        });
    }

    if (modalEditAssetStatusCatalog) {
        modalEditAssetStatusCatalog.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            if (!trigger || !modalEditAssetStatusCatalogForm) return;

            const catalogId = String(trigger.dataset.catalogId || '').trim();
            modalEditAssetStatusCatalogForm.action = `{{ url('/admin/asset-status-catalogs') }}/${catalogId}`;
            if (editAssetStatusCatalogKey) editAssetStatusCatalogKey.value = String(trigger.dataset.catalogKey || '');
            if (editAssetStatusCatalogLabel) editAssetStatusCatalogLabel.value = String(trigger.dataset.catalogLabel || '');
            if (editAssetStatusCatalogSortOrder) editAssetStatusCatalogSortOrder.value = String(trigger.dataset.catalogSortOrder || '100');
            if (editAssetStatusCatalogIsActive) editAssetStatusCatalogIsActive.value = String(trigger.dataset.catalogIsActive || '1');
            if (editAssetStatusCatalogDescription) editAssetStatusCatalogDescription.value = String(trigger.dataset.catalogDescription || '');
        });
    }

    if (adModalReassignBtn) {
        adModalReassignBtn.addEventListener('click', () => {
            openReassignFromAssetDetail();
        });
    }

    if (modalMySignature && signaturePad) {
        modalMySignature.addEventListener('shown.bs.modal', () => {
            signaturePad.resizeCanvas();
            const existingDataUrl = String(currentUserSignatureDataUrlInput?.value || '').trim();
            if (existingDataUrl) {
                signaturePad.loadDataUrl(existingDataUrl);
            } else {
                signaturePad.clear();
            }
            if (signatureClearInput) signatureClearInput.value = '0';
        });
    }

    signatureClearCanvasBtn?.addEventListener('click', () => {
        signaturePad?.clear();
        if (signatureClearInput) signatureClearInput.value = '0';
    });

    signatureDeleteBtn?.addEventListener('click', () => {
        if (!signatureClearInput || !modalMySignatureForm) return;
        signatureClearInput.value = '1';
        modalMySignatureForm.submit();
    });

    modalMySignatureForm?.addEventListener('submit', (event) => {
        if (!signatureCanvas || !signatureDataUrlInput || !signatureClearInput) return;
        const isClearMode = signatureClearInput.value === '1';
        if (isClearMode) {
            signatureDataUrlInput.value = '';
            return;
        }

        if (!signatureHasStroke) {
            event.preventDefault();
            alert('Dibuja tu firma antes de guardar.');
            return;
        }

        signatureDataUrlInput.value = signatureCanvas.toDataURL('image/png');
    });

    document.querySelectorAll('.js-open-asset-detail').forEach((button) => {
        button.addEventListener('click', (event) => {
            const row = event.currentTarget.closest('tr[data-asset-id]');
            if (!row?.dataset?.assetId) return;
            openAssetDetailModal(row.dataset.assetId);
        });
    });

    applyInventoryFilters();
    applyTransferHistoryFilters();
    activateTabFromHash();
    focusAssetFromUrl();

    invoiceDraftRows.forEach((row) => {
        const serialInput = row.querySelector('.js-draft-serial');
        const descriptionInput = row.querySelector('.js-draft-description');
        const typeSelect = row.querySelector('.js-draft-equipment-type');
        const brandInput = row.querySelector('.js-draft-brand');
        const modelInput = row.querySelector('.js-draft-model');

        if (serialInput) {
            serialInput.addEventListener('input', () => refreshDraftSerialBadge(row));
        }

        [descriptionInput, typeSelect, brandInput, modelInput].forEach((control) => {
            if (!control) return;
            control.addEventListener('input', () => refreshDraftFieldConfidenceBadges(row));
            control.addEventListener('change', () => refreshDraftFieldConfidenceBadges(row));
        });

        refreshDraftSerialBadge(row);
        refreshDraftFieldConfidenceBadges(row);
    });

    invoiceDraftImportForm?.addEventListener('submit', (event) => {
        const payload = buildEditedInvoicePayload();
        if (!payload || !assetInvoicePayloadInput) {
            event.preventDefault();
            alert('No fue posible construir el borrador editado.');
            return;
        }

        if (!Array.isArray(payload.items) || payload.items.length === 0) {
            event.preventDefault();
            alert('Selecciona al menos un equipo para importar.');
            return;
        }

        assetInvoicePayloadInput.value = JSON.stringify(payload);
    });

    assetInvoiceAnalyzerForm?.addEventListener('submit', () => {
        if (assetInvoiceAnalyzerSubmitButton) {
            assetInvoiceAnalyzerSubmitButton.disabled = true;
            if (analyzerSubmitLabel) {
                analyzerSubmitLabel.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Analizando...';
            } else {
                assetInvoiceAnalyzerSubmitButton.textContent = 'Analizando...';
            }
        }

        if (window.bootstrap?.Modal && modalAssetInvoiceAnalyzer) {
            const analyzerModal = window.bootstrap.Modal.getOrCreateInstance(modalAssetInvoiceAnalyzer);
            analyzerModal.hide();
        }
    });

    if (hasInvoiceDraft && autoOpenInvoiceDraft && modalAssetInvoiceDraft) {
        let attempts = 0;
        const maxAttempts = 20;
        const openDraftModalWhenReady = () => {
            attempts += 1;

            if (window.bootstrap?.Modal) {
                const draftModal = window.bootstrap.Modal.getOrCreateInstance(modalAssetInvoiceDraft);
                draftModal.show();
                return;
            }

            if (btnOpenInvoiceDraftModal) {
                btnOpenInvoiceDraftModal.click();
                return;
            }

            if (attempts < maxAttempts) {
                window.setTimeout(openDraftModalWhenReady, 120);
            }
        };

        window.setTimeout(openDraftModalWhenReady, 180);
    }
})();
</script>
@endpush
