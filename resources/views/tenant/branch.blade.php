@extends('tenant.layouts.app')

@section('title', $branch->name)
@section('page_title', $branch->name)

@section('topbar_actions')
    <a href="{{ url('/sede/' . $branch->id . '/red') }}" class="btn btn-sm btn-primary">Mapa de red</a>
    <a href="{{ url('/admin') }}" class="btn btn-sm btn-dark">Admin</a>
    <a href="{{ url('/') }}" class="btn btn-sm btn-outline-secondary">Ciudad</a>
@endsection

@push('styles')
<style>
    .drill-shell {
        overflow: hidden;
    }

    .drill-shell .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .drill-path {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .drill-path-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .74rem;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 999px;
        padding: 4px 10px;
        font-weight: 600;
    }

    .drill-levels {
        display: grid;
        grid-template-columns: repeat(4, minmax(0,1fr));
        gap: 12px;
        margin-bottom: 12px;
    }

    .drill-level {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        padding: 12px;
        cursor: pointer;
        transition: all .2s ease;
    }

    .drill-level:hover {
        border-color: #93c5fd;
        box-shadow: 0 8px 20px rgba(37, 99, 235, .08);
        transform: translateY(-2px);
    }

    .drill-level.active {
        border-color: #2563eb;
        background: linear-gradient(180deg, #eff6ff, #ffffff);
    }

    .drill-level .kicker {
        font-size: .66rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin-bottom: 2px;
    }

    .drill-level .title {
        font-size: .9rem;
        font-weight: 700;
        color: #0f172a;
    }

    .drill-level .meta {
        font-size: .74rem;
        color: #64748b;
        margin-top: 3px;
    }

    .drill-stage {
        position: relative;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: radial-gradient(circle at 20% 15%, #f8fbff, #f1f5f9 70%);
        overflow: hidden;
        min-height: 430px;
    }

    .drill-grid-bg {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(to right, rgba(148,163,184,.14) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(148,163,184,.14) 1px, transparent 1px);
        background-size: 34px 34px;
        opacity: .7;
        pointer-events: none;
    }

    .drill-viewport {
        position: relative;
        width: 100%;
        height: 430px;
        overflow: hidden;
    }

    .drill-canvas {
        position: absolute;
        width: 1900px;
        height: 1120px;
        transform-origin: top left;
        transition: transform .62s cubic-bezier(.22,.61,.36,1);
        will-change: transform;
    }

    .drill-scene {
        position: absolute;
        width: 720px;
        min-height: 280px;
        border: 1px solid #bfdbfe;
        border-radius: 14px;
        background: rgba(255,255,255,.95);
        box-shadow: 0 14px 34px rgba(15, 23, 42, .1);
        padding: 14px;
    }

    .drill-scene::before {
        content: attr(data-title);
        display: inline-block;
        margin-bottom: 10px;
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #1d4ed8;
        font-weight: 700;
    }

    .drill-scene-general { left: 80px; top: 80px; }
    .drill-scene-hardware { left: 980px; top: 120px; }
    .drill-scene-software { left: 120px; top: 620px; }
    .drill-scene-nodes { left: 1020px; top: 650px; }

    .drill-scene.active {
        border-color: #2563eb;
        box-shadow: 0 18px 44px rgba(37, 99, 235, .2);
    }

    .drill-panel-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 10px;
    }

    .drill-mini-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px;
    }

    .drill-mini-card .name {
        font-size: .82rem;
        font-weight: 700;
        color: #0f172a;
    }

    .drill-mini-card .small {
        font-size: .72rem;
        color: #64748b;
    }

    .hier-shell {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15,23,42,.06);
        overflow: hidden;
    }

    .hier-top {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 12px 14px;
        border-bottom: 1px solid #eef2f7;
        align-items: center;
    }

    .hier-path {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        font-size: .76rem;
        color: #64748b;
    }

    .hier-path .chip {
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 999px;
        padding: 3px 9px;
        font-weight: 600;
    }

    .hier-tabs {
        display: inline-flex;
        gap: 6px;
    }

    .hier-tab {
        border: 1px solid #d1d5db;
        background: #fff;
        color: #334155;
        border-radius: 10px;
        padding: 6px 11px;
        font-size: .78rem;
        font-weight: 600;
        cursor: pointer;
    }

    .hier-tab.active {
        border-color: #2563eb;
        background: #2563eb;
        color: #fff;
    }

    .hier-body {
        padding: 14px;
        background: #f8fafc;
    }

    .hier-pane { display: none; }
    .hier-pane.active { display: block; }

    .hier-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 10px;
    }

    .hier-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        padding: 10px;
        min-height: 110px;
    }

    .hier-kicker {
        font-size: .64rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #94a3b8;
        margin-bottom: 2px;
    }

    .hier-title {
        font-size: .86rem;
        color: #0f172a;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .hier-meta {
        font-size: .74rem;
        color: #64748b;
    }

    .router-ports {
        display: grid;
        grid-template-columns: repeat(8, minmax(0,1fr));
        gap: 4px;
        margin-top: 9px;
    }

    .ap-panel {
        margin-top: 9px;
        border: 1px solid #e0e7ff;
        border-radius: 8px;
        background: #f5f7ff;
        padding: 7px 8px;
    }

    .ap-panel-title {
        font-size: .58rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #6366f1;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .ap-radio {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 4px 6px;
        border-radius: 5px;
        margin-bottom: 3px;
        cursor: pointer;
        border: 1px solid #e0e7ff;
        background: #fff;
        font-size: .7rem;
        transition: box-shadow .1s;
    }

    .ap-radio:hover { box-shadow: 0 0 0 2px #a5b4fc; }

    .ap-radio .band { font-weight: 700; width: 42px; color: #4f46e5; font-size: .68rem; }
    .ap-radio .ssid { flex: 1; color: #334155; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ap-radio .signal { font-size: .68rem; }
    .ap-radio.up   .band { color: #166534; }
    .ap-radio.down .band { color: #991b1b; }
    .ap-radio.unused .band { color: #94a3b8; }

    .fw-panel {
        margin-top: 9px;
        border: 1px solid #fde68a;
        border-radius: 8px;
        background: #fffbeb;
        padding: 7px 8px;
    }

    .fw-panel-title {
        font-size: .58rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #b45309;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .fw-iface {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 4px 6px;
        border-radius: 5px;
        margin-bottom: 3px;
        cursor: pointer;
        border: 1px solid #fde68a;
        background: #fff;
        font-size: .7rem;
        transition: box-shadow .1s;
    }

    .fw-iface:hover { box-shadow: 0 0 0 2px #fcd34d; }
    .fw-iface .iface-label { font-weight: 700; width: 46px; color: #92400e; font-size: .68rem; }
    .fw-iface .iface-info  { flex: 1; color: #334155; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .fw-iface .iface-dot   { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .fw-iface.up   .iface-dot { background: #22c55e; }
    .fw-iface.down .iface-dot { background: #ef4444; }
    .fw-iface.unused .iface-dot { background: #94a3b8; }

    .router-port {
        height: 18px;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        font-size: .61rem;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: box-shadow .1s;
    }

    .router-port:hover { box-shadow: 0 0 0 2px #93c5fd; }

    .router-port.up {
        border-color: #86efac;
        background: #dcfce7;
        color: #166534;
        font-weight: 700;
    }

    .router-port.down {
        border-color: #fca5a5;
        background: #fee2e2;
        color: #991b1b;
    }

    @media (max-width: 992px) {
        .hier-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
    }

    @media (max-width: 576px) {
        .hier-grid { grid-template-columns: 1fr; }
    }

    .drill-map {
        position: absolute;
        right: 10px;
        bottom: 10px;
        width: 170px;
        height: 112px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background: rgba(255,255,255,.88);
        box-shadow: 0 8px 20px rgba(15,23,42,.14);
        padding: 7px;
        z-index: 2;
    }

    .drill-map-title {
        font-size: .62rem;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #64748b;
        margin-bottom: 4px;
        font-weight: 700;
    }

    .drill-map-canvas {
        position: relative;
        height: 82px;
        border-radius: 7px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .map-node {
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #93c5fd;
        border: 1px solid #60a5fa;
        transform: translate(-50%, -50%);
        transition: all .25s ease;
    }

    .map-node.active {
        width: 13px;
        height: 13px;
        background: #2563eb;
        border-color: #1d4ed8;
        box-shadow: 0 0 0 3px rgba(37,99,235,.22);
    }

    .map-focus {
        position: absolute;
        width: 34px;
        height: 24px;
        border: 1px dashed #2563eb;
        border-radius: 5px;
        background: rgba(37,99,235,.08);
        transform: translate(-50%, -50%);
        transition: left .62s cubic-bezier(.22,.61,.36,1), top .62s cubic-bezier(.22,.61,.36,1);
    }

    @keyframes panelIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 992px) {
        .drill-levels { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .drill-panel-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .drill-map { display: none; }
    }

    @media (max-width: 576px) {
        .drill-levels, .drill-panel-grid { grid-template-columns: 1fr; }
        .drill-viewport { height: 360px; }
    }
</style>
@endpush

@section('content')
<div>

    <div class="hier-shell mb-4" id="hierExplorer">
        <div class="hier-top">
            <div class="hier-path">
                <span class="chip">🏙 Sucursal</span>
                <span>/</span>
                <span class="chip" id="hierPathFocus">🧱 Hardware</span>
            </div>

            <div class="hier-tabs ms-md-3">
                <button type="button" class="hier-tab active" data-pane="hardware">Hardware</button>
                <button type="button" class="hier-tab" data-pane="software">Software</button>
                <button type="button" class="hier-tab" data-pane="spaces">Espacios físicos</button>
            </div>

            <input id="hierSearch" type="text" class="form-control form-control-sm ms-md-auto" style="max-width:280px" placeholder="Buscar sistema o nodo...">
        </div>

        <div class="hier-body">
            <div class="hier-pane active" data-pane="hardware">
                <div class="hier-grid" id="hardwareGrid"></div>
            </div>

            <div class="hier-pane" data-pane="software">
                <div class="hier-grid" id="softwareGrid"></div>
            </div>

            <div class="hier-pane" data-pane="spaces">
                <div class="hier-grid" id="spacesGrid"></div>
            </div>
        </div>
    </div>

    <div class="card ic-card mb-4 drill-shell" id="drillShell">
        <div class="card-header">
            <span>Drill-down visual</span>
            <div class="drill-path" id="drillPath">
                <span class="drill-path-chip">🏙 Ciudad</span>
                <span class="drill-path-chip">🏢 {{ $branch->name }}</span>
                <span class="drill-path-chip" id="drillPathFocus">📋 General</span>
            </div>
        </div>
        <div class="card-body">
            <div class="drill-levels" id="drillLevels">
                <button type="button" class="drill-level active" data-target="general">
                    <div class="kicker">Nivel 1</div>
                    <div class="title">General</div>
                    <div class="meta">Vista agregada de la sucursal</div>
                </button>
                <button type="button" class="drill-level" data-target="hardware">
                    <div class="kicker">Nivel 2</div>
                    <div class="title">Hardware</div>
                    <div class="meta">Tipos y nodos físicos</div>
                </button>
                <button type="button" class="drill-level" data-target="software">
                    <div class="kicker">Nivel 2</div>
                    <div class="title">Software</div>
                    <div class="meta">Sistemas desplegados</div>
                </button>
                <button type="button" class="drill-level" data-target="nodes">
                    <div class="kicker">Nivel 3</div>
                    <div class="title">Nodos</div>
                    <div class="meta">Detalle por activo</div>
                </button>
            </div>

            <div class="drill-stage">
                <div class="drill-grid-bg"></div>
                <div class="drill-viewport">
                    <div class="drill-canvas" id="drillCanvas">
                        <section class="drill-scene drill-scene-general active" data-scene="general" data-title="Nivel 1 · General">
                            <div class="drill-panel-grid">
                                <div class="drill-mini-card">
                                    <div class="name">Nodos totales</div>
                                    <div class="small">{{ $branchNodes->count() }} activos registrados</div>
                                </div>
                                <div class="drill-mini-card">
                                    <div class="name">Tipos de nodo</div>
                                    <div class="small">{{ $nodeTypes->count() }} categorías</div>
                                </div>
                                <div class="drill-mini-card">
                                    <div class="name">Sistemas</div>
                                    <div class="small">{{ $systems->count() }} desplegados</div>
                                </div>
                            </div>
                        </section>

                        <section class="drill-scene drill-scene-hardware" data-scene="hardware" data-title="Nivel 2 · Hardware">
                            <div class="drill-panel-grid">
                                @forelse($nodeTypes as $type)
                                    <div class="drill-mini-card">
                                        <div class="name">{{ $type->name }}</div>
                                        <div class="small">Nodos: {{ $type->nodes_count }}</div>
                                    </div>
                                @empty
                                    <div class="drill-mini-card">
                                        <div class="name">Sin hardware clasificado</div>
                                        <div class="small">No hay tipos de nodo disponibles</div>
                                    </div>
                                @endforelse
                            </div>
                        </section>

                        <section class="drill-scene drill-scene-software" data-scene="software" data-title="Nivel 2 · Software">
                            <div class="drill-panel-grid">
                                @forelse($systems as $system)
                                    <div class="drill-mini-card">
                                        <div class="name">{{ $system->name }}</div>
                                        <div class="small">{{ optional($system->node)->name ?? 'Sin nodo' }} · v{{ $system->version ?? 'N/A' }}</div>
                                        <div class="mt-2">
                                            @if (optional($system->node)->id)
                                                <a href="{{ url('/nodos/' . $system->node->id) }}" class="btn btn-sm btn-outline-primary">Ir al nodo</a>
                                            @else
                                                <a href="{{ url('/admin?edit_software=' . $system->id) }}" class="btn btn-sm btn-outline-secondary">Configurar</a>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="drill-mini-card">
                                        <div class="name">Sin software registrado</div>
                                        <div class="small">No hay sistemas para esta sucursal</div>
                                        <div class="mt-2">
                                            <a href="{{ url('/admin') }}" class="btn btn-sm btn-outline-secondary">Ir a Admin</a>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </section>

                        <section class="drill-scene drill-scene-nodes" data-scene="nodes" data-title="Nivel 3 · Nodos">
                            <div class="drill-panel-grid">
                                @forelse($branchNodes as $bnode)
                                    <a href="{{ url('/nodos/' . $bnode->id) }}" class="drill-mini-card text-decoration-none">
                                        <div class="name">{{ $bnode->name }}</div>
                                        <div class="small">{{ $bnode->status ?? 'N/A' }} · {{ $bnode->ip_address ?? 'sin IP' }}</div>
                                    </a>
                                @empty
                                    <div class="drill-mini-card">
                                        <div class="name">Sin nodos</div>
                                        <div class="small">No hay activos para listar</div>
                                    </div>
                                @endforelse
                            </div>
                        </section>
                    </div>
                </div>

                <div class="drill-map" aria-hidden="true">
                    <div class="drill-map-title">Mapa de contexto</div>
                    <div class="drill-map-canvas" id="drillMapCanvas">
                        <span class="map-node" data-map-node="general" style="left:22%;top:24%"></span>
                        <span class="map-node" data-map-node="hardware" style="left:78%;top:28%"></span>
                        <span class="map-node" data-map-node="software" style="left:26%;top:74%"></span>
                        <span class="map-node" data-map-node="nodes" style="left:80%;top:76%"></span>
                        <span class="map-focus" id="drillMapFocus" style="left:22%;top:24%"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card ic-card h-100">
                <div class="card-header">Tipos de nodo</div>
                <ul class="list-group list-group-flush">
                    @forelse ($nodeTypes as $nodeType)
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size:.875rem">
                            <span>{{ $nodeType->name }}</span>
                            <span class="badge rounded-pill text-bg-primary">{{ $nodeType->nodes_count }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted small">No hay tipos de nodo configurados.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card ic-card h-100">
                <div class="card-header">Software alojado</div>
                <ul class="list-group list-group-flush">
                    @forelse ($systems as $system)
                        <li class="list-group-item">
                            <div class="fw-semibold" style="font-size:.875rem">{{ $system->name }} <span class="text-muted fw-normal">v{{ $system->version ?? 'N/A' }}</span></div>
                            <div class="small text-muted">{{ optional($system->node)->name ?? 'Sin asignar' }}{{ $system->vendor ? ' · ' . $system->vendor : '' }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted small">No hay sistemas registrados.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

<div class="card ic-card mb-4">
        <div class="card-header">Nodos del campus</div>
        <div class="table-responsive">
            <table class="table table-hover ic-table mb-0">
                <thead>
                    <tr>
                        <th>Nodo</th>
                        <th>Estado</th>
                        <th>IP</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($branchNodes as $bnode)
                        @php $st = strtolower($bnode->status ?? 'inactive'); @endphp
                        <tr>
                            <td class="fw-semibold">{{ $bnode->name }}</td>
                            <td><span class="sb-badge {{ in_array($st,['active','warning','error','inactive']) ? $st : 'default' }}">{{ $bnode->status ?? 'N/A' }}</span></td>
                            <td class="text-muted"><code style="font-size:.8rem">{{ $bnode->ip_address ?? '—' }}</code></td>
                            <td class="text-end">
                                <a href="{{ url('/nodos/' . $bnode->id) }}" class="btn btn-sm btn-outline-primary">Ver detalle</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4 small">No hay nodos registrados para esta sede.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

<div class="card ic-card">
        <div class="card-header">Monitoreo bajo demanda</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-5">
                    <div class="list-group list-group-flush">
                        @forelse ($monitoredNodes as $mnode)
                            @php $mst = strtolower($mnode->status ?? 'inactive'); @endphp
                            <button
                                type="button"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center js-node-metrics"
                                style="font-size:.875rem"
                                data-url="{{ url('/nodos/' . $mnode->id . '/metricas') }}">
                                <span class="fw-semibold">{{ $mnode->name }}</span>
                                <span class="sb-badge {{ in_array($mst,['active','warning','error','inactive']) ? $mst : 'default' }}">{{ $mnode->status ?? 'N/A' }}</span>
                            </button>
                        @empty
                            <p class="text-muted small mb-0">No hay nodos monitoreables en esta sede.</p>
                        @endforelse
                    </div>
                </div>
                <div class="col-lg-7">
                    <div id="metricsPanel" class="border rounded-3 p-3 h-100" style="background:#f8fafc;min-height:140px">
                        <p class="text-muted small mb-0">Selecciona un nodo para ver CPU, RAM y disco.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Port detail popup --}}
<div id="portPopup" style="position:fixed;z-index:8999;background:#fff;border:1px solid #bfdbfe;border-radius:10px;box-shadow:0 8px 28px rgba(15,23,42,.18);padding:13px 15px;min-width:190px;max-width:240px;font-size:.82rem;display:none">
    <button id="portPopupClose" style="position:absolute;top:7px;right:10px;background:none;border:none;font-size:1.1rem;color:#94a3b8;cursor:pointer;line-height:1;padding:0">×</button>
    <div id="portPopupTitle" style="font-weight:700;font-size:.86rem;margin-bottom:3px"></div>
    <div id="portPopupStatus" style="font-size:.75rem;margin-bottom:2px"></div>
    <div id="portPopupConnected" style="color:#64748b;font-size:.75rem;margin-bottom:10px"></div>
    <a id="portPopupEditLink" href="/admin" class="btn btn-sm btn-outline-primary w-100" style="font-size:.76rem">Configurar en Admin</a>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const explorerNodes = @json($branchNodesExplorer ?? []);
    const explorerSystems = @json($systemsExplorer ?? []);

    const hierSearch = document.getElementById('hierSearch');
    const hierTabs = document.querySelectorAll('.hier-tab');
    const hierPanes = document.querySelectorAll('.hier-pane');
    const hierPathFocus = document.getElementById('hierPathFocus');
    const hardwareGrid = document.getElementById('hardwareGrid');
    const softwareGrid = document.getElementById('softwareGrid');
    const spacesGrid = document.getElementById('spacesGrid');

    const pathLabel = {
        hardware: '🧱 Hardware',
        software: '🧩 Software',
        spaces: '🏠 Espacios físicos',
    };

    const nodeTypeSlug = (node) => String(node?.node_type?.slug || node?.nodeType?.slug || node?.node_type?.name || node?.nodeType?.name || '').toLowerCase();

    const hasPortPanel = (node) => {
        const t = nodeTypeSlug(node);
        return t.includes('router') || t.includes('switch') || t.includes('firewall');
    };

    const isDatabaseNode = (node) => {
        const t = nodeTypeSlug(node);
        return t.includes('database') || t.includes('db') || t.includes('sql');
    };

    const isAccessPoint = (node) => nodeTypeSlug(node).includes('access') || nodeTypeSlug(node).includes('ap');
    const isFirewall   = (node) => nodeTypeSlug(node).includes('firewall');

    const DEFAULT_FW_IFACES = [
        { name: 'WAN',   status: 'up',   connected_to: '' },
        { name: 'LAN-1', status: 'up',   connected_to: '' },
        { name: 'LAN-2', status: 'unused', connected_to: '' },
        { name: 'DMZ',   status: 'unused', connected_to: '' },
    ];

    const DEFAULT_AP_RADIOS = [
        { name: '2.4 GHz', status: 'up',   connected_to: '', ssid: '' },
        { name: '5 GHz',   status: 'up',   connected_to: '', ssid: '' },
        { name: '6 GHz',   status: 'unused', connected_to: '', ssid: '' },
        { name: 'Uplink',  status: 'up',   connected_to: '', ssid: '' },
    ];

    const getPorts = (node) => {
        const details = node?.details || {};
        if (Array.isArray(details?.ports) && details.ports.length) {
            return details.ports.map((p, idx) => ({
                label: p?.name || `P${idx + 1}`,
                status: p?.status || (p?.up ? 'up' : 'unused'),
                connected_to: p?.connected_to || '',
                ssid: p?.ssid || '',
            }));
        }
        if (isFirewall(node))   return DEFAULT_FW_IFACES.map(f => ({ ...f, label: f.name }));
        if (isAccessPoint(node)) return DEFAULT_AP_RADIOS.map(r => ({ ...r, label: r.name }));
        return Array.from({ length: 8 }, (_, idx) => ({
            label: `P${idx + 1}`,
            status: idx < 2 ? 'up' : 'unused',
            connected_to: '',
            ssid: '',
        }));
    };

    const renderHardware = (query = '') => {
        const q = query.trim().toLowerCase();
        const filtered = explorerNodes.filter((n) => {
            if (!q) return true;
                return [
                    n.name,
                    n.ip_address,
                    n.node_type?.name,
                    n.nodeType?.name,
                    n.room,
                    n.floor,
                    n.physical_space?.name,
                    n.physicalSpace?.name,
                    n.physical_space?.space_type,
                    n.physicalSpace?.space_type,
                ]
                .filter(Boolean)
                .some((v) => String(v).toLowerCase().includes(q));
        });

        hardwareGrid.innerHTML = filtered.length ? filtered.map((n) => {
            const typeName = n.node_type?.name || n.nodeType?.name || 'Nodo';
            const spaceName = n.physical_space?.name || n.physicalSpace?.name || null;
            const spaceType = (n.physical_space?.space_type || n.physicalSpace?.space_type || '').toUpperCase();
            const location = spaceName
                ? `${spaceName} ${spaceType ? '· ' + spaceType : ''}`
                : `Piso ${n.floor || 'N/A'} · Cuarto ${n.room || 'N/A'}`;
            const portStatusCls = (p) => p.status === 'up' ? 'up' : p.status === 'down' ? 'down' : 'unused';
            const buildPortData = (p, pidx) => `data-node-id="${n.id}" data-port-idx="${pidx}" data-port-name="${p.label.replace(/"/g,'&quot;')}" data-port-status="${p.status}" data-port-connected="${p.connected_to.replace(/"/g,'&quot;')}"`;

            let portsHtml = '';
            if (isAccessPoint(n)) {
                const radios = getPorts(n);
                portsHtml = `<div class="ap-panel">
                    <div class="ap-panel-title">📶 Radios / Interfaces</div>
                    ${radios.map((p, pidx) => {
                        const sig = p.status === 'up' ? '🟢' : p.status === 'down' ? '🔴' : '⚫';
                        const ssidTxt = (p.ssid || p.connected_to) ? (p.ssid || p.connected_to) : 'Sin SSID';
                        return `<div class="ap-radio ${portStatusCls(p)}" ${buildPortData(p, pidx)} title="${p.label}">
                            <span class="band">${p.label}</span>
                            <span class="ssid">${ssidTxt}</span>
                            <span class="signal">${sig}</span>
                        </div>`;
                    }).join('')}
                </div>`;
            } else if (isFirewall(n)) {
                const ifaces = getPorts(n);
                portsHtml = `<div class="fw-panel">
                    <div class="fw-panel-title">🔥 Interfaces de red</div>
                    ${ifaces.map((p, pidx) => {
                        const info = p.connected_to || (p.status === 'unused' ? 'Sin usar' : 'Activo');
                        return `<div class="fw-iface ${portStatusCls(p)}" ${buildPortData(p, pidx)} title="${p.label}">
                            <span class="iface-label">${p.label}</span>
                            <span class="iface-info">${info}</span>
                            <span class="iface-dot"></span>
                        </div>`;
                    }).join('')}
                </div>`;
            } else if (hasPortPanel(n)) {
                portsHtml = `<div class="router-ports">${getPorts(n).map((p, pidx) => `<span class="router-port ${portStatusCls(p)}" ${buildPortData(p, pidx)} title="${p.label}${p.connected_to ? ' \u2192 ' + p.connected_to : ''}">${p.label}</span>`).join('')}</div>`;
            }

            return `
                <div class="hier-card">
                    <div class="hier-kicker">${typeName}</div>
                    <div class="hier-title">${n.name}</div>
                    <div class="hier-meta">IP: ${n.ip_address || 'N/A'}</div>
                    <div class="hier-meta">${location}</div>
                    ${isDatabaseNode(n) ? `<div class="hier-meta">🗄 Motor de base de datos / almacenamiento lógico</div>` : ''}
                    ${portsHtml}
                    <div class="mt-2">
                        <a class="btn btn-sm btn-outline-primary" href="/nodos/${n.id}">Ver detalle</a>
                    </div>
                </div>
            `;
        }).join('') : '<div class="text-muted small">Sin coincidencias en hardware.</div>';
    };

    const renderSoftware = (query = '') => {
        const q = query.trim().toLowerCase();
        const filtered = explorerSystems.filter((s) => {
            if (!q) return true;
            return [s.name, s.vendor, s.project_name, s.version, s.node?.name, s.node?.ip_address]
                .filter(Boolean)
                .some((v) => String(v).toLowerCase().includes(q));
        });

        softwareGrid.innerHTML = filtered.length ? filtered.map((s) => {
            const nodeName = s.node?.name || 'Sin nodo';
            const location = `Piso ${s.node?.floor || 'N/A'} · Cuarto ${s.node?.room || 'N/A'}`;
            return `
                <div class="hier-card">
                    <div class="hier-kicker">Sistema</div>
                    <div class="hier-title">${s.name}</div>
                    <div class="hier-meta">v${s.version || 'N/A'} · ${s.vendor || 'N/A'}</div>
                    <div class="hier-meta">Reside en: ${nodeName}</div>
                    <div class="hier-meta">${location}</div>
                    <div class="mt-2">
                        ${s.node_id ? `<a class="btn btn-sm btn-outline-primary" href="/nodos/${s.node_id}">Ir al nodo</a>` : ''}
                    </div>
                </div>
            `;
        }).join('') : `
            <div class="hier-card">
                <div class="hier-kicker">Sistema</div>
                <div class="hier-title">Sin software registrado</div>
                <div class="hier-meta">No hay sistemas en esta sucursal o no coinciden con la búsqueda.</div>
                <div class="mt-2">
                    <a class="btn btn-sm btn-outline-secondary" href="/admin">Ir a Admin</a>
                </div>
            </div>
        `;
    };

    const renderSpaces = (query = '') => {
        const q = query.trim().toLowerCase();
        const grouped = {};

        explorerNodes.forEach((n) => {
            const pSpace = n.physical_space || n.physicalSpace || null;
            const floor = pSpace?.floor || n.floor || 'N/A';
            const room = pSpace?.room || n.room || 'N/A';
            const spaceType = pSpace?.space_type || 'room';
            const spaceName = pSpace?.name || `Piso ${floor} · Cuarto ${room}`;
            const key = pSpace?.id ? `space_${pSpace.id}` : `${floor}__${room}`;
            if (!grouped[key]) {
                grouped[key] = { floor, room, spaceType, spaceName, nodes: [], systems: [] };
            }
            grouped[key].nodes.push(n);
        });

        explorerSystems.forEach((s) => {
            const pSpace = s.node?.physical_space || s.node?.physicalSpace || null;
            const floor = pSpace?.floor || s.node?.floor || 'N/A';
            const room = pSpace?.room || s.node?.room || 'N/A';
            const spaceType = pSpace?.space_type || 'room';
            const spaceName = pSpace?.name || `Piso ${floor} · Cuarto ${room}`;
            const key = pSpace?.id ? `space_${pSpace.id}` : `${floor}__${room}`;
            if (!grouped[key]) {
                grouped[key] = { floor, room, spaceType, spaceName, nodes: [], systems: [] };
            }
            grouped[key].systems.push(s);
        });

        const list = Object.values(grouped).filter((g) => {
            if (!q) return true;
            return [g.floor, g.room, g.spaceName, g.spaceType, g.nodes.map((n) => n.name).join(' '), g.systems.map((s) => s.name).join(' ')]
                .some((v) => String(v).toLowerCase().includes(q));
        });

        spacesGrid.innerHTML = list.length ? list.map((g) => `
            <div class="hier-card">
                <div class="hier-kicker">Espacio físico</div>
                <div class="hier-title">${g.spaceName}</div>
                <div class="hier-meta">Tipo: ${String(g.spaceType || 'room').toUpperCase()}</div>
                <div class="hier-meta">Equipos: ${g.nodes.length}</div>
                <div class="hier-meta">Servicios/Sistemas: ${g.systems.length}</div>
            </div>
        `).join('') : '<div class="text-muted small">Sin coincidencias en espacios.</div>';
    };

    const switchPane = (paneName, syncDrill = true) => {
        hierTabs.forEach((tab) => tab.classList.toggle('active', tab.dataset.pane === paneName));
        hierPanes.forEach((pane) => pane.classList.toggle('active', pane.dataset.pane === paneName));
        if (hierPathFocus) {
            hierPathFocus.textContent = pathLabel[paneName] || '🧱 Hardware';
        }

        if (syncDrill && (paneName === 'hardware' || paneName === 'software')) {
            activatePanel(paneName, false);
        }
    };

    hierTabs.forEach((tab) => {
        tab.addEventListener('click', () => switchPane(tab.dataset.pane));
    });

    if (hierSearch) {
        hierSearch.addEventListener('input', () => {
            const query = hierSearch.value || '';
            renderHardware(query);
            renderSoftware(query);
            renderSpaces(query);
        });
    }

    renderHardware();
    renderSoftware();
    renderSpaces();

    const pathFocus = document.getElementById('drillPathFocus');
    const levels = document.querySelectorAll('#drillLevels .drill-level');
    const scenes = document.querySelectorAll('#drillShell .drill-scene');
    const mapNodes = document.querySelectorAll('[data-map-node]');
    const mapFocus = document.getElementById('drillMapFocus');
    const canvas = document.getElementById('drillCanvas');
    const camera = {
        general:  { x: -20,  y: 10,   scale: .92, map: { left: '22%', top: '24%' } },
        hardware: { x: -910, y: -20,  scale: .92, map: { left: '78%', top: '28%' } },
        software: { x: -60,  y: -520, scale: .92, map: { left: '26%', top: '74%' } },
        nodes:    { x: -950, y: -560, scale: .92, map: { left: '80%', top: '76%' } },
    };
    const levelLabels = {
        general: '📋 General',
        hardware: '🧱 Hardware',
        software: '🧩 Software',
        nodes: '🔎 Nodos'
    };

    const activatePanel = (target, syncPane = true) => {
        levels.forEach((el) => el.classList.toggle('active', el.getAttribute('data-target') === target));
        scenes.forEach((scene) => scene.classList.toggle('active', scene.getAttribute('data-scene') === target));
        mapNodes.forEach((node) => node.classList.toggle('active', node.getAttribute('data-map-node') === target));
        pathFocus.textContent = levelLabels[target] || '📋 General';

        if (syncPane && (target === 'hardware' || target === 'software')) {
            switchPane(target, false);
        }

        const setup = camera[target] || camera.general;
        canvas.style.transform = `translate(${setup.x}px, ${setup.y}px) scale(${setup.scale})`;
        if (mapFocus) {
            mapFocus.style.left = setup.map.left;
            mapFocus.style.top = setup.map.top;
        }
    };

    levels.forEach((button) => {
        button.addEventListener('click', () => activatePanel(button.getAttribute('data-target')));
    });

    const params = new URLSearchParams(window.location.search);
    if (params.get('drill') === '1') {
        document.getElementById('drillShell')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        setTimeout(() => activatePanel('general'), 80);
    }

    mapNodes.forEach((node) => {
        node.addEventListener('click', () => activatePanel(node.getAttribute('data-map-node')));
        node.style.cursor = 'pointer';
    });

    document.querySelectorAll('.js-node-metrics').forEach((button) => {
        button.addEventListener('click', async () => {
            const url = button.getAttribute('data-url');
            const panel = document.getElementById('metricsPanel');
            if (!url || !panel) {
                return;
            }

            panel.innerHTML = '<p class="text-muted small mb-0">Consultando métricas…</p>';

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const r = await response.json();
                panel.innerHTML = `
                <div class="fw-semibold mb-3" style="font-size:.88rem">${r.node_name}</div>
                <div class="metric-row">
                    <div class="metric-head"><span>CPU</span><strong>${r.cpu_percent}%</strong></div>
                    <div class="progress"><div class="progress-bar bg-primary" style="width:${r.cpu_percent}%"></div></div>
                </div>
                <div class="metric-row">
                    <div class="metric-head"><span>RAM</span><strong>${r.ram_percent}%</strong></div>
                    <div class="progress"><div class="progress-bar ${r.ram_percent > 80 ? 'bg-warning' : 'bg-success'}" style="width:${r.ram_percent}%"></div></div>
                </div>
                <div class="metric-row">
                    <div class="metric-head"><span>Disco</span><strong>${r.disk_percent}%</strong></div>
                    <div class="progress"><div class="progress-bar ${r.disk_percent > 85 ? 'bg-danger' : 'bg-info'}" style="width:${r.disk_percent}%"></div></div>
                </div>
                <div style="font-size:.72rem;color:#94a3b8">Actualizado: ${r.updated_at}</div>
            `;
            } catch (error) {
                panel.innerHTML = '<p class="text-muted small mb-0">No fue posible cargar las métricas para este nodo.</p>';
                window.itcityAlert({
                    icon: 'error',
                    title: 'Error al consultar métricas',
                    text: 'No se pudieron consultar las métricas.',
                    toast: true,
                    position: 'top-end',
                });
            }
        });
    });

    // Port / radio / interface info popup
    const portPopup = document.getElementById('portPopup');
    if (portPopup) {
        const statusLabel = { up: '🟢 Activo', down: '🔴 Inactivo', unused: '⚫ Sin uso' };
        document.addEventListener('click', (e) => {
            const portSpan = e.target.closest('[data-port-idx]');
            if (portSpan) {
                e.stopPropagation();
                const name = portSpan.dataset.portName || 'Puerto';
                const status = portSpan.dataset.portStatus || 'unused';
                const connected = portSpan.dataset.portConnected || '';
                const nodeId = portSpan.dataset.nodeId;
                document.getElementById('portPopupTitle').textContent = name;
                document.getElementById('portPopupStatus').textContent = statusLabel[status] || status;
                document.getElementById('portPopupConnected').textContent = connected ? `Conectado a: ${connected}` : 'Sin conexión registrada';
                document.getElementById('portPopupEditLink').href = nodeId ? `/admin?edit_node=${nodeId}` : '/admin';
                portPopup.style.display = '';
                const rect = portSpan.getBoundingClientRect();
                const pw = portPopup.offsetWidth || 200;
                const ph = portPopup.offsetHeight || 110;
                let left = rect.left + window.scrollX;
                let top = rect.bottom + window.scrollY + 6;
                if (left + pw > window.innerWidth - 8) left = window.innerWidth - pw - 8;
                if (top + ph > window.innerHeight + window.scrollY - 8) top = rect.top + window.scrollY - ph - 6;
                portPopup.style.left = left + 'px';
                portPopup.style.top = top + 'px';
                return;
            }
            if (!e.target.closest('#portPopup')) portPopup.style.display = 'none';
        });
        document.getElementById('portPopupClose').addEventListener('click', () => { portPopup.style.display = 'none'; });
    }
});
</script>
@endpush
