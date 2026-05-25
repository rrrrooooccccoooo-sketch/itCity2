@extends('tenant.layouts.app')

@section('content')
@php
    $canInventoryManage = auth()->user()?->hasTenantPermission('inventory.manage') ?? false;
    $canTopologyManage  = auth()->user()?->hasTenantPermission('topology.manage') ?? false;
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

    {{-- Navigation Tabs --}}
    <div class="sticky-navigation mb-3">
        <div class="sticky-navigation-inner">
            <span class="sticky-navigation-label">Módulos</span>
            <ul class="nav nav-pills flex-nowrap gap-1" role="tablist">
            <li class="nav-item">
                <a href="#section-monitoring" class="nav-link active small" data-bs-toggle="tab">
                    <i class="bi bi-speedometer2"></i> Monitoreo
                </a>
            </li>
            <li class="nav-item">
                <a href="#section-assets" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-hdd"></i> Inventario
                </a>
            </li>
            <li class="nav-item">
                <a href="#section-nodes" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-hdd-network"></i> Nodos
                </a>
            </li>
            <li class="nav-item">
                <a href="#section-spaces" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-door-closed"></i> Espacios físicos
                </a>
            </li>
            <li class="nav-item">
                <a href="#section-branches" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-diagram-3"></i> Sedes
                </a>
            </li>
            <li class="nav-item">
                <a href="#section-node-types" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-diagram-2"></i> Tipos de nodo
                </a>
            </li>
            <li class="nav-item">
                <a href="#section-floor-plans" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-diagram-3"></i> Planos
                </a>
            </li>
            <li class="nav-item">
                <a href="#section-equipment-brands" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-box2"></i> Marcas
                </a>
            </li>
            <li class="nav-item">
                <a href="#section-equipment-models" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-boxes"></i> Modelos
                </a>
            </li>
            <li class="nav-item">
                <a href="#section-software" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-app-indicator"></i> Sistemas
                </a>
            </li>
            <li class="nav-item">
                <a href="#section-relations" class="nav-link small" data-bs-toggle="tab">
                    <i class="bi bi-share"></i> Relaciones
                </a>
            </li>
            </ul>
        </div>
    </div>

    {{-- TAB CONTENT --}}
    <div class="tab-content">

        {{-- ===== PHYSICAL SPACES SECTION (1) ===== --}}
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

        {{-- ===== BRANCHES SECTION (2) ===== --}}
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
        {{-- ===== NODE TYPES SECTION (3) ===== --}}
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

        {{-- ===== NODES SECTION (4) ===== --}}
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

        {{-- ===== MONITORING SECTION (5) ===== --}}
        <div class="tab-pane fade show active" id="section-monitoring">
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

        {{-- ===== FLOOR PLANS SECTION (6) ===== --}}
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

        {{-- ===== EQUIPMENT BRANDS SECTION (7) ===== --}}
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

        {{-- ===== EQUIPMENT MODELS SECTION (8) ===== --}}
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

        {{-- ===== ASSETS/INVENTORY SECTION (9) ===== --}}
        <div class="tab-pane fade" id="section-assets">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Inventario TI</h3>
                    <p class="text-muted small">Equipamiento: desktops, laptops, servidores, impresoras, etc.</p>
                </div>
                @if ($canInventoryManage)
                <button id="btnOpenNewAssetModal" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAsset">
                    <i class="bi bi-plus-circle"></i> Nuevo activo
                </button>
                @endif
            </div>

            @php
                $assetFilterTypes = $computerAssets->pluck('equipment_type')->filter()->unique()->sort()->values();
                $assetFilterStatuses = $computerAssets->pluck('status')->filter()->unique()->sort()->values();
                $assetFilterBranches = $computerAssets->map(fn ($asset) => optional($asset->branch)->name)->filter()->unique()->sort()->values();
                $assetFilterBrands = $computerAssets->pluck('brand')->filter()->unique()->sort()->values();
                $assetFilterModels = $computerAssets->pluck('model')->filter()->unique()->sort()->values();
            @endphp

            <div class="card">
                <div class="m-2 p-3 border rounded bg-light">
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
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="inventoryAssetsTable">
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
                                        $assetSoftwareIndex,
                                    ])->filter()->join(' '));
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
                                    data-asset-node-id="{{ $asset->node_id ?? '' }}"
                                    data-asset-equipment-type="{{ $asset->equipment_type ?? '' }}"
                                    data-asset-hostname="{{ $asset->hostname ?? '' }}"
                                    data-asset-tag="{{ $asset->asset_tag ?? '' }}"
                                    data-asset-assigned-user="{{ $asset->assigned_user ?? '' }}"
                                    data-asset-notes="{{ $asset->notes ?? '' }}"
                                    data-asset-responsiva-reference="{{ data_get($asset->details, 'responsiva.reference', '') }}"
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
                                        @endphp
                                        <div>{{ $asset->last_seen_at?->format('d/m H:i') ?? '—' }}</div>
                                        <div class="mt-1">
                                            <span class="monitor-pill {{ $monitorStateClass }}">{{ $monitorStateLabel }}</span>
                                        </div>
                                        @if ($hasHeartbeat && $hasMonitoringMetrics)
                                            <div class="small mt-1">
                                                CPU {{ $cpuUsage !== null ? number_format((float) $cpuUsage, 1) . '%' : 'N/A' }} ·
                                                RAM {{ $memoryUsage !== null ? number_format((float) $memoryUsage, 1) . '%' : 'N/A' }} ·
                                                Disco {{ $diskUsage !== null ? number_format((float) $diskUsage, 1) . '%' : 'N/A' }}
                                            </div>
                                        @elseif ($hasHeartbeat)
                                            <div class="small mt-1 text-muted">Agente activo, esperando métricas de auditoría</div>
                                        @endif
                                        <div class="text-muted small">
                                            {{ $inventoryMetaSummary }}
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1 js-open-asset-detail">
                                            <i class="bi bi-info-circle"></i> Info adicional
                                        </button>
                                    </td>
                                    <td>
                                        <a href="{{ url('/admin/computer-assets/' . $asset->id . '/responsiva/preview') }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        <a href="{{ url('/admin/computer-assets/' . $asset->id . '/responsiva') }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-filetype-pdf"></i> Descargar
                                        </a>
                                        <a href="{{ url('/admin/computer-assets/' . $asset->id . '/assignment-log') }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                                            <i class="bi bi-journal-text"></i> Bitácora
                                        </a>
                                        @if ($canInventoryManage)
                                        <button type="button" class="btn btn-sm btn-outline-warning js-reassign-asset" data-bs-toggle="modal" data-bs-target="#modalAssetReassign">
                                            <i class="bi bi-arrow-left-right"></i> Reasignar
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary js-edit-asset" data-bs-toggle="modal" data-bs-target="#modalAsset">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        @endif
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

        {{-- ===== SOFTWARE SYSTEMS SECTION (10) ===== --}}
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

        {{-- ===== RELATIONS SECTION (11) ===== --}}
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
                        <input type="text" name="name" class="form-control" required placeholder="Ej: Oficina Central">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="address" class="form-control" placeholder="Dirección completa">
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

    if (inventoryActiveFilters) {
        inventoryActiveFilters.addEventListener('click', (event) => {
            const chipButton = event.target.closest('button[data-filter-key]');
            if (!chipButton || !inventoryActiveFilters.contains(chipButton)) return;
            clearInventoryFilterByKey(chipButton.dataset.filterKey || '');
            applyInventoryFilters();
        });
    }

    [inventoryFilterSearch, inventoryFilterType, inventoryFilterStatus, inventoryFilterBranch, inventoryFilterSoftware, inventoryFilterBrand, inventoryFilterModel, inventoryFilterRamMin, inventoryFilterStorageMin, inventoryFilterSeenFrom, inventoryFilterSeenTo].forEach((control) => {
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
    activateTabFromHash();
    focusAssetFromUrl();
})();
</script>
@endpush
