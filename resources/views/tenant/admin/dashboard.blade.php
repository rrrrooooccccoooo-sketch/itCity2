@extends('tenant.layouts.app')

@section('title', 'Admin')
@section('page_title', 'Administración del Tenant')

@php
    $editorOnlyMode = (bool) ($editorOnlyMode ?? false);
    $directFloorPlanMode = $editorOnlyMode || request()->filled('floor_plan');
@endphp

@section('topbar_actions')
    @if ($editorOnlyMode)
    @elseif (!request()->filled('floor_plan'))
        <a href="{{ url('/admin/panel-admin-1') }}" class="btn btn-sm {{ $panelVariant === 1 ? 'btn-primary' : 'btn-outline-primary' }}">Panel Admin 1</a>
        <a href="{{ url('/admin/panel-admin-2') }}" class="btn btn-sm {{ $panelVariant === 2 ? 'btn-primary' : 'btn-outline-primary' }}">Panel Admin 2</a>
        <a href="{{ url('/admin/panel-admin-3') }}" class="btn btn-sm {{ $panelVariant === 3 ? 'btn-primary' : 'btn-outline-primary' }}">Panel Admin 3</a>
        <a href="{{ url('/red/memoria-mnemotecnica/vista') }}" class="btn btn-sm btn-outline-primary">Memoria mnemotécnica</a>
        <a href="{{ url('/red/memoria-mnemotecnica') }}" target="_blank" class="btn btn-sm btn-outline-secondary">JSON</a>
        <a href="{{ url('/') }}" class="btn btn-sm btn-outline-secondary">Volver a ciudad</a>
    @else
        <a href="{{ url('/admin/panel-admin-1#section-floor-plans') }}" class="btn btn-sm btn-outline-primary">Volver a panel actual</a>
    @endif
@endsection

@push('styles')
<style>
    .code-box { font-family: Consolas, monospace; font-size: .85rem; white-space: pre-wrap; }
    .sticky-form { position: sticky; top: calc(58px + 16px); }
    .mini-table td, .mini-table th { vertical-align: middle; font-size: .88rem; }
    .ops-kpi .kpi-value { font-size: 1.35rem; font-weight: 700; line-height: 1.1; }
    .ops-kpi .kpi-label { font-size: .78rem; color: #64748b; text-transform: uppercase; letter-spacing: .05em; }
    .crud-nav-link { text-decoration: none; font-size: .85rem; font-weight: 600; }
    .crud-nav-link.priority { border-color:#1d4ed8; color:#1d4ed8; background:#eff6ff; }
    .crud-nav-link.active { background:#2563eb; color:#fff !important; border-color:#2563eb; }
    .crud-block-title { font-size: .88rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #334155; }
    .crud-block-subtitle { font-size: .8rem; color: #64748b; }
    .crud-nav-sticky { position: sticky; top: calc(58px + 8px); z-index: 90; }
    [id^="crud-"] { scroll-margin-top: calc(58px + 88px); }
    .node-type-presets { display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:1rem; }
    .node-type-preset { border:1px solid #cbd5e1; background:#fff; color:#334155; border-radius:999px; padding:.35rem .7rem; font-size:.78rem; font-weight:700; }
    .node-type-preset:hover { border-color:#2563eb; color:#2563eb; }
    .node-type-preview { border:1px solid #dbe4f0; border-radius:16px; padding:1rem; background:linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%); }
    .node-type-preview-canvas { display:flex; align-items:center; gap:1rem; }
    .node-type-preview-shape { width:72px; height:72px; display:flex; align-items:center; justify-content:center; position:relative; color:#fff; font-size:.9rem; font-weight:800; letter-spacing:.04em; user-select:none; box-shadow:0 10px 24px rgba(15,23,42,.12); }
    .node-type-preview-shape span { position:relative; z-index:2; }
    .node-type-preview-shape.variant-default { background:#1d4ed8; border-radius:999px; }
    .node-type-preview-shape.variant-router { background:#b45309; clip-path:polygon(25% 6%, 75% 6%, 100% 50%, 75% 94%, 25% 94%, 0% 50%); }
    .node-type-preview-shape.variant-switch { background:#6d28d9; border-radius:12px; width:84px; height:50px; }
    .node-type-preview-shape.variant-switch::before { content:''; position:absolute; inset:22px 10px 18px 10px; border-top:4px dotted rgba(255,255,255,.6); }
    .node-type-preview-shape.variant-firewall { background:#b91c1c; clip-path:polygon(50% 0%, 92% 18%, 92% 62%, 50% 100%, 8% 62%, 8% 18%); }
    .node-type-preview-shape.variant-access-point { background:#4338ca; border-radius:999px; }
    .node-type-preview-shape.variant-vpn-gateway { background:#7c3aed; border-radius:10px; transform:rotate(45deg); }
    .node-type-preview-shape.variant-vpn-gateway span { transform:rotate(-45deg); }
    .node-type-preview-shape.variant-server { background:#0e7490; border-radius:10px; }
    .node-type-preview-shape.variant-database { background:#0f766e; border-radius:18px; }
    .node-type-preview-shape.variant-database::before, .node-type-preview-shape.variant-database::after { content:''; position:absolute; left:10px; right:10px; height:10px; border:1.5px solid rgba(255,255,255,.4); border-radius:999px / 60%; }
    .node-type-preview-shape.variant-database::before { top:12px; }
    .node-type-preview-shape.variant-database::after { bottom:12px; }
    .node-type-preview-shape.variant-load-balancer { background:#0284c7; border-radius:14px; width:84px; height:54px; }
    .node-type-preview-shape.variant-load-balancer::before { content:'⇄'; position:absolute; font-size:1.35rem; opacity:.28; }
    .node-type-preview-shape.variant-pbx { background:#047857; border-radius:18px; }
    .node-type-preview-shape.variant-pbx::before { content:''; position:absolute; inset:16px; border:2px dashed rgba(255,255,255,.28); border-radius:12px; }
    .node-type-preview-shape.variant-ip-camera { background:#475569; border-radius:999px; }
    .node-type-preview-shape.variant-ip-camera::before { content:''; position:absolute; width:14px; height:14px; border-radius:999px; background:rgba(255,255,255,.22); box-shadow:0 0 0 5px rgba(255,255,255,.08); }
    .node-type-preview-shape.variant-printer { background:#334155; border-radius:10px; width:80px; height:56px; }
    .node-type-preview-shape.variant-printer::before { content:''; position:absolute; top:-8px; width:36px; height:18px; background:#e2e8f0; border-radius:4px 4px 0 0; }
    .node-type-preview-shape.variant-storage { background:#0f766e; border-radius:10px; }
    .node-type-preview-meta { min-width:0; }
    .node-type-preview-name { font-weight:800; color:#0f172a; }
    .node-type-preview-slug { font-family:Consolas, monospace; font-size:.78rem; color:#475569; }
    .node-type-preview-helper { font-size:.78rem; color:#64748b; margin-top:.25rem; }
    .asset-status-badge { border: 1px solid transparent; font-weight: 700; letter-spacing: .01em; }
    .asset-status-badge.in_use { background: #dcfce7; border-color: #86efac; color: #166534; }
    .asset-status-badge.stock { background: #dbeafe; border-color: #93c5fd; color: #1d4ed8; }
    .asset-status-badge.repair { background: #fef3c7; border-color: #fcd34d; color: #b45309; }
    .asset-status-badge.retired { background: #e5e7eb; border-color: #cbd5e1; color: #475569; }
    .asset-status-badge.default { background: #f8fafc; border-color: #cbd5e1; color: #334155; }
    .monitor-pill { display:inline-flex; align-items:center; padding:.2rem .5rem; border-radius:999px; font-size:.72rem; font-weight:700; border:1px solid transparent; }
    .monitor-pill.online { background:#dcfce7; color:#166534; border-color:#86efac; }
    .monitor-pill.offline { background:#e2e8f0; color:#334155; border-color:#cbd5e1; }
    .monitor-pill.critical { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }
    .relation-weight-badge { display:inline-flex; align-items:center; gap:.35rem; padding:.24rem .6rem; border-radius:999px; font-size:.72rem; font-weight:700; border:1px solid transparent; white-space:nowrap; }
    .relation-weight-badge.preferred { background:#dcfce7; color:#166534; border-color:#86efac; }
    .relation-weight-badge.normal { background:#dbeafe; color:#1d4ed8; border-color:#93c5fd; }
    .relation-weight-badge.backup { background:#fef3c7; color:#b45309; border-color:#fcd34d; }
    .relation-weight-badge.auto { background:#e2e8f0; color:#334155; border-color:#cbd5e1; }
    .relation-weight-scale { display:flex; flex-wrap:wrap; gap:.45rem; margin-top:.55rem; }
    /* Port visualizer */
    .pv-device { background:#0f172a; border:1.5px solid #334155; border-radius:6px; padding:8px 10px; user-select:none; }
    .pv-device-label { font-size:.6rem; color:#94a3b8; letter-spacing:.1em; text-transform:uppercase; margin-bottom:6px; font-family:monospace; }
    .pv-ports-grid { display:flex; flex-wrap:wrap; gap:4px; align-items:center; }
    .pv-port { width:36px; height:24px; border-radius:3px; border:2px solid #1e293b; background:#1e293b; color:#475569; font-size:.58rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:border-color .1s,transform .1s; padding:0; line-height:1; }
    .pv-port:hover { transform:scale(1.12); border-color:#60a5fa; }
    .pv-port.up   { background:#166534; border-color:#22c55e; color:#dcfce7; }
    .pv-port.down { background:#7f1d1d; border-color:#ef4444; color:#fee2e2; }
    .pv-port.sel  { border-color:#facc15 !important; box-shadow:0 0 0 2px rgba(253,224,71,.45); }
    .port-count-btn.active { background:#2563eb !important; border-color:#2563eb !important; color:#fff !important; }
    #portEditor { border-left:3px solid #2563eb; }
    #floorPlanEditorModal {
        --fp-editor-height: min(92vh, 980px);
        --fp-editor-chrome: 132px;
    }
    #floorPlanEditorModal .modal-dialog {
        height: var(--fp-editor-height);
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
    #floorPlanEditorModal .floor-plan-editor-content {
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    #floorPlanEditorModal .floor-plan-editor-body {
        overflow: hidden !important;
        flex: 1 1 auto;
        min-height: 0;
    }
    #floorPlanEditorModal .floor-plan-editor-layout {
        height: 100%;
        min-height: 0;
        align-items: stretch;
    }
    #floorPlanEditorModal .floor-plan-editor-sidebar-col,
    #floorPlanEditorModal .floor-plan-editor-canvas-col {
        display:flex;
        min-height: 0;
    }
    #floorPlanEditorModal .floor-plan-editor-sidebar {
        height: calc(var(--fp-editor-height) - var(--fp-editor-chrome));
        max-height: calc(var(--fp-editor-height) - var(--fp-editor-chrome));
        min-height: calc(var(--fp-editor-height) - var(--fp-editor-chrome));
        overflow-y: auto;
        overflow-x: hidden;
    }
    #floorPlanEditorModal .floor-plan-editor-stage {
        width: 100%;
        position: sticky;
        top: 0;
        align-self: flex-start;
    }
    #floorPlanEditorModal .floor-plan-editor-save-fab {
        position: absolute;
        right: 18px;
        bottom: 18px;
        z-index: 20;
        border-radius: 999px;
        padding: .7rem 1rem;
        box-shadow: 0 14px 28px rgba(15, 23, 42, .28);
    }
    #floorPlanEditorModal .floor-plan-editor-signal-probe {
        position: absolute;
        z-index: 21;
        min-width: 148px;
        max-width: 220px;
        padding: .55rem .7rem;
        border-radius: 12px;
        background: rgba(15, 23, 42, .92);
        color: #e2e8f0;
        box-shadow: 0 16px 32px rgba(15, 23, 42, .28);
        pointer-events: none;
        transform: translate(10px, -10px);
        display: none;
    }
    #floorPlanEditorModal .floor-plan-editor-signal-probe strong {
        display: block;
        color: #f8fafc;
        font-size: .82rem;
        margin-bottom: .15rem;
    }
    #floorPlanEditorModal .floor-plan-editor-signal-probe small {
        display: block;
        color: #94a3b8;
        line-height: 1.25;
    }
    @media (max-width: 991.98px) {
        #floorPlanEditorModal .modal-dialog { height: auto; }
        #floorPlanEditorModal .floor-plan-editor-content { height: auto; }
        #floorPlanEditorModal .floor-plan-editor-body { overflow: auto !important; }
        #floorPlanEditorModal .floor-plan-editor-sidebar { overflow: visible; }
        #floorPlanEditorModal .floor-plan-editor-stage { position: static; }
        #floorPlanEditorModal .floor-plan-editor-save-fab {
            right: 12px;
            bottom: 12px;
            padding: .6rem .9rem;
        }
    }

    .admin-panel-shell {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
        padding: .9rem 1rem;
    }
    .admin-panel-shell .title { font-size: .82rem; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: .05em; }
    .admin-panel-shell .subtitle { font-size: .8rem; color: #64748b; }
    .admin-panel-links { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .6rem; }

    .admin-panel.admin-panel-1 .section-card,
    .admin-panel.admin-panel-1 .ic-card {
        border-radius: 18px !important;
        box-shadow: 0 10px 28px rgba(37, 99, 235, .08), 0 2px 8px rgba(15, 23, 42, .04) !important;
        border: 1px solid #dbeafe !important;
    }
    .admin-panel.admin-panel-1 .card-header {
        background: linear-gradient(90deg, #eff6ff 0%, #f8fafc 100%) !important;
        border-bottom: 1px solid #dbeafe !important;
    }

    .admin-panel.admin-panel-2 {
        background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
        border-radius: 20px;
        padding: 1rem;
    }
    .admin-panel.admin-panel-2 .ic-stat,
    .admin-panel.admin-panel-2 .section-card,
    .admin-panel.admin-panel-2 .ic-card,
    .admin-panel.admin-panel-2 .card,
    .admin-panel.admin-panel-2 .table,
    .admin-panel.admin-panel-2 .admin-panel-shell {
        background: #111827 !important;
        color: #e2e8f0 !important;
        border-color: #334155 !important;
    }
    .admin-panel.admin-panel-2 .card-header,
    .admin-panel.admin-panel-2 .card-footer {
        background: #0b1220 !important;
        color: #e2e8f0 !important;
        border-color: #334155 !important;
    }
    .admin-panel.admin-panel-2 .st-label,
    .admin-panel.admin-panel-2 .kpi-label,
    .admin-panel.admin-panel-2 .crud-block-subtitle,
    .admin-panel.admin-panel-2 .subtitle,
    .admin-panel.admin-panel-2 .form-text,
    .admin-panel.admin-panel-2 .text-muted,
    .admin-panel.admin-panel-2 small,
    .admin-panel.admin-panel-2 .table,
    .admin-panel.admin-panel-2 .table th,
    .admin-panel.admin-panel-2 .table td,
    .admin-panel.admin-panel-2 .title {
        color: #cbd5e1 !important;
    }
    .admin-panel.admin-panel-2 .form-control,
    .admin-panel.admin-panel-2 .form-select,
    .admin-panel.admin-panel-2 textarea {
        background: #0f172a;
        border-color: #334155;
        color: #e2e8f0;
    }
    .admin-panel.admin-panel-2 .form-control::placeholder,
    .admin-panel.admin-panel-2 textarea::placeholder {
        color: #64748b;
    }

    .admin-panel.admin-panel-3 .row.g-4.mb-4 {
        margin-bottom: .75rem !important;
    }
    .admin-panel.admin-panel-3 .ic-stat {
        border-radius: 10px;
        padding: 14px 16px;
        gap: 10px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    }
    .admin-panel.admin-panel-3 .ic-stat .st-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        font-size: .9rem;
    }
    .admin-panel.admin-panel-3 .st-label { font-size: .72rem; text-transform: uppercase; color: #64748b; letter-spacing: .04em; }
    .admin-panel.admin-panel-3 .st-value { font-size: 1.1rem; }
    .admin-panel.admin-panel-3 .section-card,
    .admin-panel.admin-panel-3 .ic-card,
    .admin-panel.admin-panel-3 .card {
        border-radius: 10px !important;
    }
    .admin-panel.admin-panel-3 .card-header {
        padding-top: .55rem !important;
        padding-bottom: .55rem !important;
        font-size: .84rem !important;
    }
    .admin-panel.admin-panel-3 .card-body,
    .admin-panel.admin-panel-3 .card-footer {
        padding-top: .7rem !important;
        padding-bottom: .7rem !important;
    }
    .floor-plan-direct-mode:not(.editor-only-mode) #adminLegacyContent {
        display: none;
    }
    body:has(.floor-plan-direct-mode) #ic-sidebar,
    body:has(.floor-plan-direct-mode) #ic-topbar {
        display: none !important;
    }
    body:has(.floor-plan-direct-mode) #ic-main {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    body:has(.floor-plan-direct-mode) #ic-content {
        padding: 0 !important;
    }
    body:has(.floor-plan-direct-mode),
    body:has(.floor-plan-direct-mode) #ic-main,
    body:has(.floor-plan-direct-mode) #ic-content,
    body:has(.floor-plan-direct-mode) #preziOverlay {
        background: #fff !important;
    }
    .admin-panel.editor-only-mode {
        min-height: 100vh;
        padding: 0;
    }
</style>
@endpush

@section('content')
@php
    $currentUser = auth()->user();
    $isAdminRole = $currentUser && (($currentUser->role ?? null) === 'admin');

    $crudNavItems = [
        ['anchor' => 'crud-branches', 'label' => 'Sedes'],
        ['anchor' => 'crud-spaces', 'label' => 'Espacios físicos'],
        ['anchor' => 'crud-nodes', 'label' => 'Nodos'],
        ['anchor' => 'crud-node-types', 'label' => 'Tipos de nodo'],
        ['anchor' => 'crud-relations', 'label' => 'Relaciones'],
        ['anchor' => 'crud-monitoring', 'label' => 'Monitoreo'],
        ['anchor' => 'crud-floor-plans', 'label' => 'Planos / Heatmap'],
        ['anchor' => 'crud-assets', 'label' => 'Inventario TI'],
        ['anchor' => 'crud-equipment-brands', 'label' => 'Marcas'],
        ['anchor' => 'crud-equipment-models', 'label' => 'Modelos de equipo'],
        ['anchor' => 'crud-software', 'label' => 'Sistemas'],
    ];

    $crudAdminPriority = [
        'crud-branches',
        'crud-spaces',
        'crud-nodes',
        'crud-node-types',
        'crud-relations',
        'crud-monitoring',
        'crud-floor-plans',
        'crud-assets',
        'crud-equipment-brands',
        'crud-equipment-models',
        'crud-software',
    ];

    $crudOperationPriority = [
        'crud-monitoring',
        'crud-assets',
        'crud-nodes',
        'crud-branches',
        'crud-spaces',
        'crud-floor-plans',
        'crud-relations',
        'crud-software',
        'crud-node-types',
        'crud-equipment-brands',
        'crud-equipment-models',
    ];

    $priorityMap = array_flip($isAdminRole ? $crudAdminPriority : $crudOperationPriority);

    usort($crudNavItems, static function (array $a, array $b) use ($priorityMap): int {
        $left = $priorityMap[$a['anchor']] ?? 999;
        $right = $priorityMap[$b['anchor']] ?? 999;
        return $left <=> $right;
    });

    $crudTopPriorityAnchors = array_slice($isAdminRole ? $crudAdminPriority : $crudOperationPriority, 0, 3);
@endphp
<div class="admin-panel admin-panel-{{ $panelVariant }} {{ $directFloorPlanMode ? 'floor-plan-direct-mode' : '' }} {{ $editorOnlyMode ? 'editor-only-mode' : '' }}">

    @if (session('status'))
        <div data-swal-flash data-swal-icon="success" data-swal-title="Operación completada" data-swal-text="{{ session('status') }}" data-swal-toast="1" data-swal-position="top-end" data-swal-timer="2600"></div>
    @endif

    @if ($errors->any())
        <div
            data-swal-flash
            data-swal-icon="error"
            data-swal-title="Hay errores de validación"
            data-swal-html="{!! collect($errors->all())->map(fn($e) => '• ' . e($e))->implode('<br>') !!}"
            data-swal-toast="0"
            data-swal-show-confirm-button="1"
            data-swal-confirm-button-text="Entendido"
        ></div>
    @endif

    <div id="adminLegacyContent">
    @unless($editorOnlyMode)
    <div class="admin-panel-shell mb-3">
        <div class="title">Opciones de presentación</div>
        <div class="subtitle">Mismos datos, lógica y formularios; solo cambia el diseño del Panel Admin.</div>
        <div class="admin-panel-links">
            <a href="{{ url('/admin/panel-admin-1') }}" class="btn btn-sm {{ $panelVariant === 1 ? 'btn-primary' : 'btn-outline-primary' }}">Panel Admin 1 · Ejecutivo</a>
            <a href="{{ url('/admin/panel-admin-2') }}" class="btn btn-sm {{ $panelVariant === 2 ? 'btn-primary' : 'btn-outline-primary' }}">Panel Admin 2 · Técnico oscuro</a>
            <a href="{{ url('/admin/panel-admin-3') }}" class="btn btn-sm {{ $panelVariant === 3 ? 'btn-primary' : 'btn-outline-primary' }}">Panel Admin 3 · Compacto</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="ic-stat">
                <div class="st-icon" style="background:#eff6ff">🏗️</div>
                <div>
                    <div class="st-label">Sedes</div>
                    <div class="st-value">{{ $branches->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="ic-stat">
                <div class="st-icon" style="background:#f0fdf4">💻</div>
                <div>
                    <div class="st-label">Tipos de nodo</div>
                    <div class="st-value">{{ $nodeTypes->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="ic-stat">
                <div class="st-icon" style="background:#fefce8">📡</div>
                <div>
                    <div class="st-label">Nodos</div>
                    <div class="st-value">{{ $nodes->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="ic-stat">
                <div class="st-icon" style="background:#fdf4ff">📦</div>
                <div>
                    <div class="st-label">Sistemas</div>
                    <div class="st-value">{{ $systems->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card section-card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Resumen operativo</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnRefreshOpsSummary">Actualizar</button>
        </div>
        <div class="card-body">
            <div class="row g-3 ops-kpi">
                <div class="col-6 col-lg-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="kpi-label">Disponibilidad</div>
                        <div class="kpi-value" id="opsAvailability">—</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="kpi-label">Latencia prom.</div>
                        <div class="kpi-value" id="opsLatency">—</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="kpi-label">Nodos monitoreados</div>
                        <div class="kpi-value" id="opsMonitored">—</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="kpi-label">Nodos activos</div>
                        <div class="kpi-value" id="opsActiveNodes">—</div>
                    </div>
                </div>
            </div>
            <small class="text-muted d-block mt-2" id="opsSummaryMeta">Consultando métricas...</small>
        </div>
    </div>

    <div class="card section-card mb-4 crud-nav-sticky">
        <div class="card-header bg-white fw-semibold">Navegación por módulos CRUD</div>
        <div class="card-body d-flex flex-wrap gap-2">
            @foreach ($crudNavItems as $navItem)
                @php
                    $isPriorityItem = in_array($navItem['anchor'], $crudTopPriorityAnchors, true);
                    $buttonClass = $isPriorityItem ? 'btn-primary priority' : 'btn-outline-secondary';
                @endphp
                <a href="#{{ $navItem['anchor'] }}" class="btn btn-sm {{ $buttonClass }} crud-nav-link">{{ $navItem['label'] }}</a>
            @endforeach
        </div>
    </div>

    <div id="crud-monitoring" class="mb-2">
        <div class="crud-block-title">Monitoreo en vivo · Agente</div>
        <div class="crud-block-subtitle">El agente reporta inventario/performance por heartbeat y actualiza este panel automáticamente.</div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card section-card sticky-form">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    <span>Despliegue del agente</span>
                    <span class="badge text-bg-secondary">Windows</span>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Genera un instalador <code>.ps1</code> pre-configurado para este tenant. Flujo rápido: selecciona sede, descarga y ejecuta como Administrador.</p>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sede del equipo</label>
                        <select id="agentInstallerBranch" class="form-select form-select-sm">
                            <option value="">Sin especificar (usar ID 1)</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Asset Tag (opcional)</label>
                        <input type="text" id="agentInstallerAssetTag" class="form-control form-control-sm" placeholder="Ej. PC-CONT-012 (usa hostname si se deja vacío)">
                    </div>
                    <details class="mb-3">
                        <summary class="small fw-semibold text-muted">Opciones avanzadas (opcional)</summary>
                        <div class="row g-2 mt-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Intervalo heartbeat (seg)</label>
                                <input type="number" id="agentInstallerInterval" class="form-control form-control-sm" value="60" min="30" max="3600">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Inventario (horas)</label>
                                <input type="number" id="agentInstallerInventoryHours" class="form-control form-control-sm" value="12" min="1" max="168">
                            </div>
                        </div>
                    </details>
                    <a id="btnDownloadAgentInstaller" href="/admin/monitoring/agent-installer" class="btn btn-primary btn-sm w-100 mb-3" download>
                        ⬇ Descargar instalador .ps1
                    </a>
                    <a href="/admin/monitoring/agent-installer-zip" class="btn btn-outline-primary btn-sm w-100 mb-3" download>
                        ⬇ Descargar instalador .zip
                    </a>
                    <a href="/admin/monitoring/agent-installer-exe" class="btn btn-outline-primary btn-sm w-100 mb-3" download>
                        ⬇ Descargar instalador .exe
                    </a>
                    <details class="mb-3">
                        <summary class="small fw-semibold text-muted">Herramientas avanzadas</summary>
                        <div class="mt-2">
                            <a id="btnDownloadSnmpTargetsTemplate" href="{{ url('/admin/monitoring/snmp-targets-template') }}" class="btn btn-outline-secondary btn-sm w-100" download>
                                ⬇ Descargar plantilla snmp_targets.json
                            </a>
                        </div>
                    </details>
                    <div class="small text-muted mb-2 fw-semibold">Instrucciones rápidas</div>
                    <ol class="small text-muted ps-3 mb-3">
                        <li>Selecciona la sede y descarga el instalador.</li>
                        <li>Copia a la máquina destino.</li>
                        <li>Ejecuta como Administrador:<br>
                            <code>powershell -ExecutionPolicy Bypass -File .\install-agent-*.ps1</code>
                        </li>
                        <li>El script instala el agente en <code>C:\ProgramData\ITCity\Agent\</code>, protege la llave y registra la tarea programada.</li>
                    </ol>
                    <div class="small text-muted mb-1 fw-semibold">Endpoint de ingestión</div>
                    <div class="code-box border rounded p-2 mb-2">{{ $agentIngestUrl }}</div>
                    <div class="small text-muted">Configura <strong>TENANT_AGENT_INGEST_KEY</strong> en el entorno si aún no está activo.</div>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card section-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Estado de agentes</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnRefreshMonitoring">Actualizar</button>
                </div>
                <div class="card-body pb-2">
                    <div class="row g-3 mb-2 ops-kpi">
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="kpi-label">Activos monitoreados</div>
                                <div class="kpi-value" id="monitorTrackedAssets">{{ $monitoringSummary['tracked_assets'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="kpi-label">En línea (&lt;3 min)</div>
                                <div class="kpi-value" id="monitorOnlineAssets">{{ $monitoringSummary['online_assets'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="kpi-label">Alertas críticas</div>
                                <div class="kpi-value" id="monitorCriticalAssets">{{ $monitoringSummary['critical_assets'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mini-table mb-0">
                        <thead>
                            <tr>
                                <th>Activo</th>
                                <th>Sede</th>
                                <th>Nodo / Resolución</th>
                                <th>Performance</th>
                                <th>Último heartbeat</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="monitoringRows">
                            @forelse ($monitoringAssets as $asset)
                                @php
                                    $isOnline = $asset->last_seen_at && $asset->last_seen_at->gte(now()->subMinutes($monitoringOnlineWindowMinutes ?? 10));
                                    $isCritical = ($asset->last_cpu_usage_percent ?? 0) >= 90 || ($asset->last_memory_usage_percent ?? 0) >= 90 || ($asset->last_disk_usage_percent ?? 0) >= 90;
                                    $resolutionSource = data_get($asset->details, 'agent.node_resolution.source') ?: ($asset->node_id ? 'manual' : 'none');
                                    $resolutionLabel = match ($resolutionSource) {
                                        'hostname_unique' => 'hostname',
                                        'ip_unique' => 'ip',
                                        'mac_unique' => 'mac',
                                        'provided_node_id' => 'node_id',
                                        'asset_existing_node' => 'manual',
                                        'manual' => 'manual',
                                        default => 'sin resolver',
                                    };
                                @endphp
                                <tr class="monitor-asset-row" data-asset-id="{{ $asset->id }}" style="cursor:pointer;" title="Ver detalle en tiempo real" onclick="window.itcityOpenAssetDetail && window.itcityOpenAssetDetail(this.dataset.assetId)">
                                    <td>
                                        <div class="fw-semibold">{{ $asset->asset_tag ?: ($asset->hostname ?: ('Activo #' . $asset->id)) }}</div>
                                        <div class="text-muted small">{{ $asset->equipmentTypeLabel() }} @if($asset->hostname) · {{ $asset->hostname }} @endif</div>
                                    </td>
                                    <td>{{ optional($asset->branch)->name ?: 'N/A' }}</td>
                                    <td>
                                        <div>{{ optional($asset->node)->name ?: 'Sin nodo' }}</div>
                                        <div class="text-muted small">vía {{ $resolutionLabel }}</div>
                                    </td>
                                    <td>
                                        CPU {{ $asset->last_cpu_usage_percent !== null ? number_format((float) $asset->last_cpu_usage_percent, 1) . '%' : 'N/A' }} ·
                                        RAM {{ $asset->last_memory_usage_percent !== null ? number_format((float) $asset->last_memory_usage_percent, 1) . '%' : 'N/A' }} ·
                                        Disco {{ $asset->last_disk_usage_percent !== null ? number_format((float) $asset->last_disk_usage_percent, 1) . '%' : 'N/A' }}
                                    </td>
                                    <td>{{ $asset->last_seen_at ? $asset->last_seen_at->diffForHumans() : 'N/A' }}</td>
                                    <td>
                                        <span class="monitor-pill {{ $isOnline ? 'online' : 'offline' }}">{{ $isOnline ? 'Online' : 'Offline' }}</span>
                                        @if($isCritical)
                                            <span class="monitor-pill critical">Crítico</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Sin heartbeats todavía. Inicia el agente y envía el primer reporte.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Modal detalle de activo en tiempo real ─────────────────────────────── --}}
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
                    <button type="button" class="btn btn-sm btn-outline-primary" id="adModalRefreshBtn">Actualizar ahora</button>
                </div>
            </div>
        </div>
    </div>

    <div id="crud-floor-plans" class="mb-2">
        <div class="crud-block-title">CRUD · Planos / Mapa de calor WiFi</div>
        <div class="crud-block-subtitle">Carga planos por sede/piso (PNG, PDF, DWG) y marca Access Points por capas para mapa de calor.</div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card section-card sticky-form">
                <div class="card-header bg-white fw-semibold">Cargar plano</div>
                <div class="card-body">
                    <form method="POST" action="{{ url('/admin/floor-plans') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Sede</label>
                            <select name="floor_plan_branch_id" class="form-select" required>
                            <small class="text-muted d-block mt-2">Para AP puedes guardar defaults RF, por ejemplo: {"rf":{"radius_meters":12,"radiation_pattern":"omni-donut","mount_orientation":"ceiling","mount_height_m":2.6,"azimuth_deg":0,"tilt_deg":0}}</small>
                                <option value="">Selecciona...</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Piso / Espacio físico (opcional)</label>
                            <select name="floor_plan_space_id" id="floorPlanSpaceSelect" class="form-select">
                                <option value="">Sin asociar</option>
                                @foreach ($spaces as $space)
                                    <option value="{{ $space->id }}" data-branch-id="{{ $space->branch_id }}">{{ $space->name }} @if($space->floor) · Piso {{ $space->floor }} @endif · {{ optional($space->branch)->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre del plano</label>
                            <input type="text" name="floor_plan_name" class="form-control" placeholder="Ej. Corporativo Piso 3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Modo de plano</label>
                            <select name="floor_plan_mode" id="floorPlanModeSelect" class="form-select">
                                <option value="upload">Subir archivo</option>
                                <option value="blank">Diseñar plano en blanco</option>
                            </select>
                        </div>
                        <div class="mb-3" id="floorPlanFileWrap">
                            <label class="form-label">Archivo</label>
                            <input type="file" name="floor_plan_file" id="floorPlanFileInput" class="form-control" accept=".png,.pdf,.dwg,.dxf,.svg">
                            <small class="text-muted">Para editar puntos y muros en pantalla, el formato recomendado es PNG/SVG.</small>
                        </div>
                        <div class="row g-2 mb-3" id="floorPlanBlankSizeWrap" style="display:none;">
                            <div class="col-6">
                                <label class="form-label">Ancho (px)</label>
                                <input type="number" name="floor_plan_blank_width" class="form-control" min="400" max="6000" value="1400">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Alto (px)</label>
                                <input type="number" name="floor_plan_blank_height" class="form-control" min="300" max="4000" value="900">
                            </div>
                            <small class="text-muted">Se creará una plantilla SVG cuadriculada para diseñar el piso y muros dentro del sistema.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Subir plano</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card section-card">
                <div class="card-header bg-white fw-semibold">Planos cargados</div>
                <div class="table-responsive">
                    <table class="table table-hover mini-table mb-0">
                        <thead>
                            <tr>
                                <th>Plano</th>
                                <th>Sede / Piso</th>
                                <th>Formato</th>
                                <th>Puntos AP</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($floorPlans as $plan)
                                @php
                                    $overlay = is_array($plan->overlay_points) ? $plan->overlay_points : [];
                                    $planPoints = isset($overlay['points']) && is_array($overlay['points'])
                                        ? $overlay['points']
                                        : (array_is_list($overlay) ? $overlay : []);
                                    $planPointsCount = count($planPoints);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $plan->name }}</div>
                                        <div class="text-muted small">{{ data_get($plan->meta, 'original_name') ?: 'sin nombre original' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ optional($plan->branch)->name ?: 'N/A' }}</div>
                                        <div class="text-muted small">{{ optional($plan->physicalSpace)->name ?: 'Sin piso' }}</div>
                                    </td>
                                    <td><span class="badge text-bg-light border text-dark">{{ strtoupper((string) $plan->file_type) }}</span></td>
                                    <td>{{ $planPointsCount }}</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary btnOpenFloorPlanEditor" data-floor-plan-id="{{ $plan->id }}">Editar mapa</button>
                                        <form method="POST" action="{{ url('/admin/floor-plans/' . $plan->id) }}" class="d-inline" data-confirm="¿Eliminar plano?" data-confirm-title="Eliminar plano" data-confirm-icon="warning" data-confirm-button-text="Sí, eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No hay planos cargados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @endunless
    </div>

    @include('tenant.admin.partials.floor-plan-editor-modal')

    <div id="crud-spaces" class="mb-2">
        <div class="crud-block-title">CRUD · Espacios físicos</div>
        <div class="crud-block-subtitle">Formulario de alta/edición (izquierda) y listado para edición/eliminación (derecha).</div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card section-card sticky-form">
                <div class="card-header bg-white fw-semibold">{{ $editingSpace->exists ? 'Editar espacio físico' : 'Nuevo espacio físico' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ $editingSpace->exists ? url('/admin/spaces/' . $editingSpace->id) : url('/admin/spaces') }}">
                        @csrf
                        @if ($editingSpace->exists)
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Sede</label>
                            <select name="space_branch_id" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) old('space_branch_id', $editingSpace->branch_id) === (string) $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre del espacio</label>
                            <input type="text" name="space_name" class="form-control" value="{{ old('space_name', $editingSpace->name) }}" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Código</label>
                                <input type="text" name="space_code" class="form-control" value="{{ old('space_code', $editingSpace->code) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo</label>
                                <select name="space_type" class="form-select" required>
                                    @php $spaceTypeOld = old('space_type', $editingSpace->space_type ?? 'room'); @endphp
                                    <option value="site" @selected($spaceTypeOld === 'site')>Site</option>
                                    <option value="idf" @selected($spaceTypeOld === 'idf')>IDF</option>
                                    <option value="room" @selected($spaceTypeOld === 'room')>Cuarto</option>
                                    <option value="zone" @selected($spaceTypeOld === 'zone')>Zona</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Piso</label>
                                <input type="text" name="space_floor" class="form-control" value="{{ old('space_floor', $editingSpace->floor) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cuarto</label>
                                <input type="text" name="space_room" class="form-control" value="{{ old('space_room', $editingSpace->room) }}">
                            </div>
                        </div>
                        <div class="mt-3 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="space_description" rows="3" class="form-control">{{ old('space_description', $editingSpace->description) }}</textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ $editingSpace->exists ? 'Actualizar' : 'Crear' }}</button>
                            @if ($editingSpace->exists)
                                <a href="{{ url('/admin') }}" class="btn btn-outline-secondary">Cancelar</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card section-card">
                <div class="card-header bg-white fw-semibold">Espacios físicos registrados</div>
                <div class="table-responsive">
                    <table class="table table-hover mini-table mb-0">
                        <thead>
                            <tr>
                                <th>Espacio</th>
                                <th>Sede</th>
                                <th>Tipo</th>
                                <th>Ubicación</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($spaces as $space)
                                <tr>
                                    <td>{{ $space->name }} @if($space->code)<span class="text-muted small">({{ $space->code }})</span>@endif</td>
                                    <td>{{ optional($space->branch)->name }}</td>
                                    <td><span class="badge text-bg-light text-dark border">{{ strtoupper($space->space_type) }}</span></td>
                                    <td>{{ collect([$space->floor ? 'Piso '.$space->floor : null, $space->room ? 'Cuarto '.$space->room : null])->filter()->join(' · ') ?: 'N/A' }}</td>
                                    <td class="text-end">
                                        <a href="{{ url('/admin?edit_space=' . $space->id . '#crud-spaces') }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ url('/admin/spaces/' . $space->id) }}" class="d-inline" data-confirm="¿Eliminar espacio físico?" data-confirm-title="Eliminar espacio físico" data-confirm-icon="warning" data-confirm-button-text="Sí, eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No hay espacios físicos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="crud-branches" class="mb-2">
        <div class="crud-block-title">CRUD · Sedes</div>
        <div class="crud-block-subtitle">Administra ubicación, orden y datos generales de cada sede.</div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card section-card sticky-form">
                <div class="card-header bg-white fw-semibold">{{ $editingBranch->exists ? 'Editar sede' : 'Nueva sede' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ $editingBranch->exists ? url('/admin/branches/' . $editingBranch->id) : url('/admin/branches') }}">
                        @csrf
                        @if ($editingBranch->exists)
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="branch_name" class="form-control" value="{{ old('branch_name', $editingBranch->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Domicilio</label>
                            <input type="text" name="branch_address" class="form-control" value="{{ old('branch_address', $editingBranch->address) }}">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Ciudad</label>
                                <input type="text" name="branch_city" class="form-control" value="{{ old('branch_city', $editingBranch->city) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <input type="text" name="branch_state" class="form-control" value="{{ old('branch_state', $editingBranch->state) }}">
                            </div>
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">País</label>
                                <input type="text" name="branch_country" class="form-control" value="{{ old('branch_country', $editingBranch->country) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Orden</label>
                                <input type="number" min="0" name="branch_sort_order" class="form-control" value="{{ old('branch_sort_order', $editingBranch->sort_order ?? 0) }}">
                            </div>
                        </div>
                        <div class="mt-3 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="branch_description" rows="3" class="form-control">{{ old('branch_description', $editingBranch->description) }}</textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ $editingBranch->exists ? 'Actualizar' : 'Crear' }}</button>
                            @if ($editingBranch->exists)
                                <a href="{{ url('/admin') }}" class="btn btn-outline-secondary">Cancelar</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card section-card">
                <div class="card-header bg-white fw-semibold">Sedes registradas</div>
                <div class="table-responsive">
                    <table class="table table-hover mini-table mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Ubicación</th>
                                <th>Nodos</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($branches as $branch)
                                <tr>
                                    <td>{{ $branch->name }}</td>
                                    <td>{{ collect([$branch->city, $branch->state, $branch->country])->filter()->join(', ') ?: 'N/A' }}</td>
                                    <td>{{ $branch->nodes()->count() }}</td>
                                    <td class="text-end">
                                        <a href="{{ url('/admin?edit_branch=' . $branch->id . '#crud-branches') }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ url('/admin/branches/' . $branch->id) }}" class="d-inline" data-confirm="¿Eliminar sede?" data-confirm-title="Eliminar sede" data-confirm-icon="warning" data-confirm-button-text="Sí, eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No hay sedes registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="crud-node-types" class="mb-2">
        <div class="crud-block-title">CRUD · Tipos de nodo</div>
        <div class="crud-block-subtitle">Catálogo base para clasificar nodos de red y sus metadatos.</div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card section-card sticky-form" id="node-type-form">
                <div class="card-header bg-white fw-semibold">{{ $editingNodeType->exists ? 'Editar tipo de nodo' : 'Nuevo tipo de nodo' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ $editingNodeType->exists ? url('/admin/node-types/' . $editingNodeType->id) : url('/admin/node-types') }}">
                        @csrf
                        @if ($editingNodeType->exists)
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="nodeTypeNameInput" name="node_type_name" class="form-control" value="{{ old('node_type_name', $editingNodeType->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" id="nodeTypeSlugInput" name="node_type_slug" class="form-control" value="{{ old('node_type_slug', $editingNodeType->slug) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icono / etiqueta corta</label>
                            <input type="text" id="nodeTypeIconInput" name="node_type_icon" class="form-control" value="{{ old('node_type_icon', $editingNodeType->icon) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Presets sugeridos</label>
                            <div class="node-type-presets">
                                <button type="button" class="node-type-preset" data-preset-name="Base de datos" data-preset-slug="database" data-preset-icon="DB">Base de datos</button>
                                <button type="button" class="node-type-preset" data-preset-name="Balanceador" data-preset-slug="load-balancer" data-preset-icon="LB">Balanceador</button>
                                <button type="button" class="node-type-preset" data-preset-name="PBX / Telefonia" data-preset-slug="pbx" data-preset-icon="PBX">PBX</button>
                                <button type="button" class="node-type-preset" data-preset-name="Camara IP" data-preset-slug="ip-camera" data-preset-icon="CAM">Camara IP</button>
                                <button type="button" class="node-type-preset" data-preset-name="Impresora" data-preset-slug="printer" data-preset-icon="PRN">Impresora</button>
                                <button type="button" class="node-type-preset" data-preset-name="Storage" data-preset-slug="storage" data-preset-icon="ST">Storage</button>
                            </div>
                        </div>
                        <div class="mb-3 node-type-preview">
                            <div class="node-type-preview-canvas">
                                <div id="nodeTypePreviewShape" class="node-type-preview-shape variant-default">
                                    <span id="nodeTypePreviewIcon">N</span>
                                </div>
                                <div class="node-type-preview-meta">
                                    <div id="nodeTypePreviewName" class="node-type-preview-name">Nodo genérico</div>
                                    <div id="nodeTypePreviewSlug" class="node-type-preview-slug">slug: generic-node</div>
                                    <div id="nodeTypePreviewHelper" class="node-type-preview-helper">Vista previa estimada del elemento dentro del diagrama.</div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meta JSON</label>
                            <textarea name="node_type_meta_json" rows="4" class="form-control code-box">{{ old('node_type_meta_json', $editingNodeType->exists && $editingNodeType->meta ? json_encode($editingNodeType->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ $editingNodeType->exists ? 'Actualizar' : 'Crear' }}</button>
                            @if ($editingNodeType->exists)
                                <a href="{{ url('/admin') }}" class="btn btn-outline-secondary">Cancelar</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card section-card">
                <div class="card-header bg-white fw-semibold">Tipos de nodo</div>
                <div class="table-responsive">
                    <table class="table table-hover mini-table mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Slug</th>
                                <th>Icono</th>
                                <th>En uso</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($nodeTypes as $nodeType)
                                <tr>
                                    <td>{{ $nodeType->name }}</td>
                                    <td>{{ $nodeType->slug }}</td>
                                    <td><span class="badge text-bg-light border text-dark">{{ $nodeType->icon ?: 'N/A' }}</span></td>
                                    <td>{{ $nodeType->nodes()->count() }}</td>
                                    <td class="text-end">
                                        <a href="{{ url('/admin?edit_node_type=' . $nodeType->id . '#crud-node-types') }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ url('/admin/node-types/' . $nodeType->id) }}" class="d-inline" data-confirm="¿Eliminar tipo de nodo?" data-confirm-title="Eliminar tipo de nodo" data-confirm-icon="warning" data-confirm-button-text="Sí, eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No hay tipos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="crud-nodes" class="mb-2">
        <div class="crud-block-title">CRUD · Nodos</div>
        <div class="crud-block-subtitle">Inventario técnico con filtros operativos y configuración de puertos.</div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card section-card sticky-form">
                <div class="card-header bg-white fw-semibold">{{ $editingNode->exists ? 'Editar nodo' : 'Nuevo nodo' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ $editingNode->exists ? url('/admin/nodes/' . $editingNode->id) : url('/admin/nodes') }}">
                        @csrf
                        @if ($editingNode->exists)
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Sede</label>
                            <select name="node_branch_id" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) old('node_branch_id', $editingNode->branch_id) === (string) $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de nodo</label>
                            <select name="node_type_id" id="nodeTypeSelect" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($nodeTypes as $nodeType)
                                    <option value="{{ $nodeType->id }}" data-slug="{{ $nodeType->slug }}" @selected((string) old('node_type_id', $editingNode->node_type_id) === (string) $nodeType->id)>{{ $nodeType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Espacio físico (opcional)</label>
                            <select name="node_physical_space_id" class="form-select">
                                <option value="">Sin asignar</option>
                                @foreach ($spaces as $space)
                                    <option value="{{ $space->id }}" @selected((string) old('node_physical_space_id', $editingNode->physical_space_id) === (string) $space->id)>
                                        {{ $space->name }} · {{ strtoupper($space->space_type) }} · {{ optional($space->branch)->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="node_name" class="form-control" value="{{ old('node_name', $editingNode->name) }}" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Código</label>
                                <input type="text" name="node_code" class="form-control" value="{{ old('node_code', $editingNode->code) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <input type="text" name="node_status" class="form-control" value="{{ old('node_status', $editingNode->status ?? 'active') }}" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Piso</label>
                                <input type="text" name="node_floor" class="form-control" value="{{ old('node_floor', $editingNode->floor) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cuarto</label>
                                <input type="text" name="node_room" class="form-control" value="{{ old('node_room', $editingNode->room) }}">
                            </div>
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">IP</label>
                                <input type="text" name="node_ip_address" class="form-control" value="{{ old('node_ip_address', $editingNode->ip_address) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">MAC</label>
                                <input type="text" name="node_mac_address" class="form-control" value="{{ old('node_mac_address', $editingNode->mac_address) }}">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Cableado</label>
                            <input type="text" name="node_cable_type" class="form-control" value="{{ old('node_cable_type', $editingNode->cable_type) }}">
                        </div>
                        <div class="form-check mt-3 mb-3">
                            <input class="form-check-input" type="checkbox" name="node_is_monitored" id="node_is_monitored" value="1" @checked(old('node_is_monitored', $editingNode->is_monitored))>
                            <label class="form-check-label" for="node_is_monitored">Habilitar monitoreo</label>
                        </div>
                        {{-- Port Configurator --}}
                        <div class="mb-2">
                            <label class="form-label d-flex align-items-center justify-content-between mb-1">
                                <span>Configurar puertos</span>
                                <button type="button" id="toggleJsonBtn" class="btn btn-link btn-sm p-0 text-secondary" style="font-size:.75rem;text-decoration:none">Ver JSON ↕</button>
                            </label>
                            <div class="mb-2">
                                <input type="text" id="portMnemonic" class="form-control form-control-sm" placeholder="Mnemónico del dispositivo (ej. GDL-AND-SW-01)">
                            </div>
                            <div class="d-flex align-items-center gap-1 mb-2 flex-wrap">
                                <span class="text-muted small">Puertos:</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary port-count-btn" data-count="8">8</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary port-count-btn" data-count="12">12</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary port-count-btn" data-count="16">16</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary port-count-btn" data-count="24">24</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary port-count-btn" data-count="48">48</button>
                                <input type="number" id="portCountCustom" class="form-control form-control-sm ms-1" style="width:68px" min="1" max="96" placeholder="N">
                            </div>
                            <div class="pv-device">
                                <div class="pv-device-label" id="pvDeviceLabel">— sin mnemónico —</div>
                                <div class="pv-ports-grid" id="portGrid">
                                    <span style="color:#64748b;font-size:.7rem">Selecciona cantidad de puertos para comenzar</span>
                                </div>
                            </div>
                            <div id="portEditor" class="rounded-2 p-2 mt-2 bg-white border" style="display:none">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold small" id="portEditorTitle">Puerto</span>
                                    <button type="button" id="portEditorClose" class="btn-close" style="font-size:.7rem"></button>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small mb-0">Etiqueta</label>
                                        <input type="text" id="pvPortName" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-0">Estado</label>
                                        <select id="pvPortStatus" class="form-select form-select-sm">
                                            <option value="up">🟢 Activo (up)</option>
                                            <option value="down">🔴 Inactivo (down)</option>
                                            <option value="unused">⚫ Sin uso</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small mb-0">Conectado a</label>
                                        <input type="text" id="pvPortConnected" class="form-control form-control-sm" placeholder="PC-01, Cámara, AP, Impresora…">
                                    </div>
                                </div>
                                <button type="button" id="pvPortSave" class="btn btn-sm btn-primary mt-2 w-100">Guardar puerto</button>
                            </div>
                        </div>
                        <div class="mb-3" id="jsonSection" style="display:none">
                            <label class="form-label d-flex align-items-center justify-content-between">
                                <span class="text-muted small">JSON Raw</span>
                                <button type="button" id="hideJsonBtn" class="btn btn-link btn-sm p-0 text-muted" style="font-size:.75rem;text-decoration:none">Ocultar ✕</button>
                            </label>
                            <textarea name="node_details_json" id="nodeDetailsJson" rows="5" class="form-control code-box">{{ old('node_details_json', $editingNode->exists && $editingNode->details ? json_encode($editingNode->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ $editingNode->exists ? 'Actualizar' : 'Crear' }}</button>
                            @if ($editingNode->exists)
                                <a href="{{ url('/admin') }}" class="btn btn-outline-secondary">Cancelar</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card section-card">
                <div class="card-header bg-white fw-semibold">Nodos registrados</div>
                <div class="card-body border-bottom py-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label mb-1 small text-muted">Filtro sede</label>
                            <select id="nodeFilterBranch" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-1 small text-muted">Filtro estado</label>
                            <select id="nodeFilterStatus" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="active">active</option>
                                <option value="inactive">inactive</option>
                                <option value="warning">warning</option>
                                <option value="error">error</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="nodeFilterMonitoredOnly">
                                <label class="form-check-label small" for="nodeFilterMonitoredOnly">Solo monitoreados</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" id="nodeFilterReset">Limpiar</button>
                        </div>
                    </div>
                    <small class="text-muted" id="nodeFilterCount"></small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mini-table mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Sede</th>
                                <th>Espacio</th>
                                <th>Tipo</th>
                                <th>IP</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($nodes as $node)
                                <tr class="node-row"
                                    data-branch-id="{{ $node->branch_id }}"
                                    data-status="{{ strtolower((string) ($node->status ?? 'inactive')) }}"
                                    data-monitored="{{ $node->is_monitored ? '1' : '0' }}">
                                    <td>{{ $node->name }}</td>
                        <td>{{ optional($node->branch)->name }}</td>
                                    <td>{{ optional($node->physicalSpace)->name ?? 'N/A' }}</td>
                                    <td>{{ optional($node->nodeType)->name }}</td>
                                    <td><code style="font-size:.8rem">{{ $node->ip_address ?? '—' }}</code></td>
                                    <td>@php $nst = strtolower($node->status ?? 'inactive'); @endphp
                                        <span class="sb-badge {{ in_array($nst,['active','warning','error','inactive']) ? $nst : 'default' }}">{{ $node->status }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ url('/admin?edit_node=' . $node->id . '#crud-nodes') }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ url('/admin/nodes/' . $node->id) }}" class="d-inline" data-confirm="¿Eliminar nodo?" data-confirm-title="Eliminar nodo" data-confirm-icon="warning" data-confirm-button-text="Sí, eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No hay nodos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== BRANDS CRUD ===== --}}
    <div id="crud-equipment-brands" class="mb-2">
        <div class="crud-block-title">CRUD · Marcas de equipo</div>
        <div class="crud-block-subtitle">Catálogo de fabricantes (Cisco, Ubiquiti, HP, etc.).</div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card section-card sticky-form">
                <div class="card-header bg-white fw-semibold">{{ $editingEquipmentBrand->exists ? 'Editar marca' : 'Nueva marca' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ $editingEquipmentBrand->exists ? url('/admin/equipment-brands/' . $editingEquipmentBrand->id) : url('/admin/equipment-brands') }}">
                        @csrf
                        @if ($editingEquipmentBrand->exists)
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Nombre de la marca</label>
                            <input type="text" name="brand_name" class="form-control" required maxlength="120"
                                value="{{ old('brand_name', $editingEquipmentBrand->name) }}" placeholder="Ej: Cisco, Ubiquiti, TP-Link">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ $editingEquipmentBrand->exists ? 'Actualizar' : 'Crear' }}</button>
                            @if ($editingEquipmentBrand->exists)
                                <a href="{{ url('/admin#crud-equipment-brands') }}" class="btn btn-outline-secondary">Cancelar</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card section-card">
                <div class="card-header bg-white fw-semibold">Marcas registradas</div>
                <div class="table-responsive">
                    <table class="table table-hover mini-table mb-0">
                        <thead>
                            <tr><th>Nombre</th><th>Modelos</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($equipmentBrands as $eb)
                                <tr>
                                    <td class="fw-semibold">{{ $eb->name }}</td>
                                    <td>{{ $eb->equipmentModels()->count() }}</td>
                                    <td class="text-end">
                                        <a href="{{ url('/admin?edit_brand=' . $eb->id . '#crud-equipment-brands') }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ url('/admin/equipment-brands/' . $eb->id) }}" class="d-inline"
                                            data-confirm="¿Eliminar marca {{ $eb->name }}?" data-confirm-title="Eliminar marca" data-confirm-icon="warning" data-confirm-button-text="Sí, eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">No hay marcas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== EQUIPMENT MODELS CRUD ===== --}}
    <div id="crud-equipment-models" class="mb-2">
        <div class="crud-block-title">CRUD · Modelos de equipo</div>
        <div class="crud-block-subtitle">Define modelos por tipo. Para Access Points incluye radio mínimo/máximo de cobertura en metros, señal por defecto y patrón de radiación.</div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card section-card sticky-form">
                <div class="card-header bg-white fw-semibold">{{ $editingEquipmentModel->exists ? 'Editar modelo' : 'Nuevo modelo' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ $editingEquipmentModel->exists ? url('/admin/equipment-models/' . $editingEquipmentModel->id) : url('/admin/equipment-models') }}"
                          id="eqModelForm">
                        @csrf
                        @if ($editingEquipmentModel->exists)
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Marca</label>
                            <select name="eqmodel_brand_id" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($equipmentBrands as $eb)
                                    <option value="{{ $eb->id }}" @selected((string) old('eqmodel_brand_id', $editingEquipmentModel->brand_id) === (string) $eb->id)>{{ $eb->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de equipo</label>
                            @php $eqTypeOld = old('eqmodel_equipment_type', $editingEquipmentModel->equipment_type ?? 'other'); @endphp
                            <select name="eqmodel_equipment_type" class="form-select" id="eqModelTypeSelect" required>
                                @foreach ($equipmentModelTypes as $etv => $etl)
                                    <option value="{{ $etv }}" @selected($eqTypeOld === $etv)>{{ $etl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre del modelo</label>
                            <input type="text" name="eqmodel_name" class="form-control" required maxlength="120"
                                value="{{ old('eqmodel_name', $editingEquipmentModel->name) }}" placeholder="Ej: UAP-AC-PRO, EAP620">
                        </div>
                        {{-- AP-specific fields, shown only when type = access-point --}}
                        <div id="eqModelApFields" style="{{ $eqTypeOld === 'access-point' ? '' : 'display:none;' }}">
                            <hr class="my-2">
                            <div class="small text-muted mb-2 fw-semibold">Parámetros RF (Access Point)</div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label mb-1">Radio mín. cobertura (m)</label>
                                    <input type="number" name="eqmodel_radius_min" class="form-control form-control-sm"
                                        min="0.1" max="9999" step="0.1"
                                        value="{{ old('eqmodel_radius_min', $editingEquipmentModel->coverage_radius_min_m) }}"
                                        placeholder="Ej: 8">
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Radio máx. cobertura (m)</label>
                                    <input type="number" name="eqmodel_radius_max" class="form-control form-control-sm"
                                        min="0.1" max="9999" step="0.1"
                                        value="{{ old('eqmodel_radius_max', $editingEquipmentModel->coverage_radius_max_m) }}"
                                        placeholder="Ej: 30">
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label mb-1">Señal por defecto (dBm)</label>
                                    <input type="number" name="eqmodel_signal_dbm" class="form-control form-control-sm"
                                        min="-120" max="0" step="1"
                                        value="{{ old('eqmodel_signal_dbm', $editingEquipmentModel->default_signal_dbm) }}"
                                        placeholder="Ej: -55">
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Altura montaje (m)</label>
                                    <input type="number" name="eqmodel_mount_height_m" class="form-control form-control-sm"
                                        min="0.1" max="50" step="0.1"
                                        value="{{ old('eqmodel_mount_height_m', $editingEquipmentModel->mount_height_m) }}"
                                        placeholder="Ej: 2.6">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label mb-1">Patrón de radiación</label>
                                @php $eqPatternOld = old('eqmodel_radiation_pattern', $editingEquipmentModel->radiation_pattern ?? ''); @endphp
                                <select name="eqmodel_radiation_pattern" class="form-select form-select-sm">
                                    <option value="">Sin especificar</option>
                                    <option value="omni-donut" @selected($eqPatternOld === 'omni-donut')>Omnidireccional / dona</option>
                                    <option value="sphere" @selected($eqPatternOld === 'sphere')>Esférico</option>
                                    <option value="sector-120" @selected($eqPatternOld === 'sector-120')>Sectorial 120°</option>
                                    <option value="directional-60" @selected($eqPatternOld === 'directional-60')>Direccional 60°</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <textarea name="eqmodel_notes" rows="2" class="form-control form-control-sm">{{ old('eqmodel_notes', $editingEquipmentModel->notes) }}</textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ $editingEquipmentModel->exists ? 'Actualizar' : 'Crear' }}</button>
                            @if ($editingEquipmentModel->exists)
                                <a href="{{ url('/admin#crud-equipment-models') }}" class="btn btn-outline-secondary">Cancelar</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card section-card">
                <div class="card-header bg-white fw-semibold">Modelos registrados</div>
                <div class="table-responsive">
                    <table class="table table-hover mini-table mb-0">
                        <thead>
                            <tr><th>Modelo</th><th>Marca · Tipo</th><th>RF specs</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($equipmentModels as $em)
                                <tr>
                                    <td class="fw-semibold">{{ $em->name }}</td>
                                    <td>
                                        <div>{{ optional($em->brand)->name }}</div>
                                        <div class="text-muted small">{{ $equipmentModelTypes[$em->equipment_type] ?? $em->equipment_type }}</div>
                                    </td>
                                    <td class="small">
                                        @if ($em->equipment_type === 'access-point')
                                            @if ($em->coverage_radius_min_m || $em->coverage_radius_max_m)
                                                <div>Radio: {{ $em->coverage_radius_min_m ?? '?' }}–{{ $em->coverage_radius_max_m ?? '?' }} m</div>
                                            @endif
                                            @if ($em->default_signal_dbm)
                                                <div>Señal: {{ $em->default_signal_dbm }} dBm</div>
                                            @endif
                                            @if ($em->radiation_pattern)
                                                <div>{{ $em->radiation_pattern }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ url('/admin?edit_eqmodel=' . $em->id . '#crud-equipment-models') }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ url('/admin/equipment-models/' . $em->id) }}" class="d-inline"
                                            data-confirm="¿Eliminar modelo {{ $em->name }}?" data-confirm-title="Eliminar modelo" data-confirm-icon="warning" data-confirm-button-text="Sí, eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No hay modelos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
    (function () {
        const typeSelect = document.getElementById('eqModelTypeSelect');
        const apFields = document.getElementById('eqModelApFields');
        if (typeSelect && apFields) {
            typeSelect.addEventListener('change', function () {
                apFields.style.display = this.value === 'access-point' ? '' : 'none';
            });
        }
    })();
    </script>

    <div id="crud-assets" class="mb-2">
        <div class="crud-block-title">CRUD · Inventario TI</div>
        <div class="crud-block-subtitle">Registra equipos de cómputo, teléfonos, diademas y monitores, con vínculo opcional a un nodo.</div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card section-card sticky-form">
                <div class="card-header bg-white fw-semibold">{{ $editingComputerAsset->exists ? 'Editar activo TI' : 'Nuevo activo TI' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ $editingComputerAsset->exists ? url('/admin/computer-assets/' . $editingComputerAsset->id) : url('/admin/computer-assets') }}">
                        @csrf
                        @if ($editingComputerAsset->exists)
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Sede</label>
                            <select name="asset_branch_id" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) old('asset_branch_id', $editingComputerAsset->branch_id) === (string) $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nodo asociado (opcional)</label>
                            <select name="asset_node_id" class="form-select">
                                <option value="">Sin asociar</option>
                                @foreach ($nodes as $node)
                                    <option value="{{ $node->id }}" @selected((string) old('asset_node_id', $editingComputerAsset->node_id) === (string) $node->id)>{{ $node->name }} · {{ optional($node->branch)->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Modelo de catálogo (opcional)</label>
                            <select name="asset_equipment_model_id" class="form-select">
                                <option value="">Sin modelo de catálogo</option>
                                @foreach ($equipmentModels->groupBy(fn ($m) => optional($m->brand)->name ?? 'Sin marca') as $brandGroup => $brandModels)
                                    <optgroup label="{{ $brandGroup }}">
                                        @foreach ($brandModels as $em)
                                            <option value="{{ $em->id }}" @selected((string) old('asset_equipment_model_id', $editingComputerAsset->equipment_model_id) === (string) $em->id)>
                                                {{ $em->name }} ({{ $equipmentModelTypes[$em->equipment_type] ?? $em->equipment_type }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo</label>
                                @php $assetTypeOld = old('asset_equipment_type', $editingComputerAsset->equipment_type ?? 'desktop'); @endphp
                                <select name="asset_equipment_type" class="form-select" required>
                                    @foreach ($assetEquipmentTypes as $assetTypeValue => $assetTypeLabel)
                                        <option value="{{ $assetTypeValue }}" @selected($assetTypeOld === $assetTypeValue)>{{ $assetTypeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                @php $assetStatusOld = old('asset_status', $editingComputerAsset->status ?? 'in_use'); @endphp
                                <select name="asset_status" class="form-select" required>
                                    @foreach ($assetStatusOptions as $assetStatusValue => $assetStatusLabel)
                                        <option value="{{ $assetStatusValue }}" @selected($assetStatusOld === $assetStatusValue)>{{ $assetStatusLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Asset tag</label>
                                <input type="text" name="asset_tag" class="form-control" value="{{ old('asset_tag', $editingComputerAsset->asset_tag) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hostname</label>
                                <input type="text" name="asset_hostname" class="form-control" value="{{ old('asset_hostname', $editingComputerAsset->hostname) }}">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Usuario asignado</label>
                            <input type="text" name="asset_assigned_user" class="form-control" value="{{ old('asset_assigned_user', $editingComputerAsset->assigned_user) }}">
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-md-4">
                                <label class="form-label">Marca</label>
                                <input type="text" name="asset_brand" class="form-control" value="{{ old('asset_brand', $editingComputerAsset->brand) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Modelo</label>
                                <input type="text" name="asset_model" class="form-control" value="{{ old('asset_model', $editingComputerAsset->model) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Serie</label>
                                <input type="text" name="asset_serial_number" class="form-control" value="{{ old('asset_serial_number', $editingComputerAsset->serial_number) }}">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">CPU</label>
                            <input type="text" name="asset_cpu" class="form-control" value="{{ old('asset_cpu', $editingComputerAsset->cpu) }}">
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-md-4">
                                <label class="form-label">RAM (GB)</label>
                                <input type="number" min="1" name="asset_ram_gb" class="form-control" value="{{ old('asset_ram_gb', $editingComputerAsset->ram_gb) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Storage</label>
                                @php $assetStorageTypeOld = old('asset_storage_type', $editingComputerAsset->storage_type); @endphp
                                <select name="asset_storage_type" class="form-select">
                                    <option value="">N/A</option>
                                    @foreach ($assetStorageTypes as $assetStorageValue => $assetStorageLabel)
                                        <option value="{{ $assetStorageValue }}" @selected($assetStorageTypeOld === $assetStorageValue)>{{ $assetStorageLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Storage (GB)</label>
                                <input type="number" min="1" name="asset_storage_gb" class="form-control" value="{{ old('asset_storage_gb', $editingComputerAsset->storage_gb) }}">
                            </div>
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Sistema operativo</label>
                                <input type="text" name="asset_operating_system" class="form-control" value="{{ old('asset_operating_system', $editingComputerAsset->operating_system) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Office / Suite</label>
                                <input type="text" name="asset_office_version" class="form-control" value="{{ old('asset_office_version', $editingComputerAsset->office_version) }}">
                            </div>
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Fecha compra</label>
                                <input type="date" name="asset_purchase_date" class="form-control" value="{{ old('asset_purchase_date', optional($editingComputerAsset->purchase_date)->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fin garantía</label>
                                <input type="date" name="asset_warranty_expires_at" class="form-control" value="{{ old('asset_warranty_expires_at', optional($editingComputerAsset->warranty_expires_at)->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Notas</label>
                            <textarea name="asset_notes" rows="3" class="form-control">{{ old('asset_notes', $editingComputerAsset->notes) }}</textarea>
                        </div>
                        <div class="mt-3 mb-3">
                            <label class="form-label">Detalles JSON</label>
                            <textarea name="asset_details_json" rows="4" class="form-control code-box">{{ old('asset_details_json', $editingComputerAsset->exists && $editingComputerAsset->details ? json_encode($editingComputerAsset->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ $editingComputerAsset->exists ? 'Actualizar' : 'Crear' }}</button>
                            @if ($editingComputerAsset->exists)
                                <a href="{{ url('/admin#crud-assets') }}" class="btn btn-outline-secondary">Cancelar</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card section-card">
                <div class="card-header bg-white fw-semibold">Activos TI registrados</div>
                @php
                    $legacyAssetFilterTypes = $computerAssets->pluck('equipment_type')->filter()->unique()->sort()->values();
                    $legacyAssetFilterStatuses = $computerAssets->pluck('status')->filter()->unique()->sort()->values();
                    $legacyAssetFilterBranches = $computerAssets->map(fn ($asset) => optional($asset->branch)->name)->filter()->unique()->sort()->values();
                    $legacyAssetFilterBrands = $computerAssets->pluck('brand')->filter()->unique()->sort()->values();
                    $legacyAssetFilterModels = $computerAssets->pluck('model')->filter()->unique()->sort()->values();
                @endphp
                <div class="m-2 p-3 border rounded bg-light">
                    <div class="small fw-semibold text-muted mb-2">Filtros de búsqueda</div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Buscar equipo</label>
                            <input
                                type="text"
                                id="legacyInventoryAssetFilterSearch"
                                class="form-control form-control-sm"
                                placeholder="Etiqueta, hostname, IP, serie, software..."
                            >
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Tipo</label>
                            <select id="legacyInventoryAssetFilterType" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                @foreach ($legacyAssetFilterTypes as $equipmentType)
                                    <option value="{{ \Illuminate\Support\Str::lower($equipmentType) }}">{{ $assetEquipmentTypes[$equipmentType] ?? $equipmentType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Estado</label>
                            <select id="legacyInventoryAssetFilterStatus" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                @foreach ($legacyAssetFilterStatuses as $status)
                                    <option value="{{ \Illuminate\Support\Str::lower($status) }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Sede</label>
                            <select id="legacyInventoryAssetFilterBranch" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                @foreach ($legacyAssetFilterBranches as $branchName)
                                    <option value="{{ \Illuminate\Support\Str::lower($branchName) }}">{{ $branchName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 d-grid">
                            <button type="button" id="legacyInventoryAssetFilterReset" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                        </div>
                    </div>
                    <div class="row g-2 align-items-end mt-1">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Software</label>
                            <input
                                type="text"
                                id="legacyInventoryAssetFilterSoftware"
                                class="form-control form-control-sm"
                                placeholder="Office, antivirus, sistema operativo..."
                            >
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Marca</label>
                            <select id="legacyInventoryAssetFilterBrand" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                @foreach ($legacyAssetFilterBrands as $brand)
                                    <option value="{{ \Illuminate\Support\Str::lower($brand) }}">{{ $brand }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Modelo</label>
                            <select id="legacyInventoryAssetFilterModel" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                @foreach ($legacyAssetFilterModels as $model)
                                    <option value="{{ \Illuminate\Support\Str::lower($model) }}">{{ $model }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 align-items-end mt-1">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">RAM mínima (GB)</label>
                            <input type="number" min="0" step="1" id="legacyInventoryAssetFilterRamMin" class="form-control form-control-sm" placeholder="Ej. 8">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Almacenamiento mínimo (GB)</label>
                            <input type="number" min="0" step="1" id="legacyInventoryAssetFilterStorageMin" class="form-control form-control-sm" placeholder="Ej. 256">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Último reporte desde</label>
                            <input type="date" id="legacyInventoryAssetFilterSeenFrom" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Último reporte hasta</label>
                            <input type="date" id="legacyInventoryAssetFilterSeenTo" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="small text-muted mt-2">
                        Mostrando <span id="legacyInventoryAssetFilterVisibleCount">0</span> de <span id="legacyInventoryAssetFilterTotalCount">0</span> activos
                    </div>
                    <div id="legacyInventoryAssetActiveFilters" class="d-flex flex-wrap gap-2 mt-2"></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mini-table mb-0">
                        <thead>
                            <tr>
                                <th>Activo</th>
                                <th>Sede</th>
                                <th>Usuario</th>
                                <th>Detalle</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="legacyInventoryAssetsTableBody">
                            @forelse ($computerAssets as $asset)
                                @php
                                    $legacyAssetSoftwareIndex = \Illuminate\Support\Str::lower(collect([
                                        $asset->office_version,
                                        $asset->operating_system,
                                        $asset->antivirus_summary,
                                        collect(data_get($asset->details, 'inventory.software.installed_programs', []))->pluck('name')->filter()->take(30)->join(' '),
                                    ])->filter()->join(' '));
                                    $legacyAssetSearchIndex = \Illuminate\Support\Str::lower(collect([
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
                                        $legacyAssetSoftwareIndex,
                                    ])->filter()->join(' '));
                                @endphp
                                <tr
                                    data-filter-row="legacy-asset"
                                    data-search="{{ $legacyAssetSearchIndex }}"
                                    data-type="{{ \Illuminate\Support\Str::lower($asset->equipment_type ?? '') }}"
                                    data-status="{{ \Illuminate\Support\Str::lower($asset->status ?? '') }}"
                                    data-branch="{{ \Illuminate\Support\Str::lower(optional($asset->branch)->name ?? '') }}"
                                    data-brand="{{ \Illuminate\Support\Str::lower($asset->brand ?? '') }}"
                                    data-model="{{ \Illuminate\Support\Str::lower($asset->model ?? '') }}"
                                    data-software="{{ $legacyAssetSoftwareIndex }}"
                                    data-ram="{{ $asset->ram_gb ?? '' }}"
                                    data-storage="{{ $asset->storage_gb ?? '' }}"
                                    data-last-seen="{{ $asset->last_seen_at?->toIso8601String() ?? '' }}"
                                >
                                    <td>
                                        <div class="fw-semibold">{{ $asset->asset_tag ?: ($asset->hostname ?: ($asset->brand && $asset->model ? $asset->brand . ' ' . $asset->model : 'Activo #' . $asset->id)) }}</div>
                                        <div class="text-muted small">{{ $asset->equipmentTypeLabel() }} @if($asset->serial_number) · S/N {{ $asset->serial_number }} @endif @if($asset->node) · Nodo {{ $asset->node->name }} @endif</div>
                                    </td>
                                    <td>{{ optional($asset->branch)->name }}</td>
                                    <td>{{ $asset->assigned_user ?: 'Sin asignar' }}</td>
                                    <td>{{ collect([$asset->brand && $asset->model ? $asset->brand . ' ' . $asset->model : ($asset->brand ?: null), $asset->cpu, $asset->ram_gb ? $asset->ram_gb . ' GB RAM' : null, $asset->storage_gb ? $asset->storage_gb . ' GB ' . strtoupper((string) $asset->storage_type) : null])->filter()->unique()->join(' · ') ?: 'N/A' }}</td>
                                    <td>
                                        @php $assetStatusClass = in_array($asset->status, ['in_use', 'stock', 'repair', 'retired'], true) ? $asset->status : 'default'; @endphp
                                        <span class="badge asset-status-badge {{ $assetStatusClass }}">{{ $asset->statusLabel() }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ url('/admin?edit_asset=' . $asset->id . '#crud-assets') }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ url('/admin/computer-assets/' . $asset->id) }}" class="d-inline" data-confirm="¿Eliminar activo TI?" data-confirm-title="Eliminar activo TI" data-confirm-icon="warning" data-confirm-button-text="Sí, eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No hay activos TI registrados.</td></tr>
                            @endforelse
                            <tr id="legacyInventoryAssetsNoResults" class="d-none">
                                <td colspan="6" class="text-center text-muted py-4">No se encontraron activos con esos filtros</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="crud-software" class="mb-2">
        <div class="crud-block-title">CRUD · Sistemas</div>
        <div class="crud-block-subtitle">Asocia software a nodos y mantiene versiones/proveedor/contacto.</div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card section-card sticky-form">
                <div class="card-header bg-white fw-semibold">{{ $editingSoftware->exists ? 'Editar sistema' : 'Nuevo sistema' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ $editingSoftware->exists ? url('/admin/software/' . $editingSoftware->id) : url('/admin/software') }}">
                        @csrf
                        @if ($editingSoftware->exists)
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Sistema</label>
                            <input type="text" name="software_name" class="form-control" value="{{ old('software_name', $editingSoftware->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nodo / servidor</label>
                            <select name="software_node_id" class="form-select">
                                <option value="">Sin asignar</option>
                                @foreach ($nodes as $node)
                                    <option value="{{ $node->id }}" @selected((string) old('software_node_id', $editingSoftware->node_id) === (string) $node->id)>{{ $node->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Versión</label>
                                <input type="text" name="software_version" class="form-control" value="{{ old('software_version', $editingSoftware->version) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Proveedor</label>
                                <input type="text" name="software_vendor" class="form-control" value="{{ old('software_vendor', $editingSoftware->vendor) }}">
                            </div>
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Email contacto</label>
                                <input type="email" name="software_contact_email" class="form-control" value="{{ old('software_contact_email', $editingSoftware->contact_email) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="software_contact_phone" class="form-control" value="{{ old('software_contact_phone', $editingSoftware->contact_phone) }}">
                            </div>
                        </div>
                        <div class="mt-3 mb-3">
                            <label class="form-label">Proyecto</label>
                            <input type="text" name="software_project_name" class="form-control" value="{{ old('software_project_name', $editingSoftware->project_name) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Detalles JSON</label>
                            <textarea name="software_details_json" rows="4" class="form-control code-box">{{ old('software_details_json', $editingSoftware->exists && $editingSoftware->details ? json_encode($editingSoftware->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ $editingSoftware->exists ? 'Actualizar' : 'Crear' }}</button>
                            @if ($editingSoftware->exists)
                                <a href="{{ url('/admin') }}" class="btn btn-outline-secondary">Cancelar</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card section-card">
                <div class="card-header bg-white fw-semibold">Sistemas registrados</div>
                <div class="table-responsive">
                    <table class="table table-hover mini-table mb-0">
                        <thead>
                            <tr>
                                <th>Sistema</th>
                                <th>Nodo</th>
                                <th>Versión</th>
                                <th>Proveedor</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($systems as $system)
                                <tr>
                                    <td>{{ $system->name }}</td>
                                    <td>{{ optional($system->node)->name ?? 'Sin asignar' }}</td>
                                    <td>{{ $system->version ?? 'N/A' }}</td>
                                    <td>{{ $system->vendor ?? 'N/A' }}</td>
                                    <td class="text-end">
                                        <a href="{{ url('/admin?edit_software=' . $system->id . '#crud-software') }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ url('/admin/software/' . $system->id) }}" class="d-inline" data-confirm="¿Eliminar sistema?" data-confirm-title="Eliminar sistema" data-confirm-icon="warning" data-confirm-button-text="Sí, eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No hay sistemas registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="crud-relations" class="mb-2">
        <div class="crud-block-title">CRUD · Relaciones</div>
        <div class="crud-block-subtitle">Define conexiones lógicas/físicas entre nodos del tenant.</div>
    </div>
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card section-card sticky-form">
                <div class="card-header bg-white fw-semibold">{{ $editingRelation->exists ? 'Editar relación' : 'Nueva relación' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ $editingRelation->exists ? url('/admin/relations/' . $editingRelation->id) : url('/admin/relations') }}">
                        @csrf
                        @if ($editingRelation->exists)
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Nodo origen</label>
                            <select name="relation_from_node_id" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($nodes as $node)
                                    <option value="{{ $node->id }}" @selected((string) old('relation_from_node_id', $editingRelation->from_node_id) === (string) $node->id)>{{ $node->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nodo destino</label>
                            <select name="relation_to_node_id" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach ($nodes as $node)
                                    <option value="{{ $node->id }}" @selected((string) old('relation_to_node_id', $editingRelation->to_node_id) === (string) $node->id)>{{ $node->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de relación</label>
                            <input type="text" name="relation_type" class="form-control" value="{{ old('relation_type', $editingRelation->relation_type ?? 'linked_to') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Peso preferido para traceroute (opcional)</label>
                            <input type="number" min="1" max="999" name="relation_preferred_weight" class="form-control" value="{{ old('relation_preferred_weight', $editingRelation->preferred_weight) }}" placeholder="Ej. 1 = preferente, 9 = costoso">
                            <small class="text-muted">Si lo dejas vacío, el sistema usará un peso automático según el tipo de enlace.</small>
                            <div class="relation-weight-scale">
                                <span class="relation-weight-badge preferred">1-3 Preferente</span>
                                <span class="relation-weight-badge normal">4-8 Normal</span>
                                <span class="relation-weight-badge backup">9+ Respaldo / costoso</span>
                                <span class="relation-weight-badge auto">Auto por tipo</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <textarea name="relation_notes" rows="4" class="form-control">{{ old('relation_notes', $editingRelation->notes) }}</textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ $editingRelation->exists ? 'Actualizar' : 'Crear' }}</button>
                            @if ($editingRelation->exists)
                                <a href="{{ url('/admin') }}" class="btn btn-outline-secondary">Cancelar</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card section-card">
                <div class="card-header bg-white fw-semibold">Relaciones entre nodos</div>
                <div class="px-3 pt-3 d-flex flex-wrap gap-2">
                    <span class="relation-weight-badge preferred">1-3 Preferente</span>
                    <span class="relation-weight-badge normal">4-8 Normal</span>
                    <span class="relation-weight-badge backup">9+ Respaldo / costoso</span>
                    <span class="relation-weight-badge auto">Auto por tipo</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mini-table mb-0">
                        <thead>
                            <tr>
                                <th>Origen</th>
                                <th>Tipo</th>
                                <th>Peso</th>
                                <th>Destino</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($relations as $relation)
                                @php
                                    $weightValue = $relation->preferred_weight;
                                    $weightClass = 'auto';
                                    $weightLabel = 'Auto';

                                    if ($weightValue !== null) {
                                        if ($weightValue <= 3) {
                                            $weightClass = 'preferred';
                                            $weightLabel = 'Preferente';
                                        } elseif ($weightValue <= 8) {
                                            $weightClass = 'normal';
                                            $weightLabel = 'Normal';
                                        } else {
                                            $weightClass = 'backup';
                                            $weightLabel = 'Respaldo';
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>{{ optional($relation->fromNode)->name }}</td>
                                    <td>{{ $relation->relation_type }}</td>
                                    <td>
                                        <span class="relation-weight-badge {{ $weightClass }}">
                                            {{ $weightValue ?? 'auto' }} · {{ $weightLabel }}
                                        </span>
                                    </td>
                                    <td>{{ optional($relation->toNode)->name }}</td>
                                    <td class="text-end">
                                        <a href="{{ url('/admin?edit_relation=' . $relation->id . '#crud-relations') }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ url('/admin/relations/' . $relation->id) }}" class="d-inline" data-confirm="¿Eliminar relación?" data-confirm-title="Eliminar relación" data-confirm-icon="warning" data-confirm-button-text="Sí, eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No hay relaciones registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if (request()->filled('edit_node_type'))
<script>
document.addEventListener('DOMContentLoaded', () => {
    const formCard = document.getElementById('node-type-form');
    if (!formCard) return;

    formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    formCard.style.boxShadow = '0 0 0 3px rgba(37,99,235,.22), 0 12px 26px rgba(15,23,42,.08)';
    setTimeout(() => {
        formCard.style.boxShadow = '';
    }, 2200);
});
</script>
@endif
@unless($editorOnlyMode)
<script>
(function () {
    'use strict';

    const metricThresholds = {
        cpu: { warning: 80, danger: null, normal: 'success' },
        ram: { warning: 80, danger: null, normal: 'info' },
        disco: { warning: null, danger: 80, normal: 'info' },
    };

    const refreshBtn = document.getElementById('btnRefreshMonitoring');
    const trackedEl = document.getElementById('monitorTrackedAssets');
    const onlineEl = document.getElementById('monitorOnlineAssets');
    const criticalEl = document.getElementById('monitorCriticalAssets');
    const rowsEl = document.getElementById('monitoringRows');

    // ── Helpers ────────────────────────────────────────────────────────────────
    const fmtPct = (v) => (v !== null && v !== undefined) ? `${Number(v).toFixed(1)}%` : 'N/A';
    const fmtUptime = (s) => {
        if (!s) return 'N/A';
        const d = Math.floor(s / 86400), h = Math.floor((s % 86400) / 3600), m = Math.floor((s % 3600) / 60);
        return d > 0 ? `${d}d ${h}h ${m}m` : h > 0 ? `${h}h ${m}m` : `${m}m`;
    };
    const barColor = (label, pct) => {
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
        const color = barColor(label, val);
        return `<div class="mb-2">
            <div class="d-flex justify-content-between small mb-1"><span>${label}</span><span class="fw-semibold">${fmtPct(pct)}</span></div>
            <div class="progress" style="height:10px">
                <div class="progress-bar ${color}" role="progressbar" style="width:${val}%" aria-valuenow="${val}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>`;
    };
    // ── Overview table rows ────────────────────────────────────────────────────
    const renderRows = (assets) => {
        const resolutionLabel = (source) => {
            switch (String(source || 'none')) {
                case 'hostname_unique': return 'hostname';
                case 'ip_unique': return 'ip';
                case 'mac_unique': return 'mac';
                case 'provided_node_id': return 'node_id';
                case 'asset_existing_node': return 'manual';
                case 'manual': return 'manual';
                default: return 'sin resolver';
            }
        };

        if (!Array.isArray(assets) || assets.length === 0) {
            rowsEl.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Sin heartbeats todavía. Inicia el agente y envía el primer reporte.</td></tr>';
            return;
        }
        rowsEl.innerHTML = assets.map((asset) => `
            <tr class="monitor-asset-row" data-asset-id="${asset.id}" style="cursor:pointer;" title="Ver detalle en tiempo real" onclick="window.itcityOpenAssetDetail && window.itcityOpenAssetDetail(this.dataset.assetId)">
                <td>
                    <div class="fw-semibold">${asset.name || 'Activo'}</div>
                    <div class="text-muted small">${asset.hostname || 'sin hostname'}</div>
                </td>
                <td>${asset.branch || 'N/A'}</td>
                <td>
                    <div>${asset.node_name || 'Sin nodo'}</div>
                    <div class="text-muted small">vía ${resolutionLabel(asset.node_resolution_source)}</div>
                </td>
                <td>CPU ${fmtPct(asset.cpu)} · RAM ${fmtPct(asset.memory)} · Disco ${fmtPct(asset.disk)}</td>
                <td>${asset.last_seen_human || 'N/A'}</td>
                <td>
                    <span class="monitor-pill ${asset.online ? 'online' : 'offline'}">${asset.online ? 'Online' : 'Offline'}</span>
                    ${asset.critical ? '<span class="monitor-pill critical">Crítico</span>' : ''}
                </td>
            </tr>`).join('');
    };

    const refreshMonitoring = async () => {
        try {
            const response = await fetch('{{ url('/admin/monitoring/overview') }}', { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            if (!response.ok || !data.ok) return;
            trackedEl.textContent = data.summary?.tracked_assets ?? '0';
            onlineEl.textContent = data.summary?.online_assets ?? '0';
            criticalEl.textContent = data.summary?.critical_assets ?? '0';
            renderRows(data.assets || []);
        } catch { /* silencioso */ }
    };

    if (refreshBtn) refreshBtn.addEventListener('click', refreshMonitoring);
    if (trackedEl) window.setInterval(refreshMonitoring, 30000);

    // ── Asset detail modal ─────────────────────────────────────────────────────
    const adModal = document.getElementById('assetDetailModal');
    const adModalLabel = document.getElementById('assetDetailModalLabel');
    const adModalStatus = document.getElementById('adModalStatus');
    const adModalBody = document.getElementById('assetDetailBody');
    const adModalUpdated = document.getElementById('adModalUpdated');
    const adModalRefreshBtn = document.getElementById('adModalRefreshBtn');
    let adActiveAssetId = null;
    let adRefreshTimer = null;

    const esc = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const toText = (value, fallback = 'N/A') => {
        if (value === null || value === undefined) return fallback;
        if (Array.isArray(value)) {
            const items = value
                .map((item) => toText(item, ''))
                .filter((item) => item && item !== 'N/A');
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

    const renderDetailBody = (data) => {
        const a = data.asset;
        const metrics = data.metrics || [];
        const inventory = data.inventory || {};
        const summary = inventory.summary || {};
        const hardware = inventory.hardware || {};
        const software = inventory.software || {};
        const processors = Array.isArray(hardware.processors) ? hardware.processors : [];
        const memoryModules = Array.isArray(hardware.memory_modules) ? hardware.memory_modules : [];
        const physicalDisks = Array.isArray(hardware.physical_disks) ? hardware.physical_disks : [];
        const logicalDisks = Array.isArray(hardware.logical_disks) ? hardware.logical_disks : [];
        const networkAdapters = Array.isArray(hardware.network_adapters) ? hardware.network_adapters : [];
        const videoControllers = Array.isArray(hardware.video_controllers) ? hardware.video_controllers : [];
        const antivirus = Array.isArray(software.antivirus) ? software.antivirus : [];
        const installedPrograms = Array.isArray(software.installed_programs) ? software.installed_programs : [];
        const inventoryCapturedAt = inventory.captured_at ? fmtIsoDate(inventory.captured_at) : 'Pendiente';
        const inventoryScopeLabel = inventory.capture_scope === 'extended'
            ? 'Extendido'
            : (inventory.capture_scope === 'lightweight' ? 'Ligero' : null);
        const inventoryLastExtendedAt = inventory.last_extended_captured_at
            ? fmtIsoDate(inventory.last_extended_captured_at)
            : null;

        adModalLabel.textContent = a.name || 'Activo';
        adModalStatus.className = `monitor-pill ${a.online ? 'online' : 'offline'}`;
        adModalStatus.textContent = a.online ? 'Online' : 'Offline';
        adModalUpdated.textContent = `Actualizado: ${new Date().toLocaleTimeString()}`;

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
                            { label: 'Marca', value: summary.brand },
                            { label: 'Modelo', value: summary.model },
                            { label: 'Serie', value: summary.serial },
                            { label: 'Tipo', value: summary.equipment_type_label },
                            { label: 'CPU', value: summary.cpu },
                            { label: 'RAM', value: summary.ram_gb ? `${summary.ram_gb} GB` : null },
                            { label: 'Almacenamiento', value: summary.storage_gb ? `${summary.storage_gb} GB ${toText(summary.storage_type_label, '')}`.trim() : summary.storage_type_label },
                            { label: 'SO', value: summary.operating_system },
                            { label: 'Office', value: summary.office_version },
                            { label: 'Inventario', value: inventoryCapturedAt },
                            { label: 'Tipo inventario', value: inventoryScopeLabel },
                            { label: 'Último extendido', value: inventoryLastExtendedAt },
                            { label: 'Uptime', value: fmtUptime(a.uptime) },
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
                            { label: 'Fabricante', value: hardware.system?.manufacturer },
                            { label: 'Modelo', value: hardware.system?.model },
                            { label: 'Dominio', value: hardware.system?.domain },
                            { label: 'Memoria física', value: hardware.system?.total_physical_memory_gb ? `${hardware.system.total_physical_memory_gb} GB` : null },
                            { label: 'PCSystemType', value: hardware.system?.pc_system_type },
                        ])}
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Firmware</div>
                        ${renderFactList([
                            { label: 'BIOS fabricante', value: hardware.bios?.manufacturer },
                            { label: 'BIOS versión', value: hardware.bios?.version },
                            { label: 'BIOS serial', value: hardware.bios?.serial_number },
                            { label: 'BIOS fecha', value: hardware.bios?.release_date ? fmtIsoDate(hardware.bios.release_date) : null },
                            { label: 'Board fabricante', value: hardware.motherboard?.manufacturer },
                            { label: 'Board producto', value: hardware.motherboard?.product },
                            { label: 'Board serial', value: hardware.motherboard?.serial_number },
                        ])}
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Software base</div>
                        ${renderFactList([
                            { label: 'SO', value: software.operating_system?.caption || summary.operating_system },
                            { label: 'Versión', value: software.operating_system?.version },
                            { label: 'Build', value: software.operating_system?.build_number },
                            { label: 'Instalado', value: software.operating_system?.install_date ? fmtIsoDate(software.operating_system.install_date) : null },
                            { label: 'Último arranque', value: software.operating_system?.last_boot_up_time ? fmtIsoDate(software.operating_system.last_boot_up_time) : null },
                            { label: 'Office', value: software.office_version || summary.office_version },
                            { label: 'Hotfixes', value: software.hotfix_count },
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
                            : processors.map((processor) => `<div class="small mb-2"><div class="fw-semibold">${toHtml(processor.name)}</div><div class="text-muted">${toHtml(processor.manufacturer)} · ${toHtml(processor.cores)} cores · ${toHtml(processor.logical_processors)} hilos · ${toHtml(processor.max_clock_mhz)} MHz</div></div>`).join('')}
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Memoria instalada (${memoryModules.length})</div>
                        ${memoryModules.length === 0
                            ? '<div class="small text-muted">Sin datos</div>'
                            : memoryModules.map((module) => `<div class="small mb-2"><div class="fw-semibold">${toHtml(module.bank_label)}</div><div class="text-muted">${toHtml(module.capacity_gb ? `${module.capacity_gb} GB` : null)} · ${toHtml(module.speed_mhz ? `${module.speed_mhz} MHz` : null)} · ${toHtml(module.manufacturer)}${module.part_number ? ` · ${toHtml(module.part_number)}` : ''}</div></div>`).join('')}
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Discos físicos (${physicalDisks.length})</div>
                        ${physicalDisks.length === 0
                            ? '<div class="small text-muted">Sin datos</div>'
                            : physicalDisks.map((disk) => `<div class="small mb-2"><div class="fw-semibold">${toHtml(disk.model)}</div><div class="text-muted">${toHtml(disk.media_type)} · ${toHtml(disk.interface_type)} · ${toHtml(disk.size_gb ? `${disk.size_gb} GB` : null)}${disk.serial_number ? ` · SN ${toHtml(disk.serial_number)}` : ''}</div></div>`).join('')}
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Unidades lógicas (${logicalDisks.length})</div>
                        ${logicalDisks.length === 0
                            ? '<div class="small text-muted">Sin datos</div>'
                            : logicalDisks.map((disk) => `<div class="small mb-2"><div class="fw-semibold">${toHtml(disk.device_id)}${disk.volume_name ? ` · ${toHtml(disk.volume_name)}` : ''}</div><div class="text-muted">${toHtml(disk.file_system)} · ${toHtml(disk.size_gb ? `${disk.size_gb} GB` : null)} · Libre ${toHtml(disk.free_gb ? `${disk.free_gb} GB` : null)}</div></div>`).join('')}
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Video (${videoControllers.length})</div>
                        ${videoControllers.length === 0
                            ? '<div class="small text-muted">Sin datos</div>'
                            : videoControllers.map((video) => `<div class="small mb-2"><div class="fw-semibold">${toHtml(video.name)}</div><div class="text-muted">${toHtml(video.adapter_ram_gb ? `${video.adapter_ram_gb} GB VRAM` : null)}${video.driver_version ? ` · Driver ${toHtml(video.driver_version)}` : ''}</div></div>`).join('')}
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted mb-2 fw-semibold">Adaptadores de red (${networkAdapters.length})</div>
                        ${networkAdapters.length === 0
                            ? '<div class="small text-muted">Sin datos</div>'
                            : networkAdapters.map((adapter) => `<div class="small mb-2"><div class="fw-semibold">${toHtml(adapter.description)}</div><div class="text-muted mb-1">MAC ${toHtml(adapter.mac_address)} · DHCP ${toHtml(adapter.dhcp_enabled ? 'Sí' : 'No')}</div><div class="mb-1">${renderBadgeList(adapter.ip_addresses, 'Sin IP')}</div><div class="text-muted">GW: ${toHtml(adapter.default_gateway)}${adapter.dns_servers ? ` · DNS: ${toHtml(adapter.dns_servers)}` : ''}</div></div>`).join('')}
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
                            : metrics.map(m => `<tr>
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
            const response = await fetch(`/admin/monitoring/asset/${id}`, { headers: { 'Accept': 'application/json' } });
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
        });
        if (adModalRefreshBtn) adModalRefreshBtn.addEventListener('click', () => loadAssetDetail(adActiveAssetId));
    }

    const openAssetDetailModal = (assetId) => {
        if (!assetId || !adModal) return;

        adActiveAssetId = assetId;
        const bsModal = window.bootstrap?.Modal?.getOrCreateInstance(adModal);
        if (!bsModal) {
            window.itcityAlert?.({
                icon: 'error',
                title: 'No se pudo abrir el detalle',
                text: 'Bootstrap Modal no está disponible en esta vista.',
                toast: true,
                position: 'top-end',
            });
            return;
        }

        bsModal.show();
    };

    window.itcityOpenAssetDetail = openAssetDetailModal;

    if (rowsEl) {
        rowsEl.addEventListener('click', (event) => {
            const row = event.target.closest('tr.monitor-asset-row');
            if (!row || !rowsEl.contains(row)) return;

            const interactive = event.target.closest('a, button, input, select, textarea, label');
            if (interactive) return;

            openAssetDetailModal(row.dataset.assetId);
        });
    }

    document.addEventListener('click', (event) => {
        const row = event.target.closest('tr.monitor-asset-row');
        if (!row) return;

        const interactive = event.target.closest('a, button, input, select, textarea, label');
        if (interactive) return;

        openAssetDetailModal(row.dataset.assetId);
    });

    const legacyInventoryFilterSearch = document.getElementById('legacyInventoryAssetFilterSearch');
    const legacyInventoryFilterType = document.getElementById('legacyInventoryAssetFilterType');
    const legacyInventoryFilterStatus = document.getElementById('legacyInventoryAssetFilterStatus');
    const legacyInventoryFilterBranch = document.getElementById('legacyInventoryAssetFilterBranch');
    const legacyInventoryFilterSoftware = document.getElementById('legacyInventoryAssetFilterSoftware');
    const legacyInventoryFilterBrand = document.getElementById('legacyInventoryAssetFilterBrand');
    const legacyInventoryFilterModel = document.getElementById('legacyInventoryAssetFilterModel');
    const legacyInventoryFilterRamMin = document.getElementById('legacyInventoryAssetFilterRamMin');
    const legacyInventoryFilterStorageMin = document.getElementById('legacyInventoryAssetFilterStorageMin');
    const legacyInventoryFilterSeenFrom = document.getElementById('legacyInventoryAssetFilterSeenFrom');
    const legacyInventoryFilterSeenTo = document.getElementById('legacyInventoryAssetFilterSeenTo');
    const legacyInventoryFilterReset = document.getElementById('legacyInventoryAssetFilterReset');
    const legacyInventoryVisibleCount = document.getElementById('legacyInventoryAssetFilterVisibleCount');
    const legacyInventoryTotalCount = document.getElementById('legacyInventoryAssetFilterTotalCount');
    const legacyInventoryActiveFilters = document.getElementById('legacyInventoryAssetActiveFilters');
    const legacyInventoryNoResultsRow = document.getElementById('legacyInventoryAssetsNoResults');
    const legacyInventoryRows = Array.from(document.querySelectorAll('#legacyInventoryAssetsTableBody tr[data-filter-row="legacy-asset"]'));

    const normalizeFilterValue = (value) => String(value ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

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

    const getLegacyInventoryActiveFilters = () => {
        const filters = [];
        const queryText = String(legacyInventoryFilterSearch?.value || '').trim();
        const softwareText = String(legacyInventoryFilterSoftware?.value || '').trim();
        const ramMinText = String(legacyInventoryFilterRamMin?.value || '').trim();
        const storageMinText = String(legacyInventoryFilterStorageMin?.value || '').trim();
        const seenFromText = String(legacyInventoryFilterSeenFrom?.value || '').trim();
        const seenToText = String(legacyInventoryFilterSeenTo?.value || '').trim();

        if (queryText) filters.push({ key: 'search', label: `Buscar: ${queryText}` });
        if (legacyInventoryFilterType?.value) filters.push({ key: 'type', label: `Tipo: ${getSelectedOptionText(legacyInventoryFilterType)}` });
        if (legacyInventoryFilterStatus?.value) filters.push({ key: 'status', label: `Estado: ${getSelectedOptionText(legacyInventoryFilterStatus)}` });
        if (legacyInventoryFilterBranch?.value) filters.push({ key: 'branch', label: `Sede: ${getSelectedOptionText(legacyInventoryFilterBranch)}` });
        if (softwareText) filters.push({ key: 'software', label: `Software: ${softwareText}` });
        if (legacyInventoryFilterBrand?.value) filters.push({ key: 'brand', label: `Marca: ${getSelectedOptionText(legacyInventoryFilterBrand)}` });
        if (legacyInventoryFilterModel?.value) filters.push({ key: 'model', label: `Modelo: ${getSelectedOptionText(legacyInventoryFilterModel)}` });
        if (ramMinText) filters.push({ key: 'ramMin', label: `RAM ≥ ${ramMinText} GB` });
        if (storageMinText) filters.push({ key: 'storageMin', label: `Almacenamiento ≥ ${storageMinText} GB` });
        if (seenFromText) filters.push({ key: 'seenFrom', label: `Último reporte desde: ${seenFromText}` });
        if (seenToText) filters.push({ key: 'seenTo', label: `Último reporte hasta: ${seenToText}` });

        return filters;
    };

    const renderLegacyInventoryFilterChips = () => {
        if (!legacyInventoryActiveFilters) return;

        const filters = getLegacyInventoryActiveFilters();
        if (!filters.length) {
            legacyInventoryActiveFilters.innerHTML = '<span class="text-muted small">Sin filtros activos</span>';
            return;
        }

        legacyInventoryActiveFilters.innerHTML = filters.map((filter) => (
            `<button type="button" class="btn btn-sm btn-outline-primary" data-filter-key="${filter.key}" aria-label="Quitar filtro ${escapeHtml(filter.label)}">${escapeHtml(filter.label)} <span aria-hidden="true">&times;</span></button>`
        )).join('');
    };

    const clearLegacyInventoryFilterByKey = (filterKey) => {
        switch (filterKey) {
            case 'search':
                if (legacyInventoryFilterSearch) legacyInventoryFilterSearch.value = '';
                break;
            case 'type':
                if (legacyInventoryFilterType) legacyInventoryFilterType.value = '';
                break;
            case 'status':
                if (legacyInventoryFilterStatus) legacyInventoryFilterStatus.value = '';
                break;
            case 'branch':
                if (legacyInventoryFilterBranch) legacyInventoryFilterBranch.value = '';
                break;
            case 'software':
                if (legacyInventoryFilterSoftware) legacyInventoryFilterSoftware.value = '';
                break;
            case 'brand':
                if (legacyInventoryFilterBrand) legacyInventoryFilterBrand.value = '';
                break;
            case 'model':
                if (legacyInventoryFilterModel) legacyInventoryFilterModel.value = '';
                break;
            case 'ramMin':
                if (legacyInventoryFilterRamMin) legacyInventoryFilterRamMin.value = '';
                break;
            case 'storageMin':
                if (legacyInventoryFilterStorageMin) legacyInventoryFilterStorageMin.value = '';
                break;
            case 'seenFrom':
                if (legacyInventoryFilterSeenFrom) legacyInventoryFilterSeenFrom.value = '';
                break;
            case 'seenTo':
                if (legacyInventoryFilterSeenTo) legacyInventoryFilterSeenTo.value = '';
                break;
            default:
                break;
        }
    };

    const applyLegacyInventoryFilters = () => {
        if (!legacyInventoryRows.length) {
            if (legacyInventoryVisibleCount) legacyInventoryVisibleCount.textContent = '0';
            if (legacyInventoryTotalCount) legacyInventoryTotalCount.textContent = '0';
            return;
        }

        const query = normalizeFilterValue(legacyInventoryFilterSearch?.value || '');
        const type = normalizeFilterValue(legacyInventoryFilterType?.value || '');
        const status = normalizeFilterValue(legacyInventoryFilterStatus?.value || '');
        const branch = normalizeFilterValue(legacyInventoryFilterBranch?.value || '');
        const software = normalizeFilterValue(legacyInventoryFilterSoftware?.value || '');
        const brand = normalizeFilterValue(legacyInventoryFilterBrand?.value || '');
        const model = normalizeFilterValue(legacyInventoryFilterModel?.value || '');
        const ramMin = toNumberOrNull(legacyInventoryFilterRamMin?.value);
        const storageMin = toNumberOrNull(legacyInventoryFilterStorageMin?.value);
        const seenFrom = toTimeOrNull(legacyInventoryFilterSeenFrom?.value);
        const seenTo = toTimeOrNull(legacyInventoryFilterSeenTo?.value ? `${legacyInventoryFilterSeenTo.value}T23:59:59` : '');

        let visible = 0;
        legacyInventoryRows.forEach((row) => {
            const rowSearch = normalizeFilterValue(row.dataset.search || '');
            const rowType = normalizeFilterValue(row.dataset.type || '');
            const rowStatus = normalizeFilterValue(row.dataset.status || '');
            const rowBranch = normalizeFilterValue(row.dataset.branch || '');
            const rowSoftware = normalizeFilterValue(row.dataset.software || '');
            const rowBrand = normalizeFilterValue(row.dataset.brand || '');
            const rowModel = normalizeFilterValue(row.dataset.model || '');
            const rowRam = toNumberOrNull(row.dataset.ram);
            const rowStorage = toNumberOrNull(row.dataset.storage);
            const rowSeen = toTimeOrNull(row.dataset.lastSeen);

            const matches = (!query || rowSearch.includes(query))
                && (!type || rowType === type)
                && (!status || rowStatus === status)
                && (!branch || rowBranch === branch)
                && (!software || rowSoftware.includes(software))
                && (!brand || rowBrand === brand)
                && (!model || rowModel === model)
                && (ramMin === null || (rowRam !== null && rowRam >= ramMin))
                && (storageMin === null || (rowStorage !== null && rowStorage >= storageMin))
                && (seenFrom === null || (rowSeen !== null && rowSeen >= seenFrom))
                && (seenTo === null || (rowSeen !== null && rowSeen <= seenTo));

            row.classList.toggle('d-none', !matches);
            if (matches) visible += 1;
        });

        if (legacyInventoryVisibleCount) legacyInventoryVisibleCount.textContent = String(visible);
        if (legacyInventoryTotalCount) legacyInventoryTotalCount.textContent = String(legacyInventoryRows.length);
        if (legacyInventoryNoResultsRow) {
            legacyInventoryNoResultsRow.classList.toggle('d-none', visible !== 0);
        }
        renderLegacyInventoryFilterChips();
    };

    if (legacyInventoryActiveFilters) {
        legacyInventoryActiveFilters.addEventListener('click', (event) => {
            const chipButton = event.target.closest('button[data-filter-key]');
            if (!chipButton || !legacyInventoryActiveFilters.contains(chipButton)) return;
            clearLegacyInventoryFilterByKey(chipButton.dataset.filterKey || '');
            applyLegacyInventoryFilters();
        });
    }

    [legacyInventoryFilterSearch, legacyInventoryFilterType, legacyInventoryFilterStatus, legacyInventoryFilterBranch, legacyInventoryFilterSoftware, legacyInventoryFilterBrand, legacyInventoryFilterModel, legacyInventoryFilterRamMin, legacyInventoryFilterStorageMin, legacyInventoryFilterSeenFrom, legacyInventoryFilterSeenTo].forEach((control) => {
        if (!control) return;
        control.addEventListener('input', applyLegacyInventoryFilters);
        control.addEventListener('change', applyLegacyInventoryFilters);
    });

    if (legacyInventoryFilterReset) {
        legacyInventoryFilterReset.addEventListener('click', () => {
            if (legacyInventoryFilterSearch) legacyInventoryFilterSearch.value = '';
            if (legacyInventoryFilterType) legacyInventoryFilterType.value = '';
            if (legacyInventoryFilterStatus) legacyInventoryFilterStatus.value = '';
            if (legacyInventoryFilterBranch) legacyInventoryFilterBranch.value = '';
            if (legacyInventoryFilterSoftware) legacyInventoryFilterSoftware.value = '';
            if (legacyInventoryFilterBrand) legacyInventoryFilterBrand.value = '';
            if (legacyInventoryFilterModel) legacyInventoryFilterModel.value = '';
            if (legacyInventoryFilterRamMin) legacyInventoryFilterRamMin.value = '';
            if (legacyInventoryFilterStorageMin) legacyInventoryFilterStorageMin.value = '';
            if (legacyInventoryFilterSeenFrom) legacyInventoryFilterSeenFrom.value = '';
            if (legacyInventoryFilterSeenTo) legacyInventoryFilterSeenTo.value = '';
            applyLegacyInventoryFilters();
        });
    }

    applyLegacyInventoryFilters();

    // ── Installer download link ────────────────────────────────────────────────
    const installerLink     = document.getElementById('btnDownloadAgentInstaller');
    const snmpTemplateLink  = document.getElementById('btnDownloadSnmpTargetsTemplate');
    const installerBranch   = document.getElementById('agentInstallerBranch');
    const installerAssetTag = document.getElementById('agentInstallerAssetTag');
    const installerInterval = document.getElementById('agentInstallerInterval');
    const installerInvHours = document.getElementById('agentInstallerInventoryHours');

    const buildInstallerUrl = () => {
        const params = new URLSearchParams();
        const branchId = installerBranch?.value || '';
        if (branchId) params.set('branch_id', branchId);
        const assetTag = (installerAssetTag?.value || '').trim();
        if (assetTag) params.set('asset_tag', assetTag);
        const interval = parseInt(installerInterval?.value || '60', 10);
        if (interval && interval !== 60) params.set('interval', interval);
        const invHours = parseInt(installerInvHours?.value || '12', 10);
        if (invHours && invHours !== 12) params.set('inventory_hours', invHours);
        const qs = params.toString();
        return '/admin/monitoring/agent-installer' + (qs ? `?${qs}` : '');
    };

    const refreshInstallerLink = () => {
        if (installerLink) installerLink.href = buildInstallerUrl();
    };

    const buildSnmpTemplateUrl = () => {
        const params = new URLSearchParams();
        const branchId = installerBranch?.value || '';
        if (branchId) params.set('branch_id', branchId);
        const qs = params.toString();
        return '/admin/monitoring/snmp-targets-template' + (qs ? `?${qs}` : '');
    };

    const refreshSnmpTemplateLink = () => {
        if (snmpTemplateLink) snmpTemplateLink.href = buildSnmpTemplateUrl();
    };

    installerLink?.addEventListener('click', (e) => {
        installerLink.href = buildInstallerUrl();
    });

    snmpTemplateLink?.addEventListener('click', () => {
        snmpTemplateLink.href = buildSnmpTemplateUrl();
    });

    [installerBranch, installerAssetTag, installerInterval, installerInvHours].forEach((el) => {
        el?.addEventListener('change', refreshInstallerLink);
        el?.addEventListener('input', refreshInstallerLink);
    });

    installerBranch?.addEventListener('change', refreshSnmpTemplateLink);
    installerBranch?.addEventListener('input', refreshSnmpTemplateLink);

    refreshInstallerLink();
    refreshSnmpTemplateLink();
})();
</script>
@endunless
@php
$__apModelsJson = $apModels->map(fn ($m) => [
    'id'                    => $m->id,
    'name'                  => $m->name,
    'brand'                 => optional($m->brand)->name,
    'coverage_radius_min_m' => $m->coverage_radius_min_m,
    'coverage_radius_max_m' => $m->coverage_radius_max_m,
    'default_signal_dbm'    => $m->default_signal_dbm,
    'radiation_pattern'     => $m->radiation_pattern,
    'mount_height_m'        => $m->mount_height_m,
])->values()->toArray();
@endphp
<script>
window.__itcityApModels = {!! json_encode($__apModelsJson, JSON_HEX_TAG | JSON_HEX_AMP) !!};
window.__itcityUserId = {!! json_encode(optional(auth()->user())->id) !!};
window.__itcityInitialFloorPlanId = {!! json_encode($editorOnlyFloorPlanId ?? (request()->filled('floor_plan') ? (int) request()->integer('floor_plan') : null)) !!};
window.__itcityFloorPlanBackUrl = {!! json_encode($editorOnlyBackUrl ?? url('/admin/panel-admin-1#section-floor-plans')) !!};
</script>
<script>
(function () {
    'use strict';

    const modalEl = document.getElementById('floorPlanEditorModal');
    if (!modalEl) return;

    const titleEl = document.getElementById('floorPlanEditorTitle');
    const metaNameEl = document.getElementById('fpMetaName');
    const metaBranchEl = document.getElementById('fpMetaBranch');
    const nodeSelectEl = document.getElementById('fpNodeSelect');
    const apModelSelectEl = document.getElementById('fpApModelSelect');
    const editModeEl = document.getElementById('fpEditMode');
    const wallMaterialEl = document.getElementById('fpWallMaterial');
    const layerSelectEl = document.getElementById('fpLayerSelect');
    const layerFilterEl = document.getElementById('fpLayerFilter');
    const radiusInputEl = document.getElementById('fpRadiusInput');
    const radiusMetersInputEl = document.getElementById('fpRadiusMetersInput');
    const signalInputEl = document.getElementById('fpSignalInput');
    const patternSelectEl = document.getElementById('fpPatternSelect');
    const mountOrientationEl = document.getElementById('fpMountOrientationSelect');
    const mountHeightInputEl = document.getElementById('fpMountHeightInput');
    const azimuthInputEl = document.getElementById('fpAzimuthInput');
    const tiltInputEl = document.getElementById('fpTiltInput');
    const snapApWallsInputEl = document.getElementById('fpSnapApWallsInput');
    const scaleWidthInputEl = document.getElementById('fpScaleWidthMeters');
    const scaleHeightInputEl = document.getElementById('fpScaleHeightMeters');
    const wallHeightInputEl = document.getElementById('fpWallHeightInput');
    const doorHeightInputEl = document.getElementById('fpDoorHeightInput');
    const doorWidthInputEl = document.getElementById('fpDoorWidthInput');
    const windowBaseInputEl = document.getElementById('fpWindowBaseInput');
    const windowHeightInputEl = document.getElementById('fpWindowHeightInput');
    const windowWidthInputEl = document.getElementById('fpWindowWidthInput');
    const orthogonalLockInputEl = document.getElementById('fpOrthogonalLockInput');
    const symbolTypeEl = document.getElementById('fpSymbolType');
    const symbolSizeInputEl = document.getElementById('fpSymbolSizeInput');
    const calibrationDistanceInputEl = document.getElementById('fpCalibrationDistance');
    const calibrationToggleBtn = document.getElementById('fpCalibrationToggleBtn');
    const calibrationHintEl = document.getElementById('fpCalibrationHint');
    const scaleSummaryEl = document.getElementById('fpScaleSummary');
    const saveBtn = document.getElementById('fpSavePointsBtn');
    const saveFabBtn = document.getElementById('fpSavePointsFabBtn');
    const clearBtn = document.getElementById('fpClearPointsBtn');
    const deleteSelectedPointBtn = document.getElementById('fpDeleteSelectedPointBtn');
    const clearWallsBtn = document.getElementById('fpClearWallsBtn');
    const editHintEl = document.getElementById('fpEditHint');
    const imageEl = document.getElementById('fpCanvasImage');
    const pdfEl = document.getElementById('fpCanvasPdf');
    const view2dBtn = document.getElementById('fpView2dBtn');
    const view3dBtn = document.getElementById('fpView3dBtn');
    const viewModeHintEl = document.getElementById('fpViewModeHint');
    const viewport3dEl = document.getElementById('fp3dViewport');
    const heatCanvasEl = document.getElementById('fpHeatCanvas');
    const zoomInBtn = document.getElementById('fpZoomInBtn');
    const zoomOutBtn = document.getElementById('fpZoomOutBtn');
    const zoomResetBtn = document.getElementById('fpZoomResetBtn');
    const wallsLayerEl = document.getElementById('fpWallsLayer');
    const wrapEl = document.getElementById('fpCanvasWrap');
    const overlayEl = document.getElementById('fpOverlayLayer');
    const contextMenuEl = document.getElementById('fpContextMenu');
    const signalProbeEl = document.getElementById('fpSignalProbe');
    const noticeEl = document.getElementById('fpUnsupportedNotice');
    const footerEl = document.getElementById('fpFooterInfo');
    const materialLegendEl = document.getElementById('fpMaterialLegend');
    const nodeInsightsCardEl = document.getElementById('fpNodeInsightsCard');
    const nodeInsightsTitleEl = document.getElementById('fpNodeInsightsTitle');
    const nodeInsightsBodyEl = document.getElementById('fpNodeInsightsBody');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const tenantStorageScope = window.location.hostname || 'default';
    const userStorageScope = window.__itcityUserId != null ? String(window.__itcityUserId) : 'guest';
    const snapApWallsStorageKey = `itcity2.floorplan.snapApWalls.${tenantStorageScope}.${userStorageScope}`;
    const legacyTenantSnapApWallsStorageKey = `itcity2.floorplan.snapApWalls.${tenantStorageScope}`;
    const legacyGlobalSnapApWallsStorageKey = 'itcity2.floorplan.snapApWalls';
    const state = {
        planId: null,
        fileType: null,
        points: [],
        walls: [],
        apNodes: [],
        rooms: [],
        editable: false,
        isDuringDragSetup: false,
        pendingWallStart: null,
        wallDrag: null,
        apDrag: null,
        selectedPointIndex: null,
        selectedWallIndex: null,
        selectedRoomId: null,
        contextMenu: { x: null, y: null },
        undoStack: [],
        redoStack: [],
        isRestoringUndo: false,
        viewMode: '2d',
        calibration: {
            active: false,
            start: null,
            end: null,
            current: null,
        },
        threeScene: null,
        zoom: 1,
        zoomMin: 0.3,
        zoomMax: 3,
        zoomStep: 0.15,
        zoomOrigin: { x: 0, y: 0 },
        zoomPan: null,
        nodeSnapshotCache: new Map(),
        nodeSnapshotPending: new Set(),
        nodeInsightsTargetId: null,
    };

    const persistSnapApWallsPreference = () => {
        try {
            if (!snapApWallsInputEl) return;
            localStorage.setItem(snapApWallsStorageKey, snapApWallsInputEl.checked ? '1' : '0');
        } catch (error) {
        }
    };

    const readSnapApWallsPreference = () => {
        try {
            const current = localStorage.getItem(snapApWallsStorageKey);
            if (current !== null) {
                return current;
            }

            const legacyKeys = [legacyTenantSnapApWallsStorageKey, legacyGlobalSnapApWallsStorageKey];
            for (const legacyKey of legacyKeys) {
                const legacyValue = localStorage.getItem(legacyKey);
                if (legacyValue === null) {
                    continue;
                }

                localStorage.setItem(snapApWallsStorageKey, legacyValue);
                return legacyValue;
            }
        } catch (error) {
        }

        return null;
    };

    const restoreSnapApWallsPreference = () => {
        try {
            if (!snapApWallsInputEl) return;
            const raw = readSnapApWallsPreference();
            if (raw === null) return;
            snapApWallsInputEl.checked = raw !== '0';
        } catch (error) {
        }
    };

    restoreSnapApWallsPreference();

    const WALL_MATERIALS = {
        drywall: { label: 'Drywall', loss_db: 3, color: '#94a3b8' },
        brick: { label: 'Ladrillo', loss_db: 8, color: '#b45309' },
        concrete: { label: 'Concreto', loss_db: 12, color: '#64748b' },
        glass: { label: 'Vidrio', loss_db: 2, color: '#06b6d4' },
        wood: { label: 'Madera', loss_db: 5, color: '#92400e' },
        metal: { label: 'Metal', loss_db: 18, color: '#475569' },
        door: { label: 'Puerta', loss_db: 1.5, color: '#f59e0b' },
        window: { label: 'Ventana', loss_db: 1, color: '#38bdf8' },
    };

    const SYMBOL_LIBRARY = {
        rack: { label: 'Rack', icon: '🗄️', color: '#334155' },
        switch: { label: 'Switch', icon: '🧩', color: '#2563eb' },
        camera: { label: 'Cámara', icon: '📷', color: '#7c3aed' },
        desk: { label: 'Escritorio', icon: '🪑', color: '#92400e' },
        printer: { label: 'Impresora', icon: '🖨️', color: '#0f766e' },
        ups: { label: 'UPS', icon: '🔋', color: '#b45309' },
    };

    const esc = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const currentLayerFilter = () => layerFilterEl?.value || 'all';

    const currentEditMode = () => editModeEl?.value || 'access-point';

    const isStructureMode = () => ['wall', 'door', 'window'].includes(currentEditMode());

    const isSymbolMode = () => currentEditMode() === 'symbol';

    const isOrthogonalLockEnabled = () => Boolean(orthogonalLockInputEl?.checked);

    const isAccessPointItem = (point) => String(point?.item_type || 'access-point') === 'access-point';

    const symbolKeyFromPoint = (point) => {
        const itemType = String(point?.item_type || '');
        if (!itemType.startsWith('symbol:')) return null;
        return itemType.slice(7);
    };

    const symbolMetaFromPoint = (point) => {
        const key = symbolKeyFromPoint(point);
        return key ? (SYMBOL_LIBRARY[key] || null) : null;
    };

    const selectedSymbolKey = () => String(symbolTypeEl?.value || 'rack');

    const selectedSymbolSizeMeters = () => {
        const value = symbolSizeInputEl?.value ? Number(symbolSizeInputEl.value) : 1.2;
        return Number.isFinite(value) && value > 0 ? value : 1.2;
    };

    const selectedStructureMaterial = () => {
        const mode = currentEditMode();
        if (mode === 'door') return 'door';
        if (mode === 'window') return 'window';
        return String(wallMaterialEl?.value || 'drywall');
    };

    const materialMeta = (material) => WALL_MATERIALS[String(material || '').toLowerCase()] || WALL_MATERIALS.drywall;

    const planScale = () => ({
        width_m: scaleWidthInputEl?.value ? Number(scaleWidthInputEl.value) : null,
        height_m: scaleHeightInputEl?.value ? Number(scaleHeightInputEl.value) : null,
        unit: 'm',
    });

    const structureDefaults = () => {
        const preferredMaterial = String(wallMaterialEl?.value || 'drywall');
        const allowedPreferred = ['drywall', 'brick', 'concrete', 'glass', 'wood', 'metal'];

        return {
            wall_height_m: wallHeightInputEl?.value ? Number(wallHeightInputEl.value) : null,
            door_height_m: doorHeightInputEl?.value ? Number(doorHeightInputEl.value) : null,
            door_width_m: doorWidthInputEl?.value ? Number(doorWidthInputEl.value) : null,
            window_base_m: windowBaseInputEl?.value ? Number(windowBaseInputEl.value) : null,
            window_height_m: windowHeightInputEl?.value ? Number(windowHeightInputEl.value) : null,
            window_width_m: windowWidthInputEl?.value ? Number(windowWidthInputEl.value) : null,
            orthogonal_lock: isOrthogonalLockEnabled(),
            ap_mount_height_m: mountHeightInputEl?.value ? Number(mountHeightInputEl.value) : null,
            preferred_wall_material: allowedPreferred.includes(preferredMaterial) ? preferredMaterial : 'drywall',
            unit: 'm',
        };
    };

    const hasPlanScale = () => {
        const visibleApCount = visiblePoints.filter(({ point }) => isAccessPointItem(point)).length;
        const totalApCount = state.points.filter((point) => isAccessPointItem(point)).length;
        const totalSymbolCount = Math.max(0, state.points.length - totalApCount);
        const scale = planScale();
        return Number(scale.width_m) > 0 || Number(scale.height_m) > 0;
    };

    const syncScaleSummary = () => {
        if (!scaleSummaryEl) return;
        const scale = planScale();
        if ((scale.width_m ?? 0) > 0 || (scale.height_m ?? 0) > 0) {
            scaleSummaryEl.textContent = `Escala actual: ${scale.width_m || '—'} m ancho x ${scale.height_m || '—'} m alto.`;
            return;
        }
        scaleSummaryEl.textContent = 'Si defines la escala, la cobertura AP se calculará en metros reales.';
    };

    const syncCalibrationUi = () => {
        if (calibrationToggleBtn) {
            calibrationToggleBtn.textContent = state.calibration.active ? 'Cancelar' : 'Calibrar';
            calibrationToggleBtn.classList.toggle('btn-outline-primary', !state.calibration.active);
            calibrationToggleBtn.classList.toggle('btn-warning', state.calibration.active);
        }

        if (!calibrationHintEl) return;

        if (!state.calibration.active) {
            calibrationHintEl.textContent = 'Marca dos puntos sobre una distancia conocida para calcular la escala automáticamente.';
            return;
        }

        if (!state.calibration.start) {
            calibrationHintEl.textContent = 'Calibración activa: haz clic en el primer punto de referencia.';
            return;
        }

        if (!state.calibration.end) {
            const preview = state.calibration.current
                ? calibrationSegmentInfo(state.calibration.start, state.calibration.current)
                : null;
            if (preview) {
                const knownDistance = calibrationDistanceInputEl?.value ? Number(calibrationDistanceInputEl.value) : null;
                calibrationHintEl.textContent = knownDistance > 0
                    ? `Calibración activa: tramo provisional ${preview.pixels.toFixed(1)} px para ${knownDistance.toFixed(2)} m. Haz clic en el segundo punto.`
                    : `Calibración activa: tramo provisional ${preview.pixels.toFixed(1)} px. Captura la distancia conocida y haz clic en el segundo punto.`;
            } else {
                calibrationHintEl.textContent = 'Calibración activa: haz clic en el segundo punto de referencia.';
            }
            return;
        }

        calibrationHintEl.textContent = 'Calibración lista. Si ya capturaste la distancia real en metros, la escala se aplicó automáticamente.';
    };

    const applyCalibrationScale = () => {
        if (!overlayEl || !calibrationDistanceInputEl) return false;
        const knownDistanceMeters = calibrationDistanceInputEl.value ? Number(calibrationDistanceInputEl.value) : null;
        if (!knownDistanceMeters || knownDistanceMeters <= 0) return false;
        if (!state.calibration.start || !state.calibration.end) return false;

        const width = overlayEl.clientWidth;
        const height = overlayEl.clientHeight;
        if (!width || !height) return false;

        const start = {
            x: (Number(state.calibration.start.x_percent || 0) / 100) * width,
            y: (Number(state.calibration.start.y_percent || 0) / 100) * height,
        };
        const state = {
            x: (Number(state.calibration.end.x_percent || 0) / 100) * width,
            y: (Number(state.calibration.end.y_percent || 0) / 100) * height,
        };

        const pixelDistance = Math.hypot(end.x - start.x, end.y - start.y);
        if (pixelDistance < 2) return false;

        const ppm = pixelDistance / knownDistanceMeters;
        if (!ppm || !Number.isFinite(ppm)) return false;

        if (scaleWidthInputEl) scaleWidthInputEl.value = (width / ppm).toFixed(2);
        if (scaleHeightInputEl) scaleHeightInputEl.value = (height / ppm).toFixed(2);
        syncScaleSummary();
        return true;
    };

    function calibrationSegmentInfo(startPoint, endPoint) {
        if (!overlayEl || !startPoint || !endPoint) return null;
        const width = overlayEl.clientWidth;
        const height = overlayEl.clientHeight;
        if (!width || !height) return null;

        const start = {
            x: (Number(startPoint.x_percent || 0) / 100) * width,
            y: (Number(startPoint.y_percent || 0) / 100) * height,
        };
        const end = {
            x: (Number(endPoint.x_percent || 0) / 100) * width,
            y: (Number(endPoint.y_percent || 0) / 100) * height,
        };

        return {
            start,
            end,
            pixels: Math.hypot(end.x - start.x, end.y - start.y),
            midX: (start.x + end.x) / 2,
            midY: (start.y + end.y) / 2,
        };
    }

    const renderMaterialLegend = () => {
        if (!materialLegendEl) return;
        const html = Object.entries(WALL_MATERIALS)
            .map(([, meta]) => {
                return `
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:${meta.color};border:1px solid rgba(15,23,42,.25)"></span>
                        <span>${meta.label} · ${meta.loss_db} dB</span>
                    </div>
                `;
            })
            .join('');

        materialLegendEl.innerHTML = `
            <div class="fw-semibold text-dark mb-1" style="font-size:.72rem;">Atenuación por material</div>
            ${html}
        `;
    };

    const estimatedPlanDimensions = () => {
        const scale = planScale();
        const width = Number(scale.width_m) > 0 ? Number(scale.width_m) : 20;
        const height = Number(scale.height_m) > 0 ? Number(scale.height_m) : 20;
        return { width, height };
    };

    const cleanupThreeScene = () => {
        const ctx = state.threeScene;
        if (!ctx) return;
        if (ctx.animationId) {
            window.cancelAnimationFrame(ctx.animationId);
        }
        ctx.controls?.dispose();
        ctx.renderer?.dispose();
        if (viewport3dEl) viewport3dEl.innerHTML = '';
        state.threeScene = null;
    };

    const syncViewModeUi = () => {
        const is3d = state.viewMode === '3d';
        if (view2dBtn) {
            view2dBtn.classList.toggle('btn-light', !is3d);
            view2dBtn.classList.toggle('active', !is3d);
            view2dBtn.classList.toggle('btn-outline-light', is3d);
        }
        if (view3dBtn) {
            view3dBtn.classList.toggle('btn-light', is3d);
            view3dBtn.classList.toggle('active', is3d);
            view3dBtn.classList.toggle('btn-outline-light', !is3d);
        }
        if (viewModeHintEl) {
            viewModeHintEl.textContent = is3d
                ? 'Vista 3D visual: muros extruidos, APs y orientación básica.'
                : 'Vista 2D editable del plano, heatmap y elementos estructurales.';
        }

        const show2d = !is3d;
        if (imageEl) imageEl.style.display = show2d && (state.fileType === 'png' || state.fileType === 'svg') ? 'block' : 'none';
        if (pdfEl) pdfEl.style.display = show2d && state.fileType === 'pdf' ? 'block' : 'none';
        if (overlayEl) overlayEl.style.display = show2d && state.editable ? 'block' : 'none';
        if (wallsLayerEl) wallsLayerEl.style.display = show2d ? '' : 'none';
        if (heatCanvasEl) heatCanvasEl.style.display = show2d ? 'block' : 'none';
        if (viewport3dEl) viewport3dEl.style.display = is3d ? 'block' : 'none';
        if (is3d) hideSignalProbe();
    };

    const render3dScene = async () => {
        if (!viewport3dEl || state.viewMode !== '3d') return;
        const loader = window.loadITCityThree;
        if (typeof loader !== 'function') {
            if (noticeEl) {
                noticeEl.style.display = 'block';
                noticeEl.textContent = 'Three.js no está disponible en el bundle; no se pudo abrir la vista 3D.';
            }
            return;
        }

        let THREE_LIB;
        let OrbitControlsLib;
        try {
            ({ THREE: THREE_LIB, OrbitControls: OrbitControlsLib } = await loader());
        } catch {
            if (noticeEl) {
                noticeEl.style.display = 'block';
                noticeEl.textContent = 'No se pudo cargar el motor 3D en este momento.';
            }
            return;
        }

        cleanupThreeScene();

        const widthPx = Math.max(1, viewport3dEl.clientWidth || wrapEl.clientWidth || 800);
        const heightPx = Math.max(1, viewport3dEl.clientHeight || wrapEl.clientHeight || 600);
        const dims = estimatedPlanDimensions();
        const defaults = structureDefaults();
        const wallHeight = Number(defaults.wall_height_m) > 0 ? Number(defaults.wall_height_m) : 2.8;
        const doorHeight = Number(defaults.door_height_m) > 0 ? Number(defaults.door_height_m) : 2.1;
        const windowBase = Number(defaults.window_base_m) >= 0 ? Number(defaults.window_base_m) : 1.0;
        const windowHeight = Number(defaults.window_height_m) > 0 ? Number(defaults.window_height_m) : 1.2;
        const apMountHeight = Number(defaults.ap_mount_height_m) > 0 ? Number(defaults.ap_mount_height_m) : 2.6;
        const scene = new THREE_LIB.Scene();
        scene.background = new THREE_LIB.Color('#08111f');

        const camera = new THREE_LIB.PerspectiveCamera(50, widthPx / heightPx, 0.1, 5000);
        camera.position.set(dims.width * 0.9, Math.max(dims.width, dims.height) * 0.8, dims.height * 0.9);

        const renderer = new THREE_LIB.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setPixelRatio(window.devicePixelRatio || 1);
        renderer.setSize(widthPx, heightPx);
        viewport3dEl.appendChild(renderer.domElement);

        const controls = new OrbitControlsLib(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.enableZoom = true;
        controls.minDistance = 0.5;
        controls.maxDistance = Math.max(dims.width, dims.height) * 25;
        controls.zoomSpeed = 1.15;
        controls.target.set(0, 0, 0);

        scene.add(new THREE_LIB.AmbientLight('#ffffff', 1.3));
        const dirLight = new THREE_LIB.DirectionalLight('#dbeafe', 1.2);
        dirLight.position.set(dims.width, dims.width * 1.5, dims.height);
        scene.add(dirLight);

        const floorGroup = new THREE_LIB.Group();
        scene.add(floorGroup);

        const floorGeo = new THREE_LIB.PlaneGeometry(dims.width, dims.height);
        let floorMaterial = new THREE_LIB.MeshStandardMaterial({ color: '#dbe4f0', side: THREE_LIB.DoubleSide });
        if (state.fileType === 'png' || state.fileType === 'svg') {
            const texture = new THREE_LIB.TextureLoader().load(imageEl?.src || '', () => renderer.render(scene, camera));
            texture.colorSpace = THREE_LIB.SRGBColorSpace;
            floorMaterial = new THREE_LIB.MeshStandardMaterial({ map: texture, side: THREE_LIB.DoubleSide });
        }
        const floorMesh = new THREE_LIB.Mesh(floorGeo, floorMaterial);
        floorMesh.rotation.x = -Math.PI / 2;
        floorGroup.add(floorMesh);

        const grid = new THREE_LIB.GridHelper(Math.max(dims.width, dims.height), 20, '#334155', '#1e293b');
        scene.add(grid);

        const toWorld = (xPercent, yPercent) => ({
            x: ((Number(xPercent || 0) / 100) - 0.5) * dims.width,
            z: ((Number(yPercent || 0) / 100) - 0.5) * dims.height,
        });

        const addCoverageRings = ({
            group,
            radius,
            baseSignal,
            aperture = null,
            azimuthRad = 0,
            mountOrientation = 'ceiling',
            mountHeight = 0,
        }) => {
            const maxRadius = Math.max(0.6, Number(radius || 0));
            const bands = [
                { inner: 0.00, outer: 0.28, signalDrop: 4, opacity: 0.22 },
                { inner: 0.28, outer: 0.52, signalDrop: 12, opacity: 0.20 },
                { inner: 0.52, outer: 0.76, signalDrop: 24, opacity: 0.17 },
                { inner: 0.76, outer: 1.00, signalDrop: 36, opacity: 0.14 },
            ];

            const mount = String(mountOrientation || 'ceiling');
            const isWallMount = mount === 'wall-horizontal' || mount === 'wall-vertical';

            bands.forEach((band, index) => {
                const ring = new THREE_LIB.Mesh(
                    new THREE_LIB.RingGeometry(
                        maxRadius * band.inner,
                        maxRadius * band.outer,
                        72,
                        1,
                        aperture ? -aperture / 2 : 0,
                        aperture || (Math.PI * 2),
                    ),
                    new THREE_LIB.MeshStandardMaterial({
                        color: signalColorForValue(baseSignal - band.signalDrop),
                        transparent: true,
                        opacity: band.opacity,
                        side: THREE_LIB.DoubleSide,
                    })
                );
                if (isWallMount) {
                    ring.position.y = Number(mountHeight || 0);
                    ring.rotation.x = 0;
                    ring.rotation.y = -azimuthRad;
                    ring.rotation.z = 0;
                } else {
                    ring.position.y = 0.02 + (index * 0.003);
                    ring.rotation.x = -Math.PI / 2;
                    ring.rotation.z = -azimuthRad;
                }
                group.add(ring);
            });
        };

        state.walls.forEach((wall) => {
            const start = toWorld(wall.x1_percent, wall.y1_percent);
            const end = toWorld(wall.x2_percent, wall.y2_percent);
            const length = Math.hypot(end.x - start.x, end.z - start.z);
            if (length < 0.05) return;
            const materialKey = String(wall.material || 'drywall');
            const meta = materialMeta(materialKey);
            const height = materialKey === 'door' ? doorHeight : (materialKey === 'window' ? windowHeight : wallHeight);
            const y = materialKey === 'window' ? (windowBase + (windowHeight / 2)) : (height / 2);
            const thickness = materialKey === 'door' || materialKey === 'window' ? 0.1 : 0.18;
            const geometry = new THREE_LIB.BoxGeometry(length, height, thickness);
            const material = new THREE_LIB.MeshStandardMaterial({
                color: meta.color,
                transparent: materialKey === 'window',
                opacity: materialKey === 'window' ? 0.55 : 0.95,
            });
            const mesh = new THREE_LIB.Mesh(geometry, material);
            mesh.position.set((start.x + end.x) / 2, y, (start.z + end.z) / 2);
            mesh.rotation.y = -Math.atan2(end.z - start.z, end.x - start.x);
            scene.add(mesh);
        });

        state.points.forEach((point) => {
            const pos = toWorld(point.x_percent, point.y_percent);
            const group = new THREE_LIB.Group();
            const base = new THREE_LIB.Mesh(
                new THREE_LIB.CylinderGeometry(0.18, 0.18, 0.12, 24),
                new THREE_LIB.MeshStandardMaterial({ color: '#60a5fa' })
            );
            const pointMountHeight = Number(point.mount_height_m) > 0 ? Number(point.mount_height_m) : apMountHeight;
            const mountOrientation = String(point.mount_orientation || 'ceiling');
            const azimuthRad = (Number(point.azimuth_deg || 0) * Math.PI) / 180;
            const tiltRad = (Number(point.tilt_deg || 0) * Math.PI) / 180;
            const isWallMount = mountOrientation === 'wall-horizontal' || mountOrientation === 'wall-vertical';

            base.position.y = pointMountHeight;
            if (mountOrientation === 'wall-horizontal') {
                base.rotation.z = Math.PI / 2;
                base.rotation.y = -azimuthRad;
            } else if (mountOrientation === 'wall-vertical') {
                base.rotation.x = Math.PI / 2;
                base.rotation.y = -azimuthRad;
            } else if (mountOrientation === 'desktop') {
                base.position.y = Math.max(0.08, pointMountHeight);
            }
            group.add(base);

            const pattern = String(point.radiation_pattern || 'omni-donut');
            const coverageRadius = Number(point.radius_meters || 0) > 0 ? Number(point.radius_meters) : Math.max(dims.width, dims.height) * ((Number(point.radius_percent || 12) / 100) * 0.35);
            const baseSignal = Number(point.signal_dbm ?? -55);
            const coverageColor = signalColorForValue(baseSignal);

            if (pattern === 'sector-120' || pattern === 'directional-60') {
                const aperture = pattern === 'directional-60' ? Math.PI / 3 : (2 * Math.PI / 3);
                const cone = new THREE_LIB.Mesh(
                    new THREE_LIB.ConeGeometry(Math.max(0.8, coverageRadius * 0.65), Math.max(1.2, coverageRadius * 0.9), 40, 1, true, -aperture / 2, aperture),
                    new THREE_LIB.MeshStandardMaterial({ color: coverageColor, transparent: true, opacity: 0.2, side: THREE_LIB.DoubleSide })
                );
                cone.position.y = isWallMount
                    ? Math.max(0.5, pointMountHeight)
                    : Math.max(0.8, pointMountHeight - Math.max(0.5, coverageRadius * 0.15));
                cone.rotation.z = Math.PI / 2 + (isWallMount ? 0 : tiltRad);
                cone.rotation.y = -azimuthRad;
                if (isWallMount) {
                    cone.rotation.x = tiltRad;
                }
                group.add(cone);

                addCoverageRings({
                    group,
                    radius: coverageRadius,
                    baseSignal,
                    aperture,
                    azimuthRad,
                    mountOrientation,
                    mountHeight: pointMountHeight,
                });
            } else {
                const dome = new THREE_LIB.Mesh(
                    new THREE_LIB.SphereGeometry(Math.max(0.8, coverageRadius), 28, 18, 0, Math.PI * 2, 0, Math.PI / 2),
                    new THREE_LIB.MeshStandardMaterial({ color: coverageColor, transparent: true, opacity: 0.12, side: THREE_LIB.DoubleSide })
                );
                dome.position.y = pointMountHeight;
                if (isWallMount) {
                    dome.rotation.z = Math.PI / 2;
                    dome.rotation.y = -azimuthRad;
                }
                group.add(dome);

                addCoverageRings({
                    group,
                    radius: coverageRadius,
                    baseSignal,
                    azimuthRad,
                    mountOrientation,
                    mountHeight: pointMountHeight,
                });
            }

            group.position.set(pos.x, 0, pos.z);
            scene.add(group);
        });

        const animate = () => {
            if (!state.threeScene || state.threeScene.renderer !== renderer) return;
            controls.update();
            renderer.render(scene, camera);
            state.threeScene.animationId = window.requestAnimationFrame(animate);
        };

        state.threeScene = { scene, camera, renderer, controls, animationId: null };
        animate();
    };

    const setViewMode = (mode) => {
        state.viewMode = mode === '3d' ? '3d' : '2d';
        syncViewModeUi();
        if (state.viewMode === '3d') {
            render3dScene();
        } else {
            cleanupThreeScene();
            renderPoints();
        }
    };

    const toPercent = (clientX, clientY) => {
        const rect = overlayEl.getBoundingClientRect();
        if (!rect.width || !rect.height) return null;
        return {
            x: Math.max(0, Math.min(100, ((clientX - rect.left) / rect.width) * 100)),
            y: Math.max(0, Math.min(100, ((clientY - rect.top) / rect.height) * 100)),
        };
    };

    const syncOverlayGeometry = () => {
        if (state.fileType !== 'png' && state.fileType !== 'svg') return;
        const w = imageEl?.clientWidth || 0;
        const h = imageEl?.clientHeight || 0;
        if (!w || !h) return;

        overlayEl.style.width = `${w}px`;
        overlayEl.style.height = `${h}px`;
        overlayEl.style.left = `${imageEl.offsetLeft}px`;
        overlayEl.style.top = `${imageEl.offsetTop}px`;
        overlayEl.style.position = 'absolute';

        wallsLayerEl.style.width = `${w}px`;
        wallsLayerEl.style.height = `${h}px`;
        wallsLayerEl.style.left = `${imageEl.offsetLeft}px`;
        wallsLayerEl.style.top = `${imageEl.offsetTop}px`;
        wallsLayerEl.style.position = 'absolute';

        heatCanvasEl.width = Math.max(1, Math.floor(w));
        heatCanvasEl.height = Math.max(1, Math.floor(h));
        heatCanvasEl.style.width = `${w}px`;
        heatCanvasEl.style.height = `${h}px`;
        heatCanvasEl.style.left = `${imageEl.offsetLeft}px`;
        heatCanvasEl.style.top = `${imageEl.offsetTop}px`;
        heatCanvasEl.style.position = 'absolute';

        applyZoomTransform();
        renderPoints();
    };

    const WALL_SNAP_STEP_PERCENT = 2;
    const AP_WALL_SNAP_DISTANCE_PERCENT = 1.8;
    const clampPercent = (value) => Math.max(0, Math.min(100, Number(value || 0)));
    const snapStepPercent = (axis = 'x', fallback = WALL_SNAP_STEP_PERCENT) => {
        const scale = planScale();
        const meters = axis === 'y' ? Number(scale.height_m) : Number(scale.width_m);
        if (Number.isFinite(meters) && meters > 0) {
            return 100 / meters;
        }
        return fallback;
    };

    const snapPercent = (value, axis = 'x', step = null) => {
        const numeric = Number(value || 0);
        const resolvedStep = step ?? snapStepPercent(axis);
        if (!Number.isFinite(numeric) || resolvedStep <= 0) return clampPercent(numeric);
        return clampPercent(Math.round(numeric / resolvedStep) * resolvedStep);
    };

    const hasMetricScale = () => {
        const scale = planScale();
        return Number(scale.width_m) > 0 && Number(scale.height_m) > 0;
    };

    const openingWidthMetersForMaterial = (material) => {
        const key = String(material || '').toLowerCase();
        if (key === 'door') {
            const value = doorWidthInputEl?.value ? Number(doorWidthInputEl.value) : null;
            return Number.isFinite(value) && value > 0 ? value : null;
        }
        if (key === 'window') {
            const value = windowWidthInputEl?.value ? Number(windowWidthInputEl.value) : null;
            return Number.isFinite(value) && value > 0 ? value : null;
        }
        return null;
    };

    const resolveOrthogonalEndpoint = (start, end) => {
        if (!start || !end) return end;
        const scale = planScale();
        const dxMeters = Number(scale.width_m) > 0
            ? ((Number(end.x || 0) - Number(start.x || 0)) / 100) * Number(scale.width_m)
            : Number(end.x || 0) - Number(start.x || 0);
        const dyMeters = Number(scale.height_m) > 0
            ? ((Number(end.y || 0) - Number(start.y || 0)) / 100) * Number(scale.height_m)
            : Number(end.y || 0) - Number(start.y || 0);

        if (Math.abs(dxMeters) >= Math.abs(dyMeters)) {
            return { x: Number(end.x || 0), y: Number(start.y || 0), axis: 'x' };
        }

        return { x: Number(start.x || 0), y: Number(end.y || 0), axis: 'y' };
    };

    const resolveFixedLengthEndpoint = (start, end, fixedMeters) => {
        if (!hasMetricScale() || !(Number(fixedMeters) > 0)) return end;
        const scale = planScale();
        const startMeters = {
            x: (Number(start.x || 0) / 100) * Number(scale.width_m),
            y: (Number(start.y || 0) / 100) * Number(scale.height_m),
        };
        const endMeters = {
            x: (Number(end.x || 0) / 100) * Number(scale.width_m),
            y: (Number(end.y || 0) / 100) * Number(scale.height_m),
        };
        const dx = endMeters.x - startMeters.x;
        const dy = endMeters.y - startMeters.y;
        const length = Math.hypot(dx, dy);
        const safeLength = length > 0.0001 ? length : 1;
        const unitX = dx / safeLength;
        const unitY = dy / safeLength;
        const finalMeters = {
            x: startMeters.x + (unitX * Number(fixedMeters)),
            y: startMeters.y + (unitY * Number(fixedMeters)),
        };

        return {
            x: (finalMeters.x / Number(scale.width_m)) * 100,
            y: (finalMeters.y / Number(scale.height_m)) * 100,
        };
    };

    const buildStructureSegment = (start, end, material) => {
        if (!start || !end) return null;

        const shouldLockOrthogonal = isOrthogonalLockEnabled();
        let resolvedEnd = { x: Number(end.x || 0), y: Number(end.y || 0) };
        if (shouldLockOrthogonal) {
            resolvedEnd = resolveOrthogonalEndpoint(start, resolvedEnd);
        }

        const fixedLengthMeters = openingWidthMetersForMaterial(material);
        if (fixedLengthMeters !== null) {
            resolvedEnd = resolveFixedLengthEndpoint(start, resolvedEnd, fixedLengthMeters);
        }

        const meta = materialMeta(material);
        const wall = {
            x1_percent: snapPercent(start.x, 'x'),
            y1_percent: snapPercent(start.y, 'y'),
            x2_percent: snapPercent(resolvedEnd.x, 'x'),
            y2_percent: snapPercent(resolvedEnd.y, 'y'),
            material,
            loss_db: meta.loss_db,
        };

        if (fixedLengthMeters !== null && hasMetricScale()) {
            wall.opening_width_m = Number(fixedLengthMeters.toFixed(2));
        }

        return wall;
    };

    const pxPoint = (point, width, height) => ({
        x: (Number(point.x_percent || 0) / 100) * width,
        y: (Number(point.y_percent || 0) / 100) * height,
    });

    const addSymbolAt = (pos, symbolKey = selectedSymbolKey()) => {
        if (!pos) return;
        const symbol = SYMBOL_LIBRARY[symbolKey] || SYMBOL_LIBRARY.rack;
        pushUndoSnapshot();
        state.points.push({
            node_id: null,
            x_percent: snapPercent(pos.x, 'x'),
            y_percent: snapPercent(pos.y, 'y'),
            layer: 'access-point',
            item_type: `symbol:${symbolKey}`,
            label: symbol.label,
            symbol_key: symbolKey,
            symbol_size_m: Number(selectedSymbolSizeMeters().toFixed(2)),
            rotation_deg: 0,
            signal_dbm: null,
            radius_percent: null,
            radius_meters: null,
        });
    };

    const pixelsPerMeter = (width, height) => {
        const scale = planScale();
        const ratios = [];
        if (Number(scale.width_m) > 0) ratios.push(width / Number(scale.width_m));
        if (Number(scale.height_m) > 0) ratios.push(height / Number(scale.height_m));
        if (!ratios.length) return null;
        return ratios.reduce((sum, value) => sum + value, 0) / ratios.length;
    };

    const computeRadiusPx = (point, width, height) => {
        const ppm = pixelsPerMeter(width, height);
        if (ppm && Number(point.radius_meters || 0) > 0) {
            return Math.max(24, Number(point.radius_meters) * ppm);
        }
        return Math.max(24, (Number(point.radius_percent || 12) / 100) * Math.max(width, height) * 0.75);
    };

    const normalizeDegrees = (value) => {
        const normalized = Number(value || 0) % 360;
        return normalized < 0 ? normalized + 360 : normalized;
    };

    const angleDifference = (a, b) => {
        const diff = Math.abs(normalizeDegrees(a) - normalizeDegrees(b));
        return diff > 180 ? 360 - diff : diff;
    };

    const orientationLossBetween = (point, source, target) => {
        const pattern = String(point.radiation_pattern || 'omni-donut');
        const mount = String(point.mount_orientation || 'ceiling');
        const azimuth = normalizeDegrees(point.azimuth_deg || 0);
        const angleToTarget = normalizeDegrees(Math.atan2(target.y - source.y, target.x - source.x) * (180 / Math.PI));
        const diff = angleDifference(angleToTarget, azimuth);

        let loss = 0;

        if (pattern === 'sector-120') {
            loss += diff <= 60 ? (diff / 60) * 3 : 18 + ((diff - 60) / 120) * 8;
        } else if (pattern === 'directional-60') {
            loss += diff <= 30 ? (diff / 30) * 4 : 24 + ((diff - 30) / 150) * 10;
        }

        if (mount === 'wall-horizontal' || mount === 'wall-vertical') {
            if (diff > 90) loss += 6;
        } else if (mount === 'desktop') {
            loss += 1.5;
        }

        return loss;
    };

    const isWallSnapEnabled = () => snapApWallsInputEl ? snapApWallsInputEl.checked !== false : true;

    const projectPointToSegmentPercent = (point, segmentStart, segmentEnd) => {
        const x1 = Number(segmentStart.x || 0);
        const y1 = Number(segmentStart.y || 0);
        const x2 = Number(segmentEnd.x || 0);
        const y2 = Number(segmentEnd.y || 0);
        const px = Number(point.x || 0);
        const py = Number(point.y || 0);

        const dx = x2 - x1;
        const dy = y2 - y1;
        const lenSq = (dx * dx) + (dy * dy);
        if (lenSq < 1e-6) {
            return {
                x: x1,
                y: y1,
                t: 0,
                distance: Math.hypot(px - x1, py - y1),
                dx,
                dy,
            };
        }

        const t = Math.max(0, Math.min(1, (((px - x1) * dx) + ((py - y1) * dy)) / lenSq));
        const projX = x1 + (t * dx);
        const projY = y1 + (t * dy);
        return {
            x: projX,
            y: projY,
            t,
            distance: Math.hypot(px - projX, py - projY),
            dx,
            dy,
        };
    };

    const findNearestWallSnap = (pos, preferredAzimuthDeg = 0) => {
        if (!pos || !Array.isArray(state.walls) || !state.walls.length) return null;

        let best = null;

        state.walls.forEach((wall) => {
            const material = String(wall.material || 'wall').toLowerCase();
            if (material === 'door' || material === 'window') return;

            const start = {
                x: Number(wall.x1_percent || 0),
                y: Number(wall.y1_percent || 0),
            };
            const end = {
                x: Number(wall.x2_percent || 0),
                y: Number(wall.y2_percent || 0),
            };

            const projection = projectPointToSegmentPercent(pos, start, end);
            if (!projection || !Number.isFinite(projection.distance)) return;
            if (projection.distance > AP_WALL_SNAP_DISTANCE_PERCENT) return;

            const absDx = Math.abs(projection.dx);
            const absDy = Math.abs(projection.dy);
            const mountOrientation = absDx >= absDy ? 'wall-horizontal' : 'wall-vertical';

            const wallAngleDeg = normalizeDegrees(Math.atan2(projection.dy, projection.dx) * (180 / Math.PI));
            const normalA = normalizeDegrees(wallAngleDeg + 90);
            const normalB = normalizeDegrees(wallAngleDeg - 90);
            const azimuthDeg = angleDifference(preferredAzimuthDeg, normalA) <= angleDifference(preferredAzimuthDeg, normalB)
                ? normalA
                : normalB;

            const candidate = {
                x: snapPercent(projection.x, 'x'),
                y: snapPercent(projection.y, 'y'),
                mountOrientation,
                azimuthDeg,
                distance: projection.distance,
            };

            if (!best || candidate.distance < best.distance) {
                best = candidate;
            }
        });

        return best;
    };

    const applyWallSnapToAccessPoint = (point, targetPos) => {
        if (!point || !targetPos || !isAccessPointItem(point)) return;

        const snappedTarget = {
            x: snapPercent(targetPos.x, 'x'),
            y: snapPercent(targetPos.y, 'y'),
        };

        if (!isWallSnapEnabled()) {
            point.x_percent = clampPercent(snappedTarget.x);
            point.y_percent = clampPercent(snappedTarget.y);
            return;
        }

        const preferredAzimuth = Number(point.azimuth_deg || 0);
        const wallSnap = findNearestWallSnap(snappedTarget, preferredAzimuth);

        if (wallSnap) {
            point.x_percent = wallSnap.x;
            point.y_percent = wallSnap.y;
            point.mount_orientation = wallSnap.mountOrientation;
            point.azimuth_deg = wallSnap.azimuthDeg;
            return;
        }

        point.x_percent = clampPercent(snappedTarget.x);
        point.y_percent = clampPercent(snappedTarget.y);
    };

    const findSelectedApNode = () => {
        const selectedNodeId = nodeSelectEl?.value ? Number(nodeSelectEl.value) : null;
        return state.apNodes.find((node) => Number(node.id) === selectedNodeId) || null;
    };

    const applyRfDefaults = (defaults = {}) => {
        if (radiusMetersInputEl && defaults.radius_meters != null) radiusMetersInputEl.value = defaults.radius_meters;
        if (patternSelectEl && defaults.radiation_pattern) patternSelectEl.value = defaults.radiation_pattern;
        if (mountOrientationEl && defaults.mount_orientation) mountOrientationEl.value = defaults.mount_orientation;
        if (mountHeightInputEl && defaults.mount_height_m != null) mountHeightInputEl.value = defaults.mount_height_m;
        if (azimuthInputEl && defaults.azimuth_deg != null) azimuthInputEl.value = defaults.azimuth_deg;
        if (tiltInputEl && defaults.tilt_deg != null) tiltInputEl.value = defaults.tilt_deg;
    };

    const selectedPoint = () => {
        if (!Number.isInteger(state.selectedPointIndex)) return null;
        return state.points[state.selectedPointIndex] || null;
    };

    const nodeConnectionsUrl = (nodeId) => `/red/nodos/${nodeId}`;

    const floorPlanOwnershipBadge = (ownership) => {
        const toneMap = {
            success: 'success',
            danger: 'danger',
            secondary: 'secondary',
        };
        const tone = toneMap[ownership?.tone] || 'secondary';
        const label = ownership?.label || 'Sin clasificar';

        return `<span class="badge text-bg-${tone}">${esc(label)}</span>`;
    };

    const clearNodeInsights = (message = 'Selecciona un AP ligado a un nodo.') => {
        state.nodeInsightsTargetId = null;
        if (!nodeInsightsCardEl || !nodeInsightsTitleEl || !nodeInsightsBodyEl) return;
        nodeInsightsCardEl.style.display = 'none';
        nodeInsightsTitleEl.textContent = message;
        nodeInsightsBodyEl.innerHTML = '<span class="text-muted">Sin información de conectividad.</span>';
    };

    const renderNodeInsights = (nodeMeta, snapshot, { loading = false, error = null } = {}) => {
        if (!nodeInsightsCardEl || !nodeInsightsTitleEl || !nodeInsightsBodyEl) return;

        nodeInsightsCardEl.style.display = 'block';
        nodeInsightsTitleEl.textContent = nodeMeta
            ? `${nodeMeta.name || 'Nodo'}${nodeMeta.code ? ` · ${nodeMeta.code}` : ''}`
            : 'Nodo asociado';

        if (error) {
            nodeInsightsBodyEl.innerHTML = `<span class="text-danger">${esc(error)}</span>`;
            return;
        }

        if (loading || !snapshot) {
            nodeInsightsBodyEl.innerHTML = '<span class="text-muted">Cargando dispositivos conectados...</span>';
            return;
        }

        const assets = Array.isArray(snapshot.associated_assets) ? snapshot.associated_assets : [];
        const observed = Array.isArray(snapshot.observed_devices) ? snapshot.observed_devices : [];
        const related = Array.isArray(snapshot.related_nodes) ? snapshot.related_nodes : [];
        const summary = snapshot.summary || {};

        nodeInsightsBodyEl.innerHTML = `
            <div class="d-flex flex-wrap gap-1 mb-2">
                <span class="badge text-bg-primary">Activos ${Number(summary.associated_assets_count || assets.length)}</span>
                <span class="badge text-bg-dark">Observados ${Number(summary.observed_devices_count || observed.length)}</span>
                <span class="badge text-bg-success">Propios ${Number(summary.managed_observed_devices_count || 0)}</span>
                <span class="badge text-bg-danger">Ajenos ${Number(summary.external_observed_devices_count || 0)}</span>
            </div>
            ${assets.length ? `
                <div class="small fw-semibold text-dark mb-1">Inventariados</div>
                <div class="small mb-2">
                    ${assets.slice(0, 5).map((asset) => `
                        <div class="border rounded-2 p-2 mb-1 bg-white">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <span>${esc(asset.label || 'Activo')}</span>
                                ${floorPlanOwnershipBadge(asset.ownership)}
                            </div>
                            <div class="text-muted">${[asset.hostname, asset.domain_name, asset.equipment_type].filter(Boolean).map(esc).join(' · ') || 'Sin datos adicionales'}</div>
                        </div>
                    `).join('')}
                </div>
            ` : ''}
            ${observed.length ? `
                <div class="small fw-semibold text-dark mb-1">Descubiertos</div>
                <div class="small mb-2">
                    ${observed.slice(0, 5).map((device) => `
                        <div class="border rounded-2 p-2 mb-1 bg-white">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <span>${esc(device.hostname || device.mac_address || device.ip_address || 'Dispositivo')}</span>
                                ${floorPlanOwnershipBadge(device.ownership)}
                            </div>
                            <div class="text-muted">${[
                                device.ip_address,
                                device.mac_address,
                                device.domain_name,
                                device.switch_port ? `Puerto ${device.switch_port}` : null,
                                device.ssid ? `SSID ${device.ssid}` : null,
                            ].filter(Boolean).map(esc).join(' · ') || 'Sin datos adicionales'}</div>
                        </div>
                    `).join('')}
                </div>
            ` : ''}
            ${related.length ? `
                <div class="small fw-semibold text-dark mb-1">Relaciones</div>
                <div class="small text-muted mb-2">${related.slice(0, 4).map((item) => `${esc(item.name || 'Nodo')} (${esc(item.direction === 'incoming' ? 'entrada' : 'salida')})`).join(' · ')}</div>
            ` : ''}
            <div class="d-grid gap-1">
                <a class="btn btn-sm btn-outline-primary" href="/nodos/${nodeMeta?.id}">Ver ficha del nodo</a>
                <a class="btn btn-sm btn-outline-secondary" href="/admin?edit_node=${nodeMeta?.id}">Configurar nodo</a>
            </div>
        `;
    };

    const loadSelectedPointNodeInsights = async ({ force = false } = {}) => {
        const point = selectedPoint();
        if (!point || !isAccessPointItem(point) || !point.node_id) {
            clearNodeInsights();
            return null;
        }

        const nodeId = Number(point.node_id);
        const nodeMeta = state.apNodes.find((node) => Number(node.id) === nodeId) || { id: nodeId, name: point.label || `Nodo ${nodeId}` };
        state.nodeInsightsTargetId = nodeId;

        if (!force && state.nodeSnapshotCache.has(nodeId)) {
            const snapshot = state.nodeSnapshotCache.get(nodeId);
            renderNodeInsights(nodeMeta, snapshot);
            return snapshot;
        }

        if (state.nodeSnapshotPending.has(nodeId)) {
            renderNodeInsights(nodeMeta, null, { loading: true });
            return null;
        }

        state.nodeSnapshotPending.add(nodeId);
        renderNodeInsights(nodeMeta, null, { loading: true });

        try {
            const response = await fetch(nodeConnectionsUrl(nodeId), {
                headers: { Accept: 'application/json' },
            });
            const data = await response.json();
            if (!response.ok || !data?.ok) {
                throw new Error(data?.message || `HTTP ${response.status}`);
            }

            const snapshot = data?.node?.connection_snapshot || null;
            if (snapshot) {
                state.nodeSnapshotCache.set(nodeId, snapshot);
            }
            if (state.nodeInsightsTargetId === nodeId) {
                renderNodeInsights(nodeMeta, snapshot);
            }

            return snapshot;
        } catch (error) {
            if (state.nodeInsightsTargetId === nodeId) {
                renderNodeInsights(nodeMeta, null, { error: error?.message || 'No se pudo cargar la conectividad.' });
            }
            return null;
        } finally {
            state.nodeSnapshotPending.delete(nodeId);
        }
    };

    const clearSelectedPoint = () => {
        state.selectedPointIndex = null;
        clearNodeInsights();
    };

    const clearSelectedWall = () => {
        state.selectedWallIndex = null;
    };

    const clearSelectedRoom = () => {
        state.selectedRoomId = null;
    };

    const createRoomId = () => `room-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;

    const roomById = (roomId) => state.rooms.find((room) => room.id === roomId) || null;

    const roomDisplayName = (roomId) => roomById(roomId)?.name || 'Espacio';

    const upsertRoom = (room) => {
        if (!room?.id) return;
        const index = state.rooms.findIndex((item) => item.id === room.id);
        if (index >= 0) {
            state.rooms[index] = { ...state.rooms[index], ...room };
            return;
        }
        state.rooms.push({ id: room.id, name: room.name || null });
    };

    const cloneEditorSnapshot = () => ({
        points: JSON.parse(JSON.stringify(state.points || [])),
        walls: JSON.parse(JSON.stringify(state.walls || [])),
        rooms: JSON.parse(JSON.stringify(state.rooms || [])),
        pendingWallStart: state.pendingWallStart ? { ...state.pendingWallStart } : null,
        selectedPointIndex: Number.isInteger(state.selectedPointIndex) ? state.selectedPointIndex : null,
        selectedWallIndex: Number.isInteger(state.selectedWallIndex) ? state.selectedWallIndex : null,
        selectedRoomId: state.selectedRoomId || null,
        scaleWidth: scaleWidthInputEl?.value ?? '',
        scaleHeight: scaleHeightInputEl?.value ?? '',
        wallMaterial: wallMaterialEl?.value ?? 'drywall',
        orthogonalLock: isOrthogonalLockEnabled(),
    });

    const pushUndoSnapshot = () => {
        if (state.isRestoringUndo) return;
        state.undoStack.push(cloneEditorSnapshot());
        if (state.undoStack.length > 100) {
            state.undoStack.shift();
        }
        state.redoStack = [];
    };

    const restoreSnapshot = (snapshot) => {
        if (!snapshot) return;
        state.isRestoringUndo = true;
        state.points = Array.isArray(snapshot.points) ? JSON.parse(JSON.stringify(snapshot.points)) : [];
        state.walls = Array.isArray(snapshot.walls) ? JSON.parse(JSON.stringify(snapshot.walls)) : [];
        state.rooms = Array.isArray(snapshot.rooms) ? JSON.parse(JSON.stringify(snapshot.rooms)) : [];
        state.pendingWallStart = snapshot.pendingWallStart ? { ...snapshot.pendingWallStart } : null;
        state.selectedPointIndex = Number.isInteger(snapshot.selectedPointIndex) ? snapshot.selectedPointIndex : null;
        state.selectedWallIndex = Number.isInteger(snapshot.selectedWallIndex) ? snapshot.selectedWallIndex : null;
        state.selectedRoomId = snapshot.selectedRoomId || null;
        if (scaleWidthInputEl) scaleWidthInputEl.value = snapshot.scaleWidth ?? '';
        if (scaleHeightInputEl) scaleHeightInputEl.value = snapshot.scaleHeight ?? '';
        if (wallMaterialEl) wallMaterialEl.value = snapshot.wallMaterial || 'drywall';
        if (orthogonalLockInputEl) orthogonalLockInputEl.checked = snapshot.orthogonalLock !== false;
        state.isRestoringUndo = false;
        syncScaleSummary();
        renderPoints();
    };

    const undoLastChange = () => {
        if (!state.undoStack.length) return;
        const currentSnapshot = cloneEditorSnapshot();
        const snapshot = state.undoStack.pop();
        state.redoStack.push(currentSnapshot);
        if (state.redoStack.length > 100) {
            state.redoStack.shift();
        }
        restoreSnapshot(snapshot);
    };

    const redoLastChange = () => {
        if (!state.redoStack.length) return;
        const currentSnapshot = cloneEditorSnapshot();
        const snapshot = state.redoStack.pop();
        state.undoStack.push(currentSnapshot);
        if (state.undoStack.length > 100) {
            state.undoStack.shift();
        }
        restoreSnapshot(snapshot);
    };

    const loadFormFromSelectedPoint = () => {
        const point = selectedPoint();
        if (!point) {
            clearNodeInsights();
            return;
        }

        if (!isAccessPointItem(point)) {
            if (nodeSelectEl) nodeSelectEl.value = '';
            clearNodeInsights('Selecciona un AP ligado a un nodo.');
            return;
        }

        if (nodeSelectEl) nodeSelectEl.value = point.node_id != null ? String(point.node_id) : '';
        if (layerSelectEl) layerSelectEl.value = point.layer || 'access-point';
        if (signalInputEl) signalInputEl.value = point.signal_dbm != null ? point.signal_dbm : '';
        if (radiusInputEl) radiusInputEl.value = point.radius_percent != null ? point.radius_percent : 12;
        if (radiusMetersInputEl) radiusMetersInputEl.value = point.radius_meters != null ? point.radius_meters : '';
        if (patternSelectEl) patternSelectEl.value = point.radiation_pattern || 'omni-donut';
        if (mountOrientationEl) mountOrientationEl.value = point.mount_orientation || 'ceiling';
        if (mountHeightInputEl) mountHeightInputEl.value = point.mount_height_m != null ? point.mount_height_m : '';
        if (azimuthInputEl) azimuthInputEl.value = point.azimuth_deg != null ? point.azimuth_deg : 0;
        if (tiltInputEl) tiltInputEl.value = point.tilt_deg != null ? point.tilt_deg : 0;
        loadSelectedPointNodeInsights();
    };

    const applyFormToSelectedPoint = () => {
        const point = selectedPoint();
        if (!point) return false;
        if (!isAccessPointItem(point)) return false;

        const selectedNodeId = nodeSelectEl?.value ? Number(nodeSelectEl.value) : null;
        const selectedNode = state.apNodes.find((node) => Number(node.id) === selectedNodeId) || null;

        point.node_id = selectedNodeId;
        point.layer = layerSelectEl?.value || 'access-point';
        point.item_type = 'access-point';
        point.label = selectedNode?.name || point.label || 'AP';
        point.signal_dbm = signalInputEl?.value ? Number(signalInputEl.value) : null;
        point.radius_percent = radiusInputEl?.value ? Number(radiusInputEl.value) : 12;
        point.radius_meters = radiusMetersInputEl?.value ? Number(radiusMetersInputEl.value) : null;
        point.radiation_pattern = patternSelectEl?.value || 'omni-donut';
        point.mount_orientation = mountOrientationEl?.value || 'ceiling';
        point.mount_height_m = mountHeightInputEl?.value ? Number(mountHeightInputEl.value) : null;
        point.azimuth_deg = azimuthInputEl?.value ? Number(azimuthInputEl.value) : 0;
        point.tilt_deg = tiltInputEl?.value ? Number(tiltInputEl.value) : 0;

        return true;
    };

    const selectPointByIndex = (index) => {
        if (!Number.isInteger(index) || !state.points[index]) {
            clearSelectedPoint();
            return;
        }
        state.selectedPointIndex = index;
        loadFormFromSelectedPoint();
    };

    const hideContextMenu = () => {
        if (!contextMenuEl) return;
        contextMenuEl.style.display = 'none';
        contextMenuEl.innerHTML = '';
        state.contextMenu = { x: null, y: null };
    };

    const addAccessPointAt = (pos) => {
        if (!pos) return;
        pushUndoSnapshot();
        const selectedNodeId = nodeSelectEl?.value ? Number(nodeSelectEl.value) : null;
        const selectedNode = state.apNodes.find((node) => Number(node.id) === selectedNodeId) || null;
        const point = {
            node_id: selectedNodeId,
            x_percent: snapPercent(pos.x, 'x'),
            y_percent: snapPercent(pos.y, 'y'),
            layer: layerSelectEl?.value || 'access-point',
            item_type: 'access-point',
            label: selectedNode?.name || 'AP',
            signal_dbm: signalInputEl?.value ? Number(signalInputEl.value) : null,
            radius_percent: radiusInputEl?.value ? Number(radiusInputEl.value) : 12,
            radius_meters: radiusMetersInputEl?.value ? Number(radiusMetersInputEl.value) : null,
            radiation_pattern: patternSelectEl?.value || 'omni-donut',
            mount_orientation: mountOrientationEl?.value || 'ceiling',
            mount_height_m: mountHeightInputEl?.value ? Number(mountHeightInputEl.value) : null,
            azimuth_deg: azimuthInputEl?.value ? Number(azimuthInputEl.value) : 0,
            tilt_deg: tiltInputEl?.value ? Number(tiltInputEl.value) : 0,
        };

        applyWallSnapToAccessPoint(point, { x: pos.x, y: pos.y });
        state.points.push(point);
    };

    const startStructureAt = (pos, mode) => {
        if (!pos || !editModeEl) return;
        pushUndoSnapshot();
        editModeEl.value = mode;
        editModeEl.dispatchEvent(new Event('change'));
        state.pendingWallStart = {
            x_percent: snapPercent(pos.x, 'x'),
            y_percent: snapPercent(pos.y, 'y'),
        };
    };

    const startWallWithMaterialAt = (pos, material) => {
        if (!pos) return;
        if (wallMaterialEl) wallMaterialEl.value = material;
        startStructureAt(pos, 'wall');
    };

    const completeStructureAt = (pos) => {
        if (!pos || !state.pendingWallStart) return;
        pushUndoSnapshot();
        const material = selectedStructureMaterial();
        const wall = buildStructureSegment(
            { x: Number(state.pendingWallStart.x_percent), y: Number(state.pendingWallStart.y_percent) },
            { x: Number(pos.x), y: Number(pos.y) },
            material,
        );
        if (wall) {
            if (state.selectedRoomId) {
                wall.room_id = state.selectedRoomId;
            }
            state.walls.push(wall);
        }
        state.pendingWallStart = null;
    };

    const parseMetersPrompt = (label, defaultValue) => {
        const raw = window.prompt(label, defaultValue);
        if (raw === null) return null;
        const value = Number(String(raw).replace(',', '.'));
        if (!Number.isFinite(value) || value <= 0) return NaN;
        return value;
    };

    const parseDegreesPrompt = (label, defaultValue) => {
        const raw = window.prompt(label, defaultValue);
        if (raw === null) return null;
        const value = Number(String(raw).replace(',', '.'));
        if (!Number.isFinite(value)) return NaN;
        return value;
    };

    const normalizeRotationDeg = (value) => {
        const normalized = Number(value || 0) % 360;
        return normalized < 0 ? normalized + 360 : normalized;
    };

    const rotateSelectedSymbolBy = (deltaDeg) => {
        const point = selectedPoint();
        if (!point || !symbolMetaFromPoint(point)) return;
        pushUndoSnapshot();
        point.rotation_deg = normalizeRotationDeg(Number(point.rotation_deg || 0) + Number(deltaDeg || 0));
        renderPoints();
    };

    const rotateSelectedSymbolExact = () => {
        const point = selectedPoint();
        if (!point || !symbolMetaFromPoint(point)) return;
        const current = normalizeRotationDeg(point.rotation_deg || 0);
        const next = parseDegreesPrompt('Rotación del símbolo (°):', String(current));
        if (next === null) return;
        if (!Number.isFinite(next)) {
            window.alert('Valor inválido. Ingresa un número en grados.');
            return;
        }
        pushUndoSnapshot();
        point.rotation_deg = normalizeRotationDeg(next);
        renderPoints();
    };

    const applyOpeningWidthToSelectedWall = () => {
        if (!Number.isInteger(state.selectedWallIndex)) return;
        const wall = state.walls[state.selectedWallIndex];
        if (!wall) return;
        const material = String(wall.material || '').toLowerCase();
        if (!['door', 'window'].includes(material)) return;

        const defaultWidth = wall.opening_width_m || openingWidthMetersForMaterial(material) || (material === 'door' ? 0.9 : 1.2);
        const nextWidth = parseMetersPrompt(`Ancho de ${material === 'door' ? 'puerta' : 'ventana'} (m):`, String(defaultWidth));
        if (nextWidth === null) return;
        if (!Number.isFinite(nextWidth)) {
            window.alert('Ancho inválido. Usa un número positivo.');
            return;
        }
        if (!hasMetricScale()) {
            window.alert('Define escala real del plano (ancho/alto en metros) para parametrizar el ancho.');
            return;
        }

        pushUndoSnapshot();
        const rebuilt = buildStructureSegment(
            { x: Number(wall.x1_percent || 0), y: Number(wall.y1_percent || 0) },
            { x: Number(wall.x2_percent || 0), y: Number(wall.y2_percent || 0) },
            material,
        );
        if (!rebuilt) return;

        const resized = resolveFixedLengthEndpoint(
            { x: Number(wall.x1_percent || 0), y: Number(wall.y1_percent || 0) },
            { x: Number(rebuilt.x2_percent || 0), y: Number(rebuilt.y2_percent || 0) },
            nextWidth,
        );
        wall.x2_percent = snapPercent(resized.x, 'x');
        wall.y2_percent = snapPercent(resized.y, 'y');
        wall.opening_width_m = Number(nextWidth.toFixed(2));
        renderPoints();
    };

    const generateRoomAt = (centerPos) => {
        const roomWidth = parseMetersPrompt('Ancho del cuarto (m):', '6');
        if (roomWidth === null) return;
        const roomHeight = parseMetersPrompt('Largo del cuarto (m):', '4');
        if (roomHeight === null) return;

        if (!Number.isFinite(roomWidth) || !Number.isFinite(roomHeight)) {
            window.alert('Dimensiones inválidas. Usa solo números positivos.');
            return;
        }

        pushUndoSnapshot();

        let scaleWidth = scaleWidthInputEl?.value ? Number(scaleWidthInputEl.value) : null;
        let scaleHeight = scaleHeightInputEl?.value ? Number(scaleHeightInputEl.value) : null;
        const fitRatio = 0.22;
        const topReservedPercent = 16;

        if (!(scaleWidth > 0)) {
            scaleWidth = Number((roomWidth / fitRatio).toFixed(2));
            if (scaleWidthInputEl) scaleWidthInputEl.value = String(scaleWidth);
        }
        if (!(scaleHeight > 0)) {
            scaleHeight = Number((roomHeight / fitRatio).toFixed(2));
            if (scaleHeightInputEl) scaleHeightInputEl.value = String(scaleHeight);
        }

        if (roomWidth >= scaleWidth) {
            scaleWidth = Number((roomWidth / fitRatio).toFixed(2));
            if (scaleWidthInputEl) scaleWidthInputEl.value = String(scaleWidth);
        }
        if (roomHeight >= scaleHeight) {
            scaleHeight = Number((roomHeight / fitRatio).toFixed(2));
            if (scaleHeightInputEl) scaleHeightInputEl.value = String(scaleHeight);
        }

        const roomWidthPercent = Math.max(2, Math.min(96, (roomWidth / scaleWidth) * 100));
        const roomHeightPercent = Math.max(2, Math.min(96, (roomHeight / scaleHeight) * 100));

        const halfW = roomWidthPercent / 2;
        const halfH = roomHeightPercent / 2;
        const centerX = Math.max(halfW, Math.min(100 - halfW, Number(centerPos?.x ?? 50)));
        const minCenterY = Math.min(100 - halfH, topReservedPercent + halfH);
        const preferredCenterY = Number(centerPos?.y ?? 50) + 6;
        const centerY = Math.max(minCenterY, Math.min(100 - halfH, preferredCenterY));

        const x1 = snapPercent(centerX - halfW, 'x');
        const y1 = snapPercent(centerY - halfH, 'y');
        const x2 = snapPercent(centerX + halfW, 'x');
        const y2 = snapPercent(centerY + halfH, 'y');

        const material = String(wallMaterialEl?.value || 'drywall');
        const meta = materialMeta(material);
        const roomId = createRoomId();
        upsertRoom({ id: roomId, name: `Espacio ${state.rooms.length + 1}` });
        state.selectedRoomId = roomId;

        state.pendingWallStart = null;
        state.walls.push(
            { x1_percent: x1, y1_percent: y1, x2_percent: x2, y2_percent: y1, material, loss_db: meta.loss_db, room_id: roomId },
            { x1_percent: x2, y1_percent: y1, x2_percent: x2, y2_percent: y2, material, loss_db: meta.loss_db, room_id: roomId },
            { x1_percent: x2, y1_percent: y2, x2_percent: x1, y2_percent: y2, material, loss_db: meta.loss_db, room_id: roomId },
            { x1_percent: x1, y1_percent: y2, x2_percent: x1, y2_percent: y1, material, loss_db: meta.loss_db, room_id: roomId },
        );

        if (layerFilterEl && !['all', 'walls'].includes(String(layerFilterEl.value || ''))) {
            layerFilterEl.value = 'all';
        }

        syncScaleSummary();
        renderPoints();

        if (state.viewMode === '3d') {
            cleanupThreeScene();
            render3dScene();
        }
    };

    const showContextMenu = (event, pos) => {
        if (!contextMenuEl || !wrapEl || !state.editable || !pos) return;
        event.preventDefault();
        event.stopPropagation();

        const currentWallMaterial = String(wallMaterialEl?.value || 'drywall');
        const wallMaterialCatalog = [
            { icon: '🪵', material: 'wood', name: 'Madera' },
            { icon: '🧱', material: 'brick', name: 'Ladrillo' },
            { icon: '🏢', material: 'concrete', name: 'Concreto' },
            { icon: '🪟', material: 'glass', name: 'Vidrio' },
            { icon: '🛡️', material: 'metal', name: 'Metálico' },
            { icon: '📄', material: 'drywall', name: 'Drywall' },
        ];

        const wallMaterialItems = wallMaterialCatalog
            .map((item) => {
                const meta = materialMeta(item.material);
                const active = currentWallMaterial === item.material;
                return {
                    icon: item.icon,
                    label: `Muro de ${item.name} (${meta.loss_db} dB)`,
                    material: item.material,
                    color: meta.color,
                    active,
                };
            })
            .sort((a, b) => Number(b.active) - Number(a.active));

        const groups = [
            {
                title: 'Equipos',
                actions: [
                    { icon: '📶', label: 'Agregar AP aquí', handler: () => { addAccessPointAt(pos); clearSelectedPoint(); renderPoints(); } },
                ],
            },
            {
                title: 'Biblioteca TI',
                actions: Object.entries(SYMBOL_LIBRARY).map(([key, item]) => ({
                    icon: item.icon,
                    label: `Agregar ${item.label}`,
                    color: item.color,
                    active: selectedSymbolKey() === key,
                    handler: () => {
                        if (symbolTypeEl) symbolTypeEl.value = key;
                        addSymbolAt(pos, key);
                        clearSelectedPoint();
                        renderPoints();
                    },
                })),
            },
            {
                title: 'Construcción',
                actions: [
                    { icon: '📐', label: 'Generar cuarto (m)', handler: () => generateRoomAt(pos) },
                ].concat(wallMaterialItems.map((item) => ({
                    icon: item.icon,
                    label: item.label,
                    color: item.color,
                    active: item.active,
                    handler: () => {
                        startWallWithMaterialAt(pos, item.material);
                        renderPoints();
                    },
                }))),
            },
            {
                title: 'Aberturas',
                actions: [
                    { icon: '🚪', label: 'Iniciar puerta aquí', handler: () => { startStructureAt(pos, 'door'); renderPoints(); } },
                    { icon: '🪟', label: 'Iniciar ventana aquí', handler: () => { startStructureAt(pos, 'window'); renderPoints(); } },
                ],
            },
        ];

        if (state.pendingWallStart && isStructureMode()) {
            groups.push({
                title: 'Segmento activo',
                actions: [
                    { icon: '✅', label: 'Completar segmento aquí', handler: () => { completeStructureAt(pos); renderPoints(); } },
                    { icon: '✖', label: 'Cancelar segmento actual', handler: () => { pushUndoSnapshot(); state.pendingWallStart = null; renderPoints(); } },
                ],
            });
        }

        if (selectedPoint()) {
            groups.push({
                title: 'Acciones',
                actions: [
                    {
                        icon: '🗑️',
                        label: 'Eliminar AP seleccionado',
                        handler: () => {
                            if (!Number.isInteger(state.selectedPointIndex)) return;
                            pushUndoSnapshot();
                            state.points.splice(state.selectedPointIndex, 1);
                            clearSelectedPoint();
                            renderPoints();
                        },
                    },
                ],
            });

            if (symbolMetaFromPoint(selectedPoint())) {
                groups.push({
                    title: 'Símbolo',
                    actions: [
                        { icon: '↺', label: 'Rotar -15°', handler: () => rotateSelectedSymbolBy(-15) },
                        { icon: '↻', label: 'Rotar +15°', handler: () => rotateSelectedSymbolBy(15) },
                        { icon: '🔢', label: 'Definir ángulo…', handler: () => rotateSelectedSymbolExact() },
                    ],
                });
            }
        }

        if (Number.isInteger(state.selectedWallIndex) && ['door', 'window'].includes(String(state.walls[state.selectedWallIndex]?.material || '').toLowerCase())) {
            groups.push({
                title: 'Abertura',
                actions: [
                    { icon: '↔️', label: 'Ajustar ancho paramétrico', handler: () => applyOpeningWidthToSelectedWall() },
                ],
            });
        }

        if (state.selectedRoomId) {
            groups.push({
                title: 'Espacio',
                actions: [
                    { icon: '🏷️', label: 'Renombrar espacio', handler: () => renameSelectedRoom() },
                ],
            });
        }

        const actions = groups.flatMap((group) => group.actions);
        let actionIndex = 0;
        contextMenuEl.innerHTML = groups.map((group) => {
            const groupButtons = group.actions.map((action) => {
                const html = `
                    <button type="button" data-fp-action-index="${actionIndex}" class="btn btn-sm w-100 text-start border-0 rounded-0 py-2 px-3" style="font-size:12px;${action.active ? ' background:#eef2ff;' : ''}">
                        <span style="display:inline-block; width:18px;">${esc(action.icon || '•')}</span>
                        <span style="display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:6px; border:1px solid #cbd5e1; background:${esc(action.color || 'transparent')}; vertical-align:middle;"></span>
                        <span>${esc(action.label)}</span>
                        ${action.active ? '<span class="badge bg-primary ms-2" style="font-size:10px;">Activo</span>' : ''}
                    </button>
                `;
                actionIndex += 1;
                return html;
            }).join('');

            return `
                <div class="border-bottom">
                    <div class="px-3 py-1 text-uppercase text-muted" style="font-size:10px; letter-spacing:0.04em;">${esc(group.title)}</div>
                    ${groupButtons}
                </div>
            `;
        }).join('');

        contextMenuEl.querySelectorAll('button[data-fp-action-index]').forEach((button) => {
            button.addEventListener('click', (clickEvent) => {
                clickEvent.preventDefault();
                clickEvent.stopPropagation();
                const actionIndex = Number(button.getAttribute('data-fp-action-index'));
                const action = actions[actionIndex];
                if (action?.handler) action.handler();
                hideContextMenu();
            });
            button.addEventListener('mouseenter', () => {
                button.style.background = '#f8fafc';
            });
            button.addEventListener('mouseleave', () => {
                button.style.background = 'transparent';
            });
        });

        const wrapRect = wrapEl.getBoundingClientRect();
        const x = event.clientX - wrapRect.left;
        const y = event.clientY - wrapRect.top;

        contextMenuEl.style.display = 'block';
        const menuWidth = contextMenuEl.offsetWidth || 220;
        const menuHeight = contextMenuEl.offsetHeight || 180;
        const left = Math.max(6, Math.min(x, wrapEl.clientWidth - menuWidth - 6));
        const top = Math.max(6, Math.min(y, wrapEl.clientHeight - menuHeight - 6));
        contextMenuEl.style.left = `${left}px`;
        contextMenuEl.style.top = `${top}px`;

        state.contextMenu = { x: pos.x, y: pos.y };
    };

    const wallSegmentsIntersect = (a, b, c, d) => {
        const orient = (p, q, r) => (q.x - p.x) * (r.y - p.y) - (q.y - p.y) * (r.x - p.x);
        const o1 = orient(a, b, c);
        const o2 = orient(a, b, d);
        const o3 = orient(c, d, a);
        const o4 = orient(c, d, b);
        return (o1 * o2 < 0) && (o3 * o4 < 0);
    };

    const wallLossBetween = (p1, p2, width, height) => {
        let totalLoss = 0;
        state.walls.forEach((wall) => {
            const w1 = { x: (Number(wall.x1_percent || 0) / 100) * width, y: (Number(wall.y1_percent || 0) / 100) * height };
            const w2 = { x: (Number(wall.x2_percent || 0) / 100) * width, y: (Number(wall.y2_percent || 0) / 100) * height };
            if (wallSegmentsIntersect(p1, p2, w1, w2)) {
                totalLoss += Number(wall.loss_db ?? materialMeta(wall.material).loss_db);
            }
        });
        return totalLoss;
    };

    const visibleAccessPoints = () => {
        const layerFilter = currentLayerFilter();
        return state.points.filter((point) => {
            if (!isAccessPointItem(point)) return false;
            if (layerFilter === 'walls') return false;
            if (layerFilter === 'all') return true;
            return point.layer === layerFilter;
        });
    };

    const estimateSignalAtPixel = (target, width, height) => {
        const points = visibleAccessPoints();
        if (!points.length) return null;

        let best = null;
        points.forEach((point) => {
            const source = pxPoint(point, width, height);
            const radiusPx = computeRadiusPx(point, width, height);
            const distPx = Math.hypot(target.x - source.x, target.y - source.y);
            if (distPx > radiusPx * 1.45) return;

            const baseSignal = Number(point.signal_dbm ?? -55);
            const distanceLoss = (distPx / radiusPx) * 45;
            const wallLoss = wallLossBetween(source, target, width, height);
            const orientationLoss = orientationLossBetween(point, source, target);
            const estimated = baseSignal - distanceLoss - wallLoss - orientationLoss;

            if (!best || estimated > best.signal) {
                best = {
                    signal: estimated,
                    point,
                    wallLoss,
                    distanceLoss,
                    orientationLoss,
                    distancePx: distPx,
                };
            }
        });

        return best;
    };

    const heatmapStyleForSignal = (signal) => {
        if (!Number.isFinite(signal) || signal < -95) return null;
        if (signal >= -55) return { fill: 'rgba(74,222,128,0.24)' };
        if (signal >= -62) return { fill: 'rgba(163,230,53,0.21)' };
        if (signal >= -68) return { fill: 'rgba(250,204,21,0.18)' };
        if (signal >= -74) return { fill: 'rgba(251,146,60,0.16)' };
        if (signal >= -82) return { fill: 'rgba(248,113,113,0.14)' };
        return { fill: 'rgba(185,28,28,0.10)' };
    };

    const signalColorForValue = (signal) => {
        if (!Number.isFinite(signal) || signal < -95) return '#b91c1c';
        if (signal >= -55) return '#4ade80';
        if (signal >= -62) return '#a3e635';
        if (signal >= -68) return '#facc15';
        if (signal >= -74) return '#fb923c';
        if (signal >= -82) return '#f87171';
        return '#b91c1c';
    };

    const hideSignalProbe = () => {
        if (!signalProbeEl) return;
        signalProbeEl.style.display = 'none';
        signalProbeEl.innerHTML = '';
    };

    const updateSignalProbe = (event) => {
        if (!signalProbeEl || !overlayEl || state.viewMode !== '2d' || state.fileType === 'pdf') {
            hideSignalProbe();
            return;
        }

        const rect = overlayEl.getBoundingClientRect();
        if (!rect.width || !rect.height) {
            hideSignalProbe();
            return;
        }

        const localX = event.clientX - rect.left;
        const localY = event.clientY - rect.top;
        if (localX < 0 || localY < 0 || localX > rect.width || localY > rect.height) {
            hideSignalProbe();
            return;
        }

        const estimated = estimateSignalAtPixel({ x: localX, y: localY }, rect.width, rect.height);
        if (!estimated) {
            hideSignalProbe();
            return;
        }

        const wrapRect = wrapEl.getBoundingClientRect();
        const pxPerMeter = pixelsPerMeter(rect.width, rect.height);
        const distanceMeters = pxPerMeter ? (estimated.distancePx / pxPerMeter) : null;

        signalProbeEl.innerHTML = `
            <strong>${estimated.signal.toFixed(1)} dBm estimados</strong>
            <small>Fuente: ${esc(estimated.point.label || 'AP')}</small>
            <small>Pérdida por distancia: ${estimated.distanceLoss.toFixed(1)} dB</small>
            <small>Pérdida por muros: ${estimated.wallLoss.toFixed(1)} dB${distanceMeters !== null ? ` · ${distanceMeters.toFixed(2)} m` : ''}</small>
        `;
        signalProbeEl.style.display = 'block';
        signalProbeEl.style.left = `${Math.min(wrapEl.clientWidth - 170, Math.max(10, event.clientX - wrapRect.left))}px`;
        signalProbeEl.style.top = `${Math.min(wrapEl.clientHeight - 80, Math.max(10, event.clientY - wrapRect.top))}px`;
    };

    const wallVertexKey = (xPercent, yPercent) => `${Number(xPercent || 0).toFixed(4)}:${Number(yPercent || 0).toFixed(4)}`;

    const buildWallTopology = () => {
        const vertexMap = new Map();
        const adjacency = new Map();

        state.walls.forEach((wall, index) => {
            const startKey = wallVertexKey(wall.x1_percent, wall.y1_percent);
            const endKey = wallVertexKey(wall.x2_percent, wall.y2_percent);

            if (!vertexMap.has(startKey)) {
                vertexMap.set(startKey, {
                    key: startKey,
                    x_percent: Number(wall.x1_percent || 0),
                    y_percent: Number(wall.y1_percent || 0),
                    connections: [],
                });
            }
            if (!vertexMap.has(endKey)) {
                vertexMap.set(endKey, {
                    key: endKey,
                    x_percent: Number(wall.x2_percent || 0),
                    y_percent: Number(wall.y2_percent || 0),
                    connections: [],
                });
            }

            vertexMap.get(startKey).connections.push({ index, endpoint: 'start' });
            vertexMap.get(endKey).connections.push({ index, endpoint: 'end' });

            if (!adjacency.has(index)) adjacency.set(index, new Set());
        });

        Array.from(vertexMap.values()).forEach((vertex) => {
            const wallIndexes = vertex.connections.map((connection) => connection.index);
            wallIndexes.forEach((wallIndex) => {
                const neighbours = adjacency.get(wallIndex) || new Set();
                wallIndexes.forEach((otherIndex) => {
                    if (otherIndex !== wallIndex) neighbours.add(otherIndex);
                });
                adjacency.set(wallIndex, neighbours);
            });
        });

        const visited = new Set();
        const groups = [];

        state.walls.forEach((wall, index) => {
            if (visited.has(index)) return;
            const queue = [index];
            const wallIndexes = [];
            visited.add(index);

            while (queue.length) {
                const current = queue.shift();
                wallIndexes.push(current);
                (adjacency.get(current) || []).forEach((neighbour) => {
                    if (visited.has(neighbour)) return;
                    visited.add(neighbour);
                    queue.push(neighbour);
                });
            }

            const coords = wallIndexes.flatMap((wallIndex) => {
                const item = state.walls[wallIndex];
                if (!item) return [];
                return [
                    { x: Number(item.x1_percent || 0), y: Number(item.y1_percent || 0) },
                    { x: Number(item.x2_percent || 0), y: Number(item.y2_percent || 0) },
                ];
            });
            if (!coords.length) return;

            const xs = coords.map((coord) => coord.x);
            const ys = coords.map((coord) => coord.y);
            const roomIds = Array.from(new Set(
                wallIndexes
                    .map((wallIndex) => state.walls[wallIndex]?.room_id || null)
                    .filter((roomId) => Boolean(roomId))
            ));
            groups.push({
                wallIndexes,
                minX: Math.min(...xs),
                maxX: Math.max(...xs),
                minY: Math.min(...ys),
                maxY: Math.max(...ys),
                centerX: xs.reduce((sum, value) => sum + value, 0) / xs.length,
                centerY: ys.reduce((sum, value) => sum + value, 0) / ys.length,
                roomIds,
                primaryRoomId: roomIds.length === 1 ? roomIds[0] : null,
            });
        });

        return { vertexMap, groups };
    };

    const wallLengthMeters = (wall) => {
        if (!wall) return null;
        const scale = planScale();
        if (!(Number(scale.width_m) > 0) || !(Number(scale.height_m) > 0)) return null;

        const dx = ((Number(wall.x2_percent || 0) - Number(wall.x1_percent || 0)) / 100) * Number(scale.width_m);
        const dy = ((Number(wall.y2_percent || 0) - Number(wall.y1_percent || 0)) / 100) * Number(scale.height_m);
        return Math.hypot(dx, dy);
    };

    const wallLengthLabel = (wall) => {
        const meters = wallLengthMeters(wall);
        if (meters !== null) {
            return `${meters.toFixed(2)} m`;
        }
        return null;
    };

    const groupAreaSqMeters = (group) => {
        if (!group) return null;
        const scale = planScale();
        if (!(Number(scale.width_m) > 0) || !(Number(scale.height_m) > 0)) return null;
        const widthMeters = ((group.maxX - group.minX) / 100) * Number(scale.width_m);
        const heightMeters = ((group.maxY - group.minY) / 100) * Number(scale.height_m);
        return widthMeters > 0 && heightMeters > 0 ? widthMeters * heightMeters : null;
    };

    const renameSelectedRoom = () => {
        if (!state.selectedRoomId) return;
        const current = roomById(state.selectedRoomId);
        const nextName = window.prompt('Nombre del espacio:', current?.name || '');
        if (nextName === null) return;
        pushUndoSnapshot();
        upsertRoom({ id: state.selectedRoomId, name: String(nextName).trim() || null });
        renderPoints();
    };

    const drawHeatmap = () => {
        if (!heatCanvasEl || !overlayEl) return;
        const ctx = heatCanvasEl.getContext('2d');
        if (!ctx) return;

        const width = heatCanvasEl.width;
        const height = heatCanvasEl.height;
        ctx.clearRect(0, 0, width, height);

        const points = visibleAccessPoints();
        if (!points.length) return;

        const step = 12;
        for (let y = 0; y <= height; y += step) {
            for (let x = 0; x <= width; x += step) {
                const estimated = estimateSignalAtPixel({ x, y }, width, height);
                const style = heatmapStyleForSignal(estimated?.signal);
                if (!style) continue;
                ctx.fillStyle = style.fill;
                ctx.fillRect(x, y, step + 1, step + 1);
            }
        }
    };

    const applyZoomTransform = () => {
        if (wrapEl && state.zoom > 1) {
            const ww = wrapEl.clientWidth;
            const wh = wrapEl.clientHeight;
            // Clamp so content stays within reachable bounds:
            // max pan right/down = 0 (can't move content past origin)
            // max pan left/up = -(size*(zoom-1)) so the far edge of content aligns with viewport edge
            state.zoomOrigin.x = Math.min(0, Math.max(-ww * (state.zoom - 1), state.zoomOrigin.x));
            state.zoomOrigin.y = Math.min(0, Math.max(-wh * (state.zoom - 1), state.zoomOrigin.y));
        } else if (wrapEl && state.zoom <= 1) {
            state.zoomOrigin.x = 0;
            state.zoomOrigin.y = 0;
        }
        const transform = `translate(${state.zoomOrigin.x}px, ${state.zoomOrigin.y}px) scale(${state.zoom})`;
        [imageEl, heatCanvasEl, wallsLayerEl, overlayEl].forEach((el) => {
            if (!el) return;
            el.style.transformOrigin = 'top left';
            el.style.transform = transform;
        });

        if (wrapEl) {
            wrapEl.style.cursor = state.zoomPan ? 'grabbing' : (state.zoom > 1 ? 'grab' : 'default');
        }
    };

    const startZoomPan = (event) => {
        if (!wrapEl || state.viewMode !== '2d') return;
        if (!(event.button === 1 || (event.button === 0 && event.altKey))) return;

        event.preventDefault();
        state.zoomPan = {
            pointerId: event.pointerId,
            startX: event.clientX,
            startY: event.clientY,
            originX: state.zoomOrigin.x,
            originY: state.zoomOrigin.y,
        };

        if (typeof wrapEl.setPointerCapture === 'function') {
            try { wrapEl.setPointerCapture(event.pointerId); } catch (_) {}
        }
        applyZoomTransform();
    };

    const stopZoomPan = () => {
        if (!state.zoomPan) return;
        state.zoomPan = null;
        applyZoomTransform();
    };
    
    // --- ZOOM HANDLERS ---
    function setZoom(newZoom, origin = null) {
        const prevZoom = state.zoom;
        state.zoom = Math.max(state.zoomMin, Math.min(state.zoomMax, newZoom));
        
        // origin es coordenadas de pantalla. Queremos que permanezca bajo el cursor.
        if (origin) {
            // Convertir coordenadas de pantalla a coordenadas de mundo (sin zoom)
            const worldX = (origin.x - state.zoomOrigin.x) / prevZoom;
            const worldY = (origin.y - state.zoomOrigin.y) / prevZoom;
            
            // Calcular nuevo origin para que el punto del mundo permanezca en la misma posición de pantalla
            state.zoomOrigin.x = origin.x - worldX * state.zoom;
            state.zoomOrigin.y = origin.y - worldY * state.zoom;
        }

        applyZoomTransform();
        drawHeatmap();
    }

    function resetZoom() {
        state.zoom = 1;
        state.zoomOrigin = { x: 0, y: 0 };
        applyZoomTransform();
        drawHeatmap();
    }

    // Ajustar tamaño del canvas al tamaño del contenedor y zoom
    function resizeHeatCanvas() {
        if (!heatCanvasEl || !wrapEl) return;
        if (state.fileType === 'png' || state.fileType === 'svg') {
            syncOverlayGeometry();
            return;
        }
        const rect = wrapEl.getBoundingClientRect();
        heatCanvasEl.width = rect.width;
        heatCanvasEl.height = rect.height;
        drawHeatmap();
    }
    window.addEventListener('resize', resizeHeatCanvas);
    setTimeout(resizeHeatCanvas, 200);

    const renderWalls = () => {
        if (!wallsLayerEl || !overlayEl) return;
        const layerFilter = currentLayerFilter();
        wallsLayerEl.innerHTML = '';

        if (layerFilter !== 'all' && layerFilter !== 'walls') {
            wallsLayerEl.style.display = 'none';
            return;
        }

        wallsLayerEl.style.display = '';
        const width = overlayEl.clientWidth;
        const height = overlayEl.clientHeight;
        wallsLayerEl.setAttribute('viewBox', `0 0 ${width} ${height}`);
        const topology = buildWallTopology();
        const sharedVertexKeys = new Set(
            Array.from(topology.vertexMap.values())
                .filter((vertex) => vertex.connections.length > 1)
                .map((vertex) => vertex.key)
        );

        state.walls.forEach((wall, index) => {
            const meta = materialMeta(wall.material);
            const isSelectedWall = state.selectedWallIndex === index;
            const x1 = (Number(wall.x1_percent || 0) / 100) * width;
            const y1 = (Number(wall.y1_percent || 0) / 100) * height;
            const x2 = (Number(wall.x2_percent || 0) / 100) * width;
            const y2 = (Number(wall.y2_percent || 0) / 100) * height;

            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', String(x1));
            line.setAttribute('y1', String(y1));
            line.setAttribute('x2', String(x2));
            line.setAttribute('y2', String(y2));
            line.setAttribute('stroke', meta.color);
            line.setAttribute('stroke-width', isSelectedWall ? (wall.material === 'door' ? '8' : '7') : (wall.material === 'door' ? '5' : '4'));
            line.setAttribute('stroke-linecap', 'round');
            if (wall.material === 'window') {
                line.setAttribute('stroke-dasharray', '8 6');
            }
            if (isSelectedWall) {
                line.setAttribute('filter', 'drop-shadow(0 0 3px rgba(14,165,233,.8))');
            }
            line.style.cursor = state.editable ? 'pointer' : 'default';
            const openingWidth = Number(wall.opening_width_m || 0) > 0 ? ` · ${Number(wall.opening_width_m).toFixed(2)} m` : '';
            line.setAttribute('title', `${meta.label} (${wall.loss_db ?? meta.loss_db} dB)${openingWidth}`);

            if (state.editable) {
                line.addEventListener('pointerdown', (event) => {
                    event.stopPropagation();
                    const pos = toPercent(event.clientX, event.clientY);
                    if (!pos) return;
                    state.selectedWallIndex = index;
                    pushUndoSnapshot();
                    state.wallDrag = {
                        index,
                        mode: 'line',
                        startPointer: pos,
                        original: {
                            x1_percent: Number(wall.x1_percent || 0),
                            y1_percent: Number(wall.y1_percent || 0),
                            x2_percent: Number(wall.x2_percent || 0),
                            y2_percent: Number(wall.y2_percent || 0),
                        },
                    };
                });

                line.addEventListener('click', (event) => {
                    event.stopPropagation();
                    if (!event.shiftKey) {
                        state.selectedWallIndex = index;
                        renderPoints();
                        return;
                    }
                    pushUndoSnapshot();
                    state.walls.splice(index, 1);
                    if (state.selectedWallIndex === index) clearSelectedWall();
                    renderPoints();
                });

                line.addEventListener('contextmenu', (event) => {
                    if (!event.shiftKey) return;
                    event.preventDefault();
                    event.stopPropagation();
                    pushUndoSnapshot();
                    state.walls.splice(index, 1);
                    if (state.selectedWallIndex === index) clearSelectedWall();
                    renderPoints();
                });
            }

            wallsLayerEl.appendChild(line);

            if (wall.material === 'door' || wall.material === 'window') {
                const mx = (x1 + x2) / 2;
                const my = (y1 + y2) / 2;
                const badge = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                badge.setAttribute('cx', String(mx));
                badge.setAttribute('cy', String(my));
                badge.setAttribute('r', '8');
                badge.setAttribute('fill', wall.material === 'door' ? '#f59e0b' : '#38bdf8');
                badge.setAttribute('stroke', '#ffffff');
                badge.setAttribute('stroke-width', '1.5');
                badge.style.pointerEvents = state.editable ? 'auto' : 'none';
                badge.style.cursor = state.editable ? 'move' : 'default';
                if (state.editable) {
                    badge.addEventListener('pointerdown', (event) => {
                        event.stopPropagation();
                        const pos = toPercent(event.clientX, event.clientY);
                        if (!pos) return;
                        state.selectedWallIndex = index;
                        pushUndoSnapshot();
                        state.wallDrag = {
                            index,
                            mode: 'line',
                            startPointer: pos,
                            original: {
                                x1_percent: Number(wall.x1_percent || 0),
                                y1_percent: Number(wall.y1_percent || 0),
                                x2_percent: Number(wall.x2_percent || 0),
                                y2_percent: Number(wall.y2_percent || 0),
                            },
                        };
                    });
                    badge.addEventListener('click', (event) => {
                        event.stopPropagation();
                        state.selectedWallIndex = index;
                        renderPoints();
                    });
                }
                wallsLayerEl.appendChild(badge);

                const badgeText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                badgeText.setAttribute('x', String(mx));
                badgeText.setAttribute('y', String(my + 3));
                badgeText.setAttribute('text-anchor', 'middle');
                badgeText.setAttribute('font-size', '8');
                badgeText.setAttribute('font-weight', '700');
                badgeText.setAttribute('fill', '#0f172a');
                badgeText.style.pointerEvents = 'none';
                badgeText.textContent = Number(wall.opening_width_m || 0) > 0
                    ? `${wall.material === 'door' ? 'P' : 'V'} ${Number(wall.opening_width_m).toFixed(1)}m`
                    : (wall.material === 'door' ? 'P' : 'V');
                wallsLayerEl.appendChild(badgeText);
            }

            if (state.editable) {
                const startHandle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                startHandle.setAttribute('cx', String(x1));
                startHandle.setAttribute('cy', String(y1));
                startHandle.setAttribute('r', '5');
                startHandle.setAttribute('fill', '#ffffff');
                startHandle.setAttribute('stroke', meta.color);
                startHandle.setAttribute('stroke-width', '2');
                startHandle.style.cursor = 'grab';
                startHandle.setAttribute('title', 'Arrastra para mover el inicio del muro');
                if (!sharedVertexKeys.has(wallVertexKey(wall.x1_percent, wall.y1_percent))) {
                    startHandle.addEventListener('pointerdown', (event) => {
                        event.stopPropagation();
                        pushUndoSnapshot();
                        state.wallDrag = { index, mode: 'start' };
                    });
                    wallsLayerEl.appendChild(startHandle);
                }

                const endHandle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                endHandle.setAttribute('cx', String(x2));
                endHandle.setAttribute('cy', String(y2));
                endHandle.setAttribute('r', '5');
                endHandle.setAttribute('fill', '#ffffff');
                endHandle.setAttribute('stroke', meta.color);
                endHandle.setAttribute('stroke-width', '2');
                endHandle.style.cursor = 'grab';
                endHandle.setAttribute('title', 'Arrastra para mover el fin del muro');
                if (!sharedVertexKeys.has(wallVertexKey(wall.x2_percent, wall.y2_percent))) {
                    endHandle.addEventListener('pointerdown', (event) => {
                        event.stopPropagation();
                        pushUndoSnapshot();
                        state.wallDrag = { index, mode: 'end' };
                    });
                    wallsLayerEl.appendChild(endHandle);
                }
            }
        });

        topology.groups
            .filter((group) => group.primaryRoomId)
            .forEach((group) => {
                const area = groupAreaSqMeters(group);
                const label = `${roomDisplayName(group.primaryRoomId)}${area !== null ? ` · ${area.toFixed(2)} m²` : ''}`;
                const labelWidth = Math.max(92, label.length * 6.4);
                const centerX = (group.centerX / 100) * width;
                const centerY = (group.centerY / 100) * height;
                const isSelectedRoom = state.selectedRoomId === group.primaryRoomId;

                const bg = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                bg.setAttribute('x', String(centerX - (labelWidth / 2)));
                bg.setAttribute('y', String(centerY - 14));
                bg.setAttribute('width', String(labelWidth));
                bg.setAttribute('height', '20');
                bg.setAttribute('rx', '7');
                bg.setAttribute('fill', isSelectedRoom ? 'rgba(14,165,233,0.16)' : 'rgba(255,255,255,0.88)');
                bg.setAttribute('stroke', isSelectedRoom ? '#0ea5e9' : '#94a3b8');
                bg.setAttribute('stroke-width', isSelectedRoom ? '1.4' : '1');
                bg.style.pointerEvents = 'none';
                wallsLayerEl.appendChild(bg);

                const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', String(centerX));
                text.setAttribute('y', String(centerY));
                text.setAttribute('text-anchor', 'middle');
                text.setAttribute('font-size', '10');
                text.setAttribute('font-weight', '700');
                text.setAttribute('fill', '#0f172a');
                text.style.pointerEvents = 'none';
                text.textContent = label;
                wallsLayerEl.appendChild(text);
            });

        const activeWallIndex = Number.isInteger(state.wallDrag?.index) ? state.wallDrag.index : state.selectedWallIndex;
        const activeWall = Number.isInteger(activeWallIndex) ? state.walls[activeWallIndex] : null;
        if (activeWall) {
            const x1 = (Number(activeWall.x1_percent || 0) / 100) * width;
            const y1 = (Number(activeWall.y1_percent || 0) / 100) * height;
            const x2 = (Number(activeWall.x2_percent || 0) / 100) * width;
            const y2 = (Number(activeWall.y2_percent || 0) / 100) * height;
            const label = wallLengthLabel(activeWall);
            if (label) {
                const midX = (x1 + x2) / 2;
                const midY = (y1 + y2) / 2;
                const labelWidth = Math.max(62, label.length * 7);

                const labelBg = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                labelBg.setAttribute('x', String(midX - (labelWidth / 2)));
                labelBg.setAttribute('y', String(midY - 24));
                labelBg.setAttribute('width', String(labelWidth));
                labelBg.setAttribute('height', '18');
                labelBg.setAttribute('rx', '6');
                labelBg.setAttribute('fill', 'rgba(255,255,255,0.95)');
                labelBg.setAttribute('stroke', '#0ea5e9');
                labelBg.setAttribute('stroke-width', '1.2');
                labelBg.style.pointerEvents = 'none';
                wallsLayerEl.appendChild(labelBg);

                const labelText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                labelText.setAttribute('x', String(midX));
                labelText.setAttribute('y', String(midY - 11));
                labelText.setAttribute('text-anchor', 'middle');
                labelText.setAttribute('font-size', '10');
                labelText.setAttribute('font-weight', '700');
                labelText.setAttribute('fill', '#0f172a');
                labelText.style.pointerEvents = 'none';
                labelText.textContent = label;
                wallsLayerEl.appendChild(labelText);
            }
        }

        const activeGroupIndexes = state.wallDrag?.mode === 'group' && Array.isArray(state.wallDrag.wallIndexes)
            ? state.wallDrag.wallIndexes
            : (state.wallDrag?.mode === 'vertex' && Array.isArray(state.wallDrag.targets)
                ? state.wallDrag.targets.map((target) => target.index)
                : null);

        if (activeGroupIndexes && activeGroupIndexes.length) {
            const group = topology.groups.find((item) => item.wallIndexes.some((wallIndex) => activeGroupIndexes.includes(wallIndex)));
            const scale = planScale();
            if (group && Number(scale.width_m) > 0 && Number(scale.height_m) > 0) {
                const minX = (group.minX / 100) * width;
                const maxX = (group.maxX / 100) * width;
                const minY = (group.minY / 100) * height;
                const maxY = (group.maxY / 100) * height;
                const widthMeters = ((group.maxX - group.minX) / 100) * Number(scale.width_m);
                const heightMeters = ((group.maxY - group.minY) / 100) * Number(scale.height_m);

                const horizontalLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                horizontalLine.setAttribute('x1', String(minX));
                horizontalLine.setAttribute('y1', String(Math.max(12, minY - 18)));
                horizontalLine.setAttribute('x2', String(maxX));
                horizontalLine.setAttribute('y2', String(Math.max(12, minY - 18)));
                horizontalLine.setAttribute('stroke', '#0ea5e9');
                horizontalLine.setAttribute('stroke-width', '2');
                horizontalLine.setAttribute('stroke-dasharray', '5 4');
                horizontalLine.style.pointerEvents = 'none';
                wallsLayerEl.appendChild(horizontalLine);

                const horizontalLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                horizontalLabel.setAttribute('x', String((minX + maxX) / 2));
                horizontalLabel.setAttribute('y', String(Math.max(10, minY - 24)));
                horizontalLabel.setAttribute('text-anchor', 'middle');
                horizontalLabel.setAttribute('font-size', '10');
                horizontalLabel.setAttribute('font-weight', '700');
                horizontalLabel.setAttribute('fill', '#0369a1');
                horizontalLabel.style.pointerEvents = 'none';
                horizontalLabel.textContent = `${widthMeters.toFixed(2)} m`;
                wallsLayerEl.appendChild(horizontalLabel);

                const verticalLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                verticalLine.setAttribute('x1', String(Math.max(12, minX - 18)));
                verticalLine.setAttribute('y1', String(minY));
                verticalLine.setAttribute('x2', String(Math.max(12, minX - 18)));
                verticalLine.setAttribute('y2', String(maxY));
                verticalLine.setAttribute('stroke', '#0ea5e9');
                verticalLine.setAttribute('stroke-width', '2');
                verticalLine.setAttribute('stroke-dasharray', '5 4');
                verticalLine.style.pointerEvents = 'none';
                wallsLayerEl.appendChild(verticalLine);

                const verticalLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                verticalLabel.setAttribute('x', String(Math.max(14, minX - 24)));
                verticalLabel.setAttribute('y', String((minY + maxY) / 2));
                verticalLabel.setAttribute('text-anchor', 'middle');
                verticalLabel.setAttribute('font-size', '10');
                verticalLabel.setAttribute('font-weight', '700');
                verticalLabel.setAttribute('fill', '#0369a1');
                verticalLabel.setAttribute('transform', `rotate(-90 ${Math.max(14, minX - 24)} ${(minY + maxY) / 2})`);
                verticalLabel.style.pointerEvents = 'none';
                verticalLabel.textContent = `${heightMeters.toFixed(2)} m`;
                wallsLayerEl.appendChild(verticalLabel);
            }
        }

        if (state.editable) {
            topology.groups
                .filter((group) => group.wallIndexes.length > 1)
                .forEach((group) => {
                    const minX = (group.minX / 100) * width;
                    const maxX = (group.maxX / 100) * width;
                    const minY = (group.minY / 100) * height;
                    const maxY = (group.maxY / 100) * height;

                    const outline = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                    outline.setAttribute('x', String(minX));
                    outline.setAttribute('y', String(minY));
                    outline.setAttribute('width', String(Math.max(8, maxX - minX)));
                    outline.setAttribute('height', String(Math.max(8, maxY - minY)));
                    outline.setAttribute('fill', 'transparent');
                    outline.setAttribute('stroke', '#38bdf8');
                    outline.setAttribute('stroke-width', '1.5');
                    outline.setAttribute('stroke-dasharray', '6 4');
                    outline.setAttribute('opacity', '0.45');
                    outline.style.cursor = 'move';
                    outline.setAttribute('title', 'Arrastra para mover el cuarto completo');
                    outline.addEventListener('pointerdown', (event) => {
                        event.stopPropagation();
                        const pos = toPercent(event.clientX, event.clientY);
                        if (!pos) return;
                        state.selectedRoomId = group.primaryRoomId || null;
                        pushUndoSnapshot();
                        state.wallDrag = {
                            mode: 'group',
                            wallIndexes: group.wallIndexes.slice(),
                            startPointer: pos,
                            originals: group.wallIndexes.map((wallIndex) => ({
                                index: wallIndex,
                                x1_percent: Number(state.walls[wallIndex]?.x1_percent || 0),
                                y1_percent: Number(state.walls[wallIndex]?.y1_percent || 0),
                                x2_percent: Number(state.walls[wallIndex]?.x2_percent || 0),
                                y2_percent: Number(state.walls[wallIndex]?.y2_percent || 0),
                            })),
                        };
                    });
                    wallsLayerEl.appendChild(outline);

                    const moveHandle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    moveHandle.setAttribute('cx', String((group.centerX / 100) * width));
                    moveHandle.setAttribute('cy', String((group.centerY / 100) * height));
                    moveHandle.setAttribute('r', '8');
                    moveHandle.setAttribute('fill', '#0ea5e9');
                    moveHandle.setAttribute('stroke', '#ffffff');
                    moveHandle.setAttribute('stroke-width', '2');
                    moveHandle.style.cursor = 'move';
                    moveHandle.setAttribute('title', 'Mover cuarto completo');
                    moveHandle.addEventListener('pointerdown', (event) => {
                        event.stopPropagation();
                        const pos = toPercent(event.clientX, event.clientY);
                        if (!pos) return;
                        state.selectedRoomId = group.primaryRoomId || null;
                        pushUndoSnapshot();
                        state.wallDrag = {
                            mode: 'group',
                            wallIndexes: group.wallIndexes.slice(),
                            startPointer: pos,
                            originals: group.wallIndexes.map((wallIndex) => ({
                                index: wallIndex,
                                x1_percent: Number(state.walls[wallIndex]?.x1_percent || 0),
                                y1_percent: Number(state.walls[wallIndex]?.y1_percent || 0),
                                x2_percent: Number(state.walls[wallIndex]?.x2_percent || 0),
                                y2_percent: Number(state.walls[wallIndex]?.y2_percent || 0),
                            })),
                        };
                    });
                    wallsLayerEl.appendChild(moveHandle);
                });

            Array.from(topology.vertexMap.values())
                .filter((vertex) => vertex.connections.length > 1)
                .forEach((vertex) => {
                    const nodeHandle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    nodeHandle.setAttribute('cx', String((vertex.x_percent / 100) * width));
                    nodeHandle.setAttribute('cy', String((vertex.y_percent / 100) * height));
                    nodeHandle.setAttribute('r', '7');
                    nodeHandle.setAttribute('fill', '#f8fafc');
                    nodeHandle.setAttribute('stroke', '#0ea5e9');
                    nodeHandle.setAttribute('stroke-width', '2.5');
                    nodeHandle.style.cursor = 'grab';
                    nodeHandle.setAttribute('title', 'Nodo compartido: mueve simultáneamente los muros conectados');
                    nodeHandle.addEventListener('pointerdown', (event) => {
                        event.stopPropagation();
                        const group = topology.groups.find((item) => item.wallIndexes.some((wallIndex) => vertex.connections.some((connection) => connection.index === wallIndex)));
                        state.selectedRoomId = group?.primaryRoomId || null;
                        pushUndoSnapshot();
                        state.wallDrag = {
                            mode: 'vertex',
                            targets: vertex.connections.map((connection) => ({ ...connection })),
                        };
                    });
                    wallsLayerEl.appendChild(nodeHandle);
                });
        }

        if (state.pendingWallStart) {
            const start = state.pendingWallStart;
            const sx = (Number(start.x_percent || 0) / 100) * width;
            const sy = (Number(start.y_percent || 0) / 100) * height;
            const dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            dot.setAttribute('cx', String(sx));
            dot.setAttribute('cy', String(sy));
            dot.setAttribute('r', '5');
            dot.setAttribute('fill', '#facc15');
            wallsLayerEl.appendChild(dot);
        }

        if (state.calibration.start) {
            const sx = (Number(state.calibration.start.x_percent || 0) / 100) * width;
            const sy = (Number(state.calibration.start.y_percent || 0) / 100) * height;
            const startDot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            startDot.setAttribute('cx', String(sx));
            startDot.setAttribute('cy', String(sy));
            startDot.setAttribute('r', '6');
            startDot.setAttribute('fill', '#22c55e');
            startDot.setAttribute('stroke', '#ffffff');
            startDot.setAttribute('stroke-width', '2');
            startDot.style.pointerEvents = 'none';
            wallsLayerEl.appendChild(startDot);
        }

        const calibrationEndPoint = state.calibration.end || state.calibration.current;

        if (state.calibration.start && calibrationEndPoint) {
            const segment = calibrationSegmentInfo(state.calibration.start, calibrationEndPoint);
            if (!segment) return;
            const sx = segment.start.x;
            const sy = segment.start.y;
            const ex = segment.end.x;
            const ey = segment.end.y;

            const measureLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            measureLine.setAttribute('x1', String(sx));
            measureLine.setAttribute('y1', String(sy));
            measureLine.setAttribute('x2', String(ex));
            measureLine.setAttribute('y2', String(ey));
            measureLine.setAttribute('stroke', '#22c55e');
            measureLine.setAttribute('stroke-width', '3');
            measureLine.setAttribute('stroke-dasharray', '6 4');
            measureLine.style.pointerEvents = 'none';
            wallsLayerEl.appendChild(measureLine);

            const endDot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            endDot.setAttribute('cx', String(ex));
            endDot.setAttribute('cy', String(ey));
            endDot.setAttribute('r', '6');
            endDot.setAttribute('fill', '#22c55e');
            endDot.setAttribute('stroke', '#ffffff');
            endDot.setAttribute('stroke-width', '2');
            endDot.style.pointerEvents = 'none';
            wallsLayerEl.appendChild(endDot);

            const knownDistance = calibrationDistanceInputEl?.value ? Number(calibrationDistanceInputEl.value) : null;
            const measureTextValue = knownDistance > 0
                ? `${knownDistance.toFixed(2)} m`
                : `${segment.pixels.toFixed(1)} px`;
            const labelWidth = Math.max(48, measureTextValue.length * 6.5);

            const measureLabelBg = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            measureLabelBg.setAttribute('x', String(segment.midX - (labelWidth / 2)));
            measureLabelBg.setAttribute('y', String(segment.midY - 18));
            measureLabelBg.setAttribute('width', String(labelWidth));
            measureLabelBg.setAttribute('height', '16');
            measureLabelBg.setAttribute('rx', '5');
            measureLabelBg.setAttribute('fill', 'rgba(255,255,255,0.92)');
            measureLabelBg.setAttribute('stroke', '#22c55e');
            measureLabelBg.setAttribute('stroke-width', '1');
            measureLabelBg.style.pointerEvents = 'none';
            wallsLayerEl.appendChild(measureLabelBg);

            const measureText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            measureText.setAttribute('x', String(segment.midX));
            measureText.setAttribute('y', String(segment.midY - 6));
            measureText.setAttribute('text-anchor', 'middle');
            measureText.setAttribute('font-size', '9');
            measureText.setAttribute('font-weight', '700');
            measureText.setAttribute('fill', '#166534');
            measureText.style.pointerEvents = 'none';
            measureText.textContent = measureTextValue;
            wallsLayerEl.appendChild(measureText);
        }
    };

    const renderPoints = () => {
        if (!overlayEl) return;

        overlayEl.innerHTML = '';
        const layerFilter = currentLayerFilter();
        const visiblePoints = state.points.map((point, index) => ({ point, index })).filter(({ point }) => {
            if (layerFilter === 'walls') return false;
            if (layerFilter === 'all') return true;
            return point.layer === layerFilter;
        });

        if (overlayEl) {
            const shouldRouteToWalls = layerFilter === 'walls' || isStructureMode();
            overlayEl.style.pointerEvents = shouldRouteToWalls ? 'none' : 'auto';
        }
        if (wallsLayerEl) {
            wallsLayerEl.style.pointerEvents = 'auto';
        }

        drawHeatmap();
        renderWalls();

        visiblePoints.forEach(({ point, index }) => {
            const symbol = symbolMetaFromPoint(point);
            const isSymbol = Boolean(symbol);
            const symbolSize = Number(point.symbol_size_m || 1.2);
            const marker = document.createElement('button');
            marker.type = 'button';
            marker.style.position = 'absolute';
            marker.style.left = `${point.x_percent}%`;
            marker.style.top = `${point.y_percent}%`;
            const rotation = normalizeRotationDeg(point.rotation_deg || 0);
            marker.style.transform = `translate(-50%, -50%) rotate(${rotation}deg)`;
            marker.style.width = isSymbol ? `${Math.max(18, Math.min(44, symbolSize * 12))}px` : '14px';
            marker.style.height = isSymbol ? `${Math.max(18, Math.min(44, symbolSize * 12))}px` : '14px';
            marker.style.borderRadius = isSymbol ? '8px' : '50%';
            marker.style.border = state.selectedPointIndex === index ? '3px solid #facc15' : '2px solid #fff';
            marker.style.background = isSymbol ? symbol.color : (point.layer === 'critical' ? '#ef4444' : '#2563eb');
            marker.style.fontSize = isSymbol ? '12px' : '0';
            marker.style.lineHeight = '1';
            marker.style.display = 'flex';
            marker.style.alignItems = 'center';
            marker.style.justifyContent = 'center';
            marker.style.padding = '0';
            marker.style.margin = '0';
            marker.style.outline = 'none';
            marker.style.zIndex = '100';
            marker.style.cursor = state.editable ? 'grab' : 'default';
            marker.style.pointerEvents = 'auto';
            marker.textContent = isSymbol ? symbol.icon : '';
            marker.title = isSymbol
                ? `${point.label || symbol.label}${point.symbol_size_m != null ? ` · ${point.symbol_size_m} m` : ''} · rot ${rotation.toFixed(0)}°`
                : `${point.label || 'AP'}${point.signal_dbm != null ? ` · ${point.signal_dbm} dBm` : ''}${point.radius_meters != null ? ` · ${point.radius_meters} m` : ''}${point.mount_height_m != null ? ` · h ${point.mount_height_m} m` : ''}${point.radiation_pattern ? ` · ${point.radiation_pattern}` : ''}`;

            if (state.editable) {
                marker.addEventListener('pointerdown', (event) => {
                    if (event.button !== 0) return;
                    event.preventDefault();
                    event.stopPropagation();
                    const pos = toPercent(event.clientX, event.clientY);
                    if (!pos) return;
                    state.isDuringDragSetup = true;
                    selectPointByIndex(index);
                    pushUndoSnapshot();
                    state.isDuringDragSetup = false;
                    // Capture pointer so drag events keep firing even if DOM changes
                    try { marker.setPointerCapture(event.pointerId); } catch (_) {}
                    marker.style.cursor = 'grabbing';
                    // Apply selection highlight directly — do NOT call renderPoints() here
                    // as that would destroy this marker element and break the drag gesture
                    overlayEl.querySelectorAll('button').forEach((btn) => {
                        btn.style.border = '2px solid #fff';
                    });
                    marker.style.border = '3px solid #facc15';
                    state.apDrag = {
                        index,
                        markerEl: marker,
                        startPointer: { x: pos.x, y: pos.y },
                        original: {
                            x_percent: Number(point.x_percent || 0),
                            y_percent: Number(point.y_percent || 0),
                        },
                    };

                    // Agregar listener temporal de pointermove al marcador para el drag
                    const onMarkerPointerMove = (moveEvent) => {
                        const point = state.points[state.apDrag.index];
                        if (!point) {
                            state.apDrag = null;
                            return;
                        }
                        const movePos = toPercent(moveEvent.clientX, moveEvent.clientY);
                        if (!movePos) return;
                        const dx = movePos.x - Number(state.apDrag.startPointer?.x || 0);
                        const dy = movePos.y - Number(state.apDrag.startPointer?.y || 0);
                        const newX = clampPercent(Number(state.apDrag.original?.x_percent || 0) + dx);
                        const newY = clampPercent(Number(state.apDrag.original?.y_percent || 0) + dy);
                        applyWallSnapToAccessPoint(point, { x: newX, y: newY });
                        if (state.apDrag.markerEl) {
                            state.apDrag.markerEl.style.left = `${point.x_percent}%`;
                            state.apDrag.markerEl.style.top = `${point.y_percent}%`;
                        }
                        // Actualizar el heatmap en tiempo real mientras se arrastra
                        drawHeatmap();
                    };

                    // Agregar listener temporal de pointerup para detener el drag
                    const onMarkerPointerUp = () => {
                        marker.removeEventListener('pointermove', onMarkerPointerMove);
                        document.removeEventListener('pointerup', onMarkerPointerUp);
                        state.apDrag = null;
                        marker.style.cursor = 'grab';
                        renderPoints();
                    };

                    marker.addEventListener('pointermove', onMarkerPointerMove);
                    document.addEventListener('pointerup', onMarkerPointerUp);
                });

                marker.addEventListener('click', (event) => {
                    event.stopPropagation();
                    if (event.shiftKey) {
                        pushUndoSnapshot();
                        state.points.splice(index, 1);
                        if (state.selectedPointIndex === index) clearSelectedPoint();
                        if (Number.isInteger(state.selectedPointIndex) && state.selectedPointIndex > index) {
                            state.selectedPointIndex -= 1;
                        }
                        renderPoints();
                        return;
                    }
                    selectPointByIndex(index);
                    renderPoints();
                });
                marker.addEventListener('contextmenu', (event) => {
                    if (!event.shiftKey) {
                        selectPointByIndex(index);
                        renderPoints();
                        return;
                    }
                    event.preventDefault();
                    event.stopPropagation();
                    pushUndoSnapshot();
                    state.points.splice(index, 1);
                    if (state.selectedPointIndex === index) clearSelectedPoint();
                    if (Number.isInteger(state.selectedPointIndex) && state.selectedPointIndex > index) {
                        state.selectedPointIndex -= 1;
                    }
                    renderPoints();
                });
            } else {
                marker.disabled = true;
            }

            overlayEl.appendChild(marker);
        });

        const visibleApCount = visiblePoints.filter(({ point }) => isAccessPointItem(point)).length;
        const totalApCount = state.points.filter((point) => isAccessPointItem(point)).length;
        const totalSymbolCount = state.points.length - totalApCount;
        const scale = planScale();
        const scaleLabel = (scale.width_m || scale.height_m) ? ` · escala ${scale.width_m || '—'}m x ${scale.height_m || '—'}m` : '';
        const selectedLabel = selectedPoint()
            ? (symbolMetaFromPoint(selectedPoint())
                ? ` · símbolo: ${selectedPoint().label || 'Símbolo'} (${normalizeRotationDeg(selectedPoint().rotation_deg || 0).toFixed(0)}°)`
                : ` · AP seleccionado: ${selectedPoint().label || 'AP'}`)
            : '';
        const selectedWallLabel = Number.isInteger(state.selectedWallIndex) && state.walls[state.selectedWallIndex]
            ? ` · muro: ${wallLengthLabel(state.walls[state.selectedWallIndex]) || 'seleccionado'}`
            : '';
        const selectedRoomLabel = state.selectedRoomId
            ? (() => {
                const topology = buildWallTopology();
                const group = topology.groups.find((item) => item.primaryRoomId === state.selectedRoomId);
                const area = groupAreaSqMeters(group);
                return ` · espacio: ${roomDisplayName(state.selectedRoomId)}${area !== null ? ` (${area.toFixed(2)} m²)` : ''}`;
            })()
            : '';
        footerEl.textContent = `${visibleApCount} AP visibles / ${totalApCount} AP totales · ${totalSymbolCount} símbolos TI · ${state.walls.length} muros/puertas/ventanas${scaleLabel}${selectedLabel}${selectedWallLabel}${selectedRoomLabel}`;

        if (state.viewMode === '3d') {
            render3dScene();
        }
    };

    const rebuildNodeSelect = () => {
        if (!nodeSelectEl) return;

        const options = ['<option value="">Sin nodo</option>']
            .concat(state.apNodes.map((node) => `<option value="${node.id}">${esc(node.name)}${node.code ? ` · ${esc(node.code)}` : ''}${node.space ? ` · ${esc(node.space)}` : ''}</option>`));
        nodeSelectEl.innerHTML = options.join('');
    };

    const rebuildApModelSelect = () => {
        if (!apModelSelectEl) return;
        const models = Array.isArray(window.__itcityApModels) ? window.__itcityApModels : [];
        const options = ['<option value="">Sin modelo / manual</option>']
            .concat(models.map((m) => `<option value="${m.id}">${esc(m.brand ? m.brand + ' ' : '')}${esc(m.name)}</option>`));
        apModelSelectEl.innerHTML = options.join('');
    };

    const setPlanVisual = (plan) => {
        state.fileType = String(plan.file_type || '').toLowerCase();
        // Permitir edición para todos los tipos de archivo PNG/SVG
        state.editable = state.fileType === 'png' || state.fileType === 'svg';
        state.viewMode = '2d';
        cleanupThreeScene();

        imageEl.style.display = 'none';
        pdfEl.style.display = 'none';
        overlayEl.style.display = 'none';
        wallsLayerEl.style.display = 'none';
        heatCanvasEl.style.display = 'none';
        noticeEl.style.display = 'none';
        noticeEl.textContent = '';
        state.pendingWallStart = null;

        if (state.fileType === 'png' || state.fileType === 'svg') {
            imageEl.src = plan.file_url;
            imageEl.style.display = 'block';
            overlayEl.style.display = 'block';
            wallsLayerEl.style.display = 'block';
            heatCanvasEl.style.display = 'block';
            imageEl.onload = syncOverlayGeometry;
            window.setTimeout(syncOverlayGeometry, 60);
        } else if (state.fileType === 'pdf') {
            pdfEl.src = plan.file_url;
            pdfEl.style.display = 'block';
            noticeEl.style.display = 'block';
            noticeEl.textContent = 'Vista PDF habilitada. Para edición de puntos sobre el plano usa PNG (o exporta el PDF a PNG).';
        } else {
            noticeEl.style.display = 'block';
            noticeEl.textContent = 'Archivo DWG/DXF detectado. El editor web no dibuja DWG nativo; sube una versión PNG para marcar AP y heatmap.';
        }

        syncViewModeUi();
    };

    const openEditor = async (planId, options = {}) => {
        const showModalFirst = options && options.showModalFirst === true;
        const hideLegacyOnSuccess = options && options.hideLegacyOnSuccess === true;
        const editorModal = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
        const adminPanelEl = document.querySelector('.admin-panel.floor-plan-direct-mode');

        if (showModalFirst && editorModal) {
            editorModal.show();
            if (titleEl) titleEl.textContent = 'Cargando plano...';
        }

        try {
            const response = await fetch(`/admin/floor-plans/${planId}/data`, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            if (!response.ok || !data.ok) {
                throw new Error(data?.message || `Error HTTP ${response.status}`);
            }

            const plan = data.plan || {};
            state.planId = plan.id;
            state.points = Array.isArray(plan.points) ? plan.points.slice() : [];
            state.walls = Array.isArray(plan.walls) ? plan.walls.slice() : [];
            state.rooms = Array.isArray(plan.rooms) ? plan.rooms.slice() : [];
            state.apNodes = Array.isArray(data.ap_nodes) ? data.ap_nodes : [];
            state.pendingWallStart = null;
            state.nodeSnapshotCache = new Map();
            state.nodeSnapshotPending = new Set();
            clearSelectedPoint();
            clearSelectedWall();
            clearSelectedRoom();
            state.apDrag = null;
            state.calibration = { active: false, start: null, end: null, current: null };

            if (editModeEl) {
                editModeEl.value = state.walls.length > 0 ? 'wall' : 'access-point';
            }

            if (scaleWidthInputEl) scaleWidthInputEl.value = plan.scale?.width_m ?? '';
            if (scaleHeightInputEl) scaleHeightInputEl.value = plan.scale?.height_m ?? '';
            if (wallHeightInputEl) wallHeightInputEl.value = plan.structure_defaults?.wall_height_m ?? 2.8;
            if (doorHeightInputEl) doorHeightInputEl.value = plan.structure_defaults?.door_height_m ?? 2.1;
            if (doorWidthInputEl) doorWidthInputEl.value = plan.structure_defaults?.door_width_m ?? 0.9;
            if (windowBaseInputEl) windowBaseInputEl.value = plan.structure_defaults?.window_base_m ?? 1.0;
            if (windowHeightInputEl) windowHeightInputEl.value = plan.structure_defaults?.window_height_m ?? 1.2;
            if (windowWidthInputEl) windowWidthInputEl.value = plan.structure_defaults?.window_width_m ?? 1.2;
            if (orthogonalLockInputEl) orthogonalLockInputEl.checked = plan.structure_defaults?.orthogonal_lock !== false;
            if (mountHeightInputEl) mountHeightInputEl.value = plan.structure_defaults?.ap_mount_height_m ?? 2.6;
            if (wallMaterialEl) {
                const preferred = String(plan.structure_defaults?.preferred_wall_material || '').toLowerCase();
                const allowed = ['drywall', 'brick', 'concrete', 'glass', 'wood', 'metal'];
                wallMaterialEl.value = allowed.includes(preferred) ? preferred : 'drywall';
            }
            syncScaleSummary();
            syncCalibrationUi();

            titleEl.textContent = `Editor de plano · ${plan.name || 'Plano'}`;
            metaNameEl.textContent = plan.name || 'Plano';
            metaBranchEl.textContent = `${plan.branch_name || 'N/A'}${plan.space_name ? ` · ${plan.space_name}` : ''}${plan.floor ? ` · Piso ${plan.floor}` : ''}`;

            rebuildNodeSelect();
            rebuildApModelSelect();
            setPlanVisual(plan);
            renderPoints();

            if (editorModal) {
                editorModal.show();
            } else {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
            if (hideLegacyOnSuccess && adminPanelEl) {
                adminPanelEl.classList.add('floor-plan-direct-hide-legacy');
            }
            window.setTimeout(syncOverlayGeometry, 120);
        } catch (error) {
            if (adminPanelEl) {
                adminPanelEl.classList.remove('floor-plan-direct-hide-legacy');
            }
            if (titleEl) titleEl.textContent = 'Editor de plano';
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo abrir el mapa de calor',
                    text: error?.message || 'Verifica que el plano exista y vuelve a intentar.',
                });
            }
        }
    };

    document.querySelectorAll('.btnOpenFloorPlanEditor').forEach((button) => {
        button.addEventListener('click', () => {
            const planId = Number(button.getAttribute('data-floor-plan-id'));
            if (planId > 0) openEditor(planId);
        });
    });

    if (snapApWallsInputEl) {
        snapApWallsInputEl.addEventListener('change', persistSnapApWallsPreference);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);
        const autoPlanId = Number(window.__itcityInitialFloorPlanId || params.get('floor_plan'));
        if (autoPlanId > 0) {
            openEditor(autoPlanId, { showModalFirst: true });
        }
    });

    if (view2dBtn) {
        view2dBtn.addEventListener('click', () => setViewMode('2d'));
    }

    if (view3dBtn) {
        view3dBtn.addEventListener('click', () => setViewMode('3d'));
    }

    if (layerFilterEl) layerFilterEl.addEventListener('change', renderPoints);

    if (apModelSelectEl) {
        apModelSelectEl.addEventListener('change', function () {
            const modelId = Number(this.value);
            if (!modelId) return;
            const models = Array.isArray(window.__itcityApModels) ? window.__itcityApModels : [];
            const model = models.find((m) => m.id === modelId);
            if (!model) return;
            // Use the average of min/max as the working radius, fall back to max or min
            const radius = model.coverage_radius_max_m != null
                ? (model.coverage_radius_min_m != null
                    ? Math.round(((Number(model.coverage_radius_min_m) + Number(model.coverage_radius_max_m)) / 2) * 10) / 10
                    : Number(model.coverage_radius_max_m))
                : (model.coverage_radius_min_m != null ? Number(model.coverage_radius_min_m) : null);
            if (radius != null && radiusMetersInputEl) radiusMetersInputEl.value = radius;
            if (model.default_signal_dbm != null && signalInputEl) signalInputEl.value = model.default_signal_dbm;
            if (model.radiation_pattern && patternSelectEl) patternSelectEl.value = model.radiation_pattern;
            if (model.mount_height_m != null && mountHeightInputEl) mountHeightInputEl.value = model.mount_height_m;
            // Persist changes to the currently selected point and redraw
            if (applyFormToSelectedPoint()) {
                renderPoints();
                if (state.viewMode === '3d') render3dScene();
            }
        });
    }
    if (scaleWidthInputEl) scaleWidthInputEl.addEventListener('input', () => { syncScaleSummary(); renderPoints(); });
    if (scaleHeightInputEl) scaleHeightInputEl.addEventListener('input', () => { syncScaleSummary(); renderPoints(); });
    [wallHeightInputEl, doorHeightInputEl, doorWidthInputEl, windowBaseInputEl, windowHeightInputEl, windowWidthInputEl, mountHeightInputEl].forEach((input) => {
        if (!input) return;
        input.addEventListener('input', () => renderPoints());
    });
    if (orthogonalLockInputEl) {
        orthogonalLockInputEl.addEventListener('change', () => renderWalls());
    }
    if (calibrationDistanceInputEl) calibrationDistanceInputEl.addEventListener('input', () => {
        if (state.calibration.start && state.calibration.end) {
            applyCalibrationScale();
            renderPoints();
        } else if (state.calibration.active && state.calibration.start && state.calibration.current) {
            syncCalibrationUi();
            renderWalls();
        }
    });
    if (calibrationToggleBtn) {
        calibrationToggleBtn.addEventListener('click', () => {
            state.calibration.active = !state.calibration.active;
            state.calibration.start = null;
            state.calibration.end = null;
            state.calibration.current = null;
            syncCalibrationUi();
            renderWalls();
        });
    }
    if (nodeSelectEl) {
        nodeSelectEl.addEventListener('change', () => {
            if (state.isDuringDragSetup) return;
            const selectedNode = findSelectedApNode();
            if (selectedNode?.rf_defaults) {
                applyRfDefaults(selectedNode.rf_defaults);
            }
            if (selectedPoint() && isAccessPointItem(selectedPoint())) {
                pushUndoSnapshot();
            }
            if (applyFormToSelectedPoint()) {
                renderPoints();
                loadSelectedPointNodeInsights({ force: true });
            }
        });
    }

    [
        layerSelectEl,
        radiusInputEl,
        radiusMetersInputEl,
        signalInputEl,
        patternSelectEl,
        mountOrientationEl,
        mountHeightInputEl,
        azimuthInputEl,
        tiltInputEl,
    ].forEach((input) => {
        if (!input) return;
        input.addEventListener('input', () => {
            if (state.isDuringDragSetup) return;
            if (selectedPoint() && isAccessPointItem(selectedPoint())) {
                pushUndoSnapshot();
            }
            if (applyFormToSelectedPoint()) {
                renderPoints();
            }
        });
        input.addEventListener('change', () => {
            if (selectedPoint() && isAccessPointItem(selectedPoint())) {
                pushUndoSnapshot();
            }
            if (applyFormToSelectedPoint()) {
                renderPoints();
            }
        });
    });

    if (editModeEl) {
        editModeEl.addEventListener('change', () => {
            state.pendingWallStart = null;
            state.apDrag = null;
            const mode = currentEditMode();
            if (wallMaterialEl) {
                if (mode === 'door' || mode === 'window') {
                    wallMaterialEl.value = mode;
                    wallMaterialEl.disabled = true;
                } else {
                    wallMaterialEl.disabled = false;
                }
            }
            if (mode !== 'access-point') {
                clearSelectedPoint();
            }
            if (!isStructureMode()) {
                clearSelectedWall();
                clearSelectedRoom();
            }
            if (editHintEl) {
                if (isStructureMode()) {
                    editHintEl.textContent = 'Modo muro/puerta/ventana: clic inicio + clic final para crear (con bloqueo ortogonal si está activo). Arrastra línea/extremos para ajustar, marco azul para mover cuarto y nodos para esquinas compartidas. Shift+clic o clic derecho para eliminar. Ctrl+Z deshacer / Ctrl+Y rehacer.';
                } else if (isSymbolMode()) {
                    editHintEl.textContent = 'Modo símbolo TI: clic para colocar el símbolo seleccionado de la biblioteca, luego arrastra para moverlo. R para ángulo exacto, Q/E o [/ ] para rotar ±15°. Shift+clic o clic derecho sobre símbolo para eliminar. Ctrl+Z deshacer / Ctrl+Y rehacer.';
                } else {
                    editHintEl.textContent = 'Modo AP: clic para agregar, clic en AP para seleccionar/editar y arrastrar. Shift+clic o clic derecho sobre AP para eliminar. Ctrl+Z deshacer / Ctrl+Y rehacer.';
                }
            }
            renderWalls();
            renderPoints();
        });
        editModeEl.dispatchEvent(new Event('change'));
    }

    window.addEventListener('pointermove', (event) => {
        if (state.zoomPan) {
            const dx = event.clientX - Number(state.zoomPan.startX || 0);
            const dy = event.clientY - Number(state.zoomPan.startY || 0);
            state.zoomOrigin.x = Number(state.zoomPan.originX || 0) + dx;
            state.zoomOrigin.y = Number(state.zoomPan.originY || 0) + dy;
            applyZoomTransform();
            return;
        }

        if (state.editable && state.calibration.active && state.calibration.start && !state.calibration.end) {
            const pos = toPercent(event.clientX, event.clientY);
            if (pos) {
                state.calibration.current = { x_percent: pos.x, y_percent: pos.y };
                syncCalibrationUi();
                renderWalls();
            }
        }

        if (state.editable && state.apDrag) {
            const point = state.points[state.apDrag.index];
            if (!point) {
                state.apDrag = null;
                return;
            }
            const pos = toPercent(event.clientX, event.clientY);
            if (!pos) return;
            const dx = pos.x - Number(state.apDrag.startPointer?.x || 0);
            const dy = pos.y - Number(state.apDrag.startPointer?.y || 0);
            const newX = clampPercent(Number(state.apDrag.original?.x_percent || 0) + dx);
            const newY = clampPercent(Number(state.apDrag.original?.y_percent || 0) + dy);
            applyWallSnapToAccessPoint(point, { x: newX, y: newY });
            if (state.apDrag.markerEl) {
                state.apDrag.markerEl.style.left = `${point.x_percent}%`;
                state.apDrag.markerEl.style.top = `${point.y_percent}%`;
            }
            return;
        }

        if (!state.editable || !state.wallDrag) return;

        const pos = toPercent(event.clientX, event.clientY);
        if (!pos) return;

        if (state.wallDrag.mode === 'vertex' && Array.isArray(state.wallDrag.targets)) {
            const snappedX = snapPercent(pos.x, 'x');
            const snappedY = snapPercent(pos.y, 'y');
            state.wallDrag.targets.forEach((target) => {
                const wall = state.walls[target.index];
                if (!wall) return;
                if (target.endpoint === 'start') {
                    wall.x1_percent = snappedX;
                    wall.y1_percent = snappedY;
                } else {
                    wall.x2_percent = snappedX;
                    wall.y2_percent = snappedY;
                }
            });
            renderWalls();
            return;
        }

        if (state.wallDrag.mode === 'group' && state.wallDrag.startPointer && Array.isArray(state.wallDrag.originals)) {
            const dx = pos.x - Number(state.wallDrag.startPointer.x || 0);
            const dy = pos.y - Number(state.wallDrag.startPointer.y || 0);
            state.wallDrag.originals.forEach((original) => {
                const wall = state.walls[original.index];
                if (!wall) return;
                wall.x1_percent = snapPercent(Number(original.x1_percent || 0) + dx, 'x');
                wall.y1_percent = snapPercent(Number(original.y1_percent || 0) + dy, 'y');
                wall.x2_percent = snapPercent(Number(original.x2_percent || 0) + dx, 'x');
                wall.y2_percent = snapPercent(Number(original.y2_percent || 0) + dy, 'y');
            });
            renderWalls();
            return;
        }

        const wall = state.walls[state.wallDrag.index];
        if (!wall) {
            state.wallDrag = null;
            return;
        }

        if (state.wallDrag.mode === 'start') {
            let next = { x: pos.x, y: pos.y };
            if (isOrthogonalLockEnabled()) {
                next = resolveOrthogonalEndpoint({ x: Number(wall.x2_percent || 0), y: Number(wall.y2_percent || 0) }, next);
            }
            wall.x1_percent = snapPercent(next.x, 'x');
            wall.y1_percent = snapPercent(next.y, 'y');
            renderWalls();
            return;
        }

        if (state.wallDrag.mode === 'end') {
            let next = { x: pos.x, y: pos.y };
            if (isOrthogonalLockEnabled()) {
                next = resolveOrthogonalEndpoint({ x: Number(wall.x1_percent || 0), y: Number(wall.y1_percent || 0) }, next);
            }
            wall.x2_percent = snapPercent(next.x, 'x');
            wall.y2_percent = snapPercent(next.y, 'y');
            renderWalls();
            return;
        }

        if (state.wallDrag.mode === 'line' && state.wallDrag.startPointer && state.wallDrag.original) {
            const dx = pos.x - Number(state.wallDrag.startPointer.x || 0);
            const dy = pos.y - Number(state.wallDrag.startPointer.y || 0);
            wall.x1_percent = snapPercent(Number(state.wallDrag.original.x1_percent || 0) + dx, 'x');
            wall.y1_percent = snapPercent(Number(state.wallDrag.original.y1_percent || 0) + dy, 'y');
            wall.x2_percent = snapPercent(Number(state.wallDrag.original.x2_percent || 0) + dx, 'x');
            wall.y2_percent = snapPercent(Number(state.wallDrag.original.y2_percent || 0) + dy, 'y');
            renderWalls();
        }
    });

    window.addEventListener('pointerup', () => {
        if (state.zoomPan) {
            stopZoomPan();
            return;
        }

        if (!state.wallDrag && !state.apDrag) return;
        state.apDrag = null;
        state.wallDrag = null;
        renderPoints();
    });

    window.addEventListener('resize', () => {
        if (state.viewMode === '3d') {
            render3dScene();
        }
    });

    const getDirectPlanId = () => {
        const params = new URLSearchParams(window.location.search);
        return Number(window.__itcityInitialFloorPlanId || params.get('floor_plan'));
    };

    const getDirectBackUrl = () => {
        return window.__itcityFloorPlanBackUrl || '/admin/panel-admin-1#section-floor-plans';
    };

    let shouldReturnToBackUrl = false;
    modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach((button) => {
        button.addEventListener('click', () => {
            shouldReturnToBackUrl = true;
        });
    });

    modalEl.addEventListener('hide.bs.modal', (event) => {
        const directPlanId = getDirectPlanId();
        if (directPlanId > 0) {
            if (!shouldReturnToBackUrl) {
                event.preventDefault();
                return;
            }

            const fallbackBackUrl = getDirectBackUrl();
            document.body.style.background = '#fff';
            const mainEl = document.getElementById('ic-main');
            if (mainEl) {
                mainEl.style.transition = 'opacity .08s ease';
                mainEl.style.opacity = '0';
                mainEl.style.filter = 'none';
                mainEl.style.transform = 'none';
            }
            const overlayEl = document.getElementById('preziOverlay');
            if (overlayEl) {
                overlayEl.style.transition = 'opacity .08s ease';
                overlayEl.style.background = '#fff';
                overlayEl.style.opacity = '1';
            }
            window.location.replace(fallbackBackUrl);
        }
    });

    window.addEventListener('popstate', () => {
        const directPlanId = getDirectPlanId();
        if (directPlanId > 0) {
            window.location.replace(getDirectBackUrl());
        }
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        cleanupThreeScene();
        state.viewMode = '2d';
        state.zoomPan = null;
        state.undoStack = [];
        state.redoStack = [];
        hideContextMenu();
        hideSignalProbe();
        syncViewModeUi();
    });

    modalEl.addEventListener('shown.bs.modal', () => {
        syncViewModeUi();
        window.setTimeout(syncOverlayGeometry, 80);
        
        // Initialize zoom handlers (now that modal is shown and elements exist)
        if (zoomInBtn && zoomOutBtn && zoomResetBtn && wrapEl) {
            zoomInBtn.onclick = () => {
                const rect = wrapEl.getBoundingClientRect();
                const centerOrigin = { x: rect.width / 2, y: rect.height / 2 };
                setZoom(state.zoom + state.zoomStep, centerOrigin);
            };
            zoomOutBtn.onclick = () => {
                const rect = wrapEl.getBoundingClientRect();
                const centerOrigin = { x: rect.width / 2, y: rect.height / 2 };
                setZoom(state.zoom - state.zoomStep, centerOrigin);
            };
            zoomResetBtn.onclick = resetZoom;
        }

        // Zoom con Ctrl + rueda del mouse
        if (wrapEl) {
            if (wrapEl.__fpZoomWheelHandler) {
                wrapEl.removeEventListener('wheel', wrapEl.__fpZoomWheelHandler);
            }
            wrapEl.__fpZoomWheelHandler = (e) => {
                if (state.viewMode === '3d') return;

                if (state.zoom > 1 && !e.ctrlKey && !e.metaKey) {
                    e.preventDefault();
                    if (e.shiftKey) {
                        state.zoomOrigin.x -= e.deltaY;
                    } else {
                        state.zoomOrigin.x -= e.deltaX;
                        state.zoomOrigin.y -= e.deltaY;
                    }
                    applyZoomTransform();
                    return;
                }

                if (!e.ctrlKey && !e.metaKey) return;
                e.preventDefault();
                const rect = wrapEl.getBoundingClientRect();
                const mouse = {
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top,
                };
                if (e.deltaY < 0) {
                    setZoom(state.zoom + state.zoomStep, mouse);
                } else {
                    setZoom(state.zoom - state.zoomStep, mouse);
                }
            };
            wrapEl.addEventListener('wheel', wrapEl.__fpZoomWheelHandler, { passive: false });
        }
    });

    window.addEventListener('keydown', (event) => {
        if (!modalEl.classList.contains('show')) return;

        const directPlanId = getDirectPlanId();
        if (directPlanId > 0 && String(event.key || '').toLowerCase() === 'backspace') {
            const target = event.target;
            const isEditableTarget = target instanceof HTMLElement && (
                target.isContentEditable ||
                target.tagName === 'INPUT' ||
                target.tagName === 'TEXTAREA' ||
                target.tagName === 'SELECT'
            );
            if (!isEditableTarget) {
                event.preventDefault();
                window.location.replace(getDirectBackUrl());
                return;
            }
        }

        const selected = selectedPoint();
        const selectedIsSymbol = Boolean(selected && symbolMetaFromPoint(selected));
        if (!event.ctrlKey && !event.metaKey && selectedIsSymbol) {
            const key = String(event.key || '').toLowerCase();
            if (key === 'q' || key === '[') {
                event.preventDefault();
                rotateSelectedSymbolBy(-15);
                return;
            }
            if (key === 'e' || key === ']') {
                event.preventDefault();
                rotateSelectedSymbolBy(15);
                return;
            }
            if (key === 'r') {
                event.preventDefault();
                rotateSelectedSymbolExact();
                return;
            }
        }

        if (!(event.ctrlKey || event.metaKey)) return;

        const key = String(event.key || '').toLowerCase();
        if (key === 'z' && event.shiftKey) {
            event.preventDefault();
            redoLastChange();
            return;
        }

        if (key === 'z') {
            event.preventDefault();
            undoLastChange();
            return;
        }

        if (key === 'y') {
            event.preventDefault();
            redoLastChange();
        }
    });

    const handleCanvasClick = (event) => {
        if (!state.editable) return;
        hideContextMenu();

        const pos = toPercent(event.clientX, event.clientY);
        if (!pos) return;

        if (currentEditMode() === 'access-point') {
            clearSelectedPoint();
            clearSelectedWall();
            clearSelectedRoom();
        }

        if (state.calibration.active) {
            if (!state.calibration.start) {
                state.calibration.start = { x_percent: pos.x, y_percent: pos.y };
                state.calibration.end = null;
                state.calibration.current = null;
                syncCalibrationUi();
                renderWalls();
                return;
            }

            state.calibration.end = { x_percent: pos.x, y_percent: pos.y };
            state.calibration.current = null;
            applyCalibrationScale();
            state.calibration.active = false;
            syncCalibrationUi();
            renderPoints();
            return;
        }

        if (isStructureMode()) {
            if (!state.pendingWallStart) {
                pushUndoSnapshot();
                state.pendingWallStart = {
                    x_percent: snapPercent(pos.x, 'x'),
                    y_percent: snapPercent(pos.y, 'y'),
                };
                renderWalls();
                return;
            }

            pushUndoSnapshot();
            const material = selectedStructureMaterial();
            const wall = buildStructureSegment(
                { x: Number(state.pendingWallStart.x_percent), y: Number(state.pendingWallStart.y_percent) },
                { x: Number(pos.x), y: Number(pos.y) },
                material
            );
            if (wall) {
                if (state.selectedRoomId) {
                    wall.room_id = state.selectedRoomId;
                }
                state.walls.push(wall);
            }
            state.pendingWallStart = null;
            renderPoints();
            return;
        }

        if (isSymbolMode()) {
            addSymbolAt(pos);
            renderPoints();
            return;
        }

        addAccessPointAt(pos);

        renderPoints();
    };

    const handleCanvasContextMenu = (event) => {
        if (!state.editable) return;
        const pos = toPercent(event.clientX, event.clientY);
        if (!pos) return;
        showContextMenu(event, pos);
    };

    if (wrapEl && overlayEl) {
        wrapEl.addEventListener('pointerdown', startZoomPan);
        overlayEl.addEventListener('click', handleCanvasClick);
        overlayEl.addEventListener('contextmenu', handleCanvasContextMenu);
        overlayEl.addEventListener('pointermove', updateSignalProbe);
        overlayEl.addEventListener('pointerleave', hideSignalProbe);
    }

    if (wrapEl && wallsLayerEl) {
        wallsLayerEl.addEventListener('click', handleCanvasClick);
        wallsLayerEl.addEventListener('contextmenu', handleCanvasContextMenu);
        wallsLayerEl.addEventListener('pointermove', updateSignalProbe);
        wallsLayerEl.addEventListener('pointerleave', hideSignalProbe);
    }

    if (contextMenuEl) {
        contextMenuEl.addEventListener('click', (event) => event.stopPropagation());
        contextMenuEl.addEventListener('contextmenu', (event) => {
            event.preventDefault();
            event.stopPropagation();
        });
    }

    window.addEventListener('pointerdown', (event) => {
        if (!contextMenuEl || contextMenuEl.style.display !== 'block') return;
        if (contextMenuEl.contains(event.target)) return;
        hideContextMenu();
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            pushUndoSnapshot();
            state.points = [];
            clearSelectedPoint();
            renderPoints();
        });
    }

    if (deleteSelectedPointBtn) {
        deleteSelectedPointBtn.addEventListener('click', () => {
            if (!Number.isInteger(state.selectedPointIndex)) return;
            const index = state.selectedPointIndex;
            pushUndoSnapshot();
            state.points.splice(index, 1);
            clearSelectedPoint();
            renderPoints();
        });
    }

    if (clearWallsBtn) {
        clearWallsBtn.addEventListener('click', () => {
            pushUndoSnapshot();
            state.walls = [];
            state.pendingWallStart = null;
            clearSelectedWall();
            renderPoints();
        });
    }

    const saveFloorPlanState = async () => {
        if (!state.planId) return;

        [saveBtn, saveFabBtn].forEach((button) => {
            if (!button) return;
            button.disabled = true;
            button.dataset.originalLabel = button.textContent || 'Guardar';
            button.textContent = 'Guardando...';
        });

        try {
            const response = await fetch(`/admin/floor-plans/${state.planId}/points`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    points: state.points,
                    walls: state.walls,
                    rooms: state.rooms,
                    scale: planScale(),
                    structure_defaults: structureDefaults(),
                }),
            });
            const data = await response.json();
            if (!response.ok || !data.ok) return;
            footerEl.textContent = `Guardado ✓ ${data.points_count || state.points.length} AP · ${data.walls_count || state.walls.length} muros`;
            if (typeof window.itcityAlert === 'function') {
                window.itcityAlert({
                    icon: 'success',
                    title: 'Plano guardado',
                    text: 'Los cambios del diseño se guardaron correctamente.',
                    toast: true,
                    position: 'top-end',
                });
            }
        } catch {
            if (typeof window.itcityAlert === 'function') {
                window.itcityAlert({
                    icon: 'error',
                    title: 'Error al guardar',
                    text: 'No se pudieron guardar los cambios del plano.',
                    toast: true,
                    position: 'top-end',
                });
            }
        } finally {
            [saveBtn, saveFabBtn].forEach((button) => {
                if (!button) return;
                button.disabled = false;
                button.textContent = button.dataset.originalLabel || 'Guardar';
            });
        }
    };

    if (saveBtn) {
        saveBtn.addEventListener('click', saveFloorPlanState);
    }

    if (saveFabBtn) {
        saveFabBtn.addEventListener('click', saveFloorPlanState);
    }

    const modeSelect = document.getElementById('floorPlanModeSelect');
    const fileWrap = document.getElementById('floorPlanFileWrap');
    const fileInput = document.getElementById('floorPlanFileInput');
    const blankWrap = document.getElementById('floorPlanBlankSizeWrap');
    const branchSelect = document.querySelector('select[name="floor_plan_branch_id"]');
    const spaceSelect = document.getElementById('floorPlanSpaceSelect');
    
    const syncPlanMode = () => {
        const mode = modeSelect?.value || 'upload';
        const isBlank = mode === 'blank';
        if (fileWrap) fileWrap.style.display = isBlank ? 'none' : '';
        if (blankWrap) blankWrap.style.display = isBlank ? '' : 'none';
        if (fileInput) fileInput.required = !isBlank;
    };

    const filterSpacesByBranch = () => {
        if (!branchSelect || !spaceSelect) return;
        const selectedBranchId = branchSelect.value;
        const options = spaceSelect.querySelectorAll('option');
        
        options.forEach((opt) => {
            if (opt.value === '') {
                opt.style.display = '';
            } else {
                const optBranchId = opt.getAttribute('data-branch-id');
                opt.style.display = optBranchId === selectedBranchId ? '' : 'none';
            }
        });

        // Si la opción seleccionada ahora está oculta, selecciona "Sin asociar"
        if (spaceSelect.value !== '' && spaceSelect.querySelector(`option[value="${spaceSelect.value}"]`).style.display === 'none') {
            spaceSelect.value = '';
        }
    };

    if (modeSelect) {
        modeSelect.addEventListener('change', syncPlanMode);
        syncPlanMode();
    }

    if (branchSelect) {
        branchSelect.addEventListener('change', filterSpacesByBranch);
    }

    renderMaterialLegend();
})();
</script>
        footerEl.textContent = `${visibleApCount} AP visibles / ${totalApCount} AP totales · ${totalSymbolCount} símbolos TI · ${state.walls.length} muros/puertas/ventanas${scaleLabel}${selectedLabel}${selectedWallLabel}${selectedRoomLabel}`;
(function () {
    'use strict';

    const nameInput = document.getElementById('nodeTypeNameInput');
    const slugInput = document.getElementById('nodeTypeSlugInput');
    const iconInput = document.getElementById('nodeTypeIconInput');
    const previewShape = document.getElementById('nodeTypePreviewShape');
    const previewIcon = document.getElementById('nodeTypePreviewIcon');
    const previewName = document.getElementById('nodeTypePreviewName');
    const previewSlug = document.getElementById('nodeTypePreviewSlug');
    const previewHelper = document.getElementById('nodeTypePreviewHelper');

    if (!nameInput || !slugInput || !iconInput || !previewShape || !previewIcon || !previewName || !previewSlug || !previewHelper) {
        return;
    }

    const variants = [
        'variant-default',
        'variant-router',
        'variant-switch',
        'variant-firewall',
        'variant-access-point',
        'variant-vpn-gateway',
        'variant-server',
        'variant-database',
        'variant-load-balancer',
        'variant-ip-camera',
        'variant-printer',
        'variant-storage',
    ];

    const resolveVariant = (slug) => {
        const value = String(slug || '').toLowerCase();
        if (value.includes('router')) return 'variant-router';
        if (value.includes('switch')) return 'variant-switch';
        if (value.includes('firewall')) return 'variant-firewall';
        if (value.includes('access') || value.includes('ap')) return 'variant-access-point';
        if (value.includes('vpn')) return 'variant-vpn-gateway';
        if (value.includes('database') || value.includes('db') || value.includes('sql')) return 'variant-database';
        if (value.includes('load-balancer') || value.includes('balancer')) return 'variant-load-balancer';
        if (value.includes('camera')) return 'variant-ip-camera';
        if (value.includes('printer') || value.includes('print')) return 'variant-printer';
        if (value.includes('storage') || value.includes('nas')) return 'variant-storage';
        if (value.includes('server') || value.includes('serv')) return 'variant-server';
        return 'variant-default';
    };

    const syncPreview = () => {
        const name = nameInput.value.trim() || 'Nodo genérico';
        const slug = slugInput.value.trim() || 'generic-node';
        const icon = iconInput.value.trim() || 'N';
        const variant = resolveVariant(slug);

        previewShape.classList.remove(...variants);
        previewShape.classList.add(variant);
        previewIcon.textContent = icon.slice(0, 2).toUpperCase();
        previewName.textContent = name;
        previewSlug.textContent = slug;
        previewHelper.textContent = 'Vista previa estimada del elemento dentro del diagrama.';
    };

    [nameInput, slugInput, iconInput].forEach((input) => {
        input.addEventListener('input', syncPreview);
    });

    document.querySelectorAll('.node-type-preset').forEach((button) => {
        button.addEventListener('click', () => {
            nameInput.value = button.dataset.presetName || '';
            slugInput.value = button.dataset.presetSlug || '';
            iconInput.value = button.dataset.presetIcon || '';
            syncPreview();
            nameInput.focus();
        });
    });

    syncPreview();
})();
</script>
<script>
(function () {
    'use strict';
    const textarea = document.getElementById('nodeDetailsJson');
    if (!textarea) return;

    let ports = [];
    let mnemonic = '';
    let selectedIdx = null;

    const readObj = () => { try { return JSON.parse(textarea.value || '{}') || {}; } catch { return {}; } };

    const syncJson = () => {
        const obj = readObj();
        if (mnemonic) obj.mnemonic = mnemonic; else delete obj.mnemonic;
        obj.ports = ports;
        textarea.value = JSON.stringify(obj, null, 2);
    };

    const escH = (s) => String(s || '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
    const portCls = (p) => p.status === 'up' ? 'up' : p.status === 'down' ? 'down' : '';

    const DEVICE_DEFAULTS = {
        firewall: [
            { name: 'WAN',   status: 'up',     connected_to: '' },
            { name: 'LAN-1', status: 'up',     connected_to: '' },
            { name: 'LAN-2', status: 'unused', connected_to: '' },
            { name: 'LAN-3', status: 'unused', connected_to: '' },
            { name: 'DMZ',   status: 'unused', connected_to: '' },
            { name: 'MGMT',  status: 'unused', connected_to: '' },
        ],
        'access-point': [
            { name: '2.4 GHz', status: 'up',     connected_to: '', ssid: '' },
            { name: '5 GHz',   status: 'up',     connected_to: '', ssid: '' },
            { name: '6 GHz',   status: 'unused', connected_to: '', ssid: '' },
            { name: 'Uplink',  status: 'up',     connected_to: '', ssid: '' },
        ],
        switch: { count: 24 },
        router: { count: 8 },
    };

    const renderPorts = () => {
        const grid = document.getElementById('portGrid');
        if (!grid) return;
        if (!ports.length) {
            grid.innerHTML = '<span style="color:#64748b;font-size:.7rem">Sin puertos — selecciona una cantidad arriba</span>';
            return;
        }
        grid.innerHTML = ports.map((p, i) => {
            const sel = i === selectedIdx ? ' sel' : '';
            const connTitle = p.connected_to ? ` → ${escH(p.connected_to)}` : '';
            return `<button type="button" class="pv-port ${portCls(p)}${sel}" data-pidx="${i}" title="${escH(p.name)}${connTitle}">${escH(p.name)}</button>`;
        }).join('');
    };

    const highlightCount = (count) => {
        document.querySelectorAll('.port-count-btn').forEach(b => b.classList.toggle('active', +b.dataset.count === count));
    };

    const setPorts = (count) => {
        const prev = ports.slice();
        ports = Array.from({ length: count }, (_, i) => prev[i] || { name: `P${i + 1}`, status: 'unused', connected_to: '' });
        if (selectedIdx !== null && selectedIdx >= count) {
            selectedIdx = null;
            document.getElementById('portEditor').style.display = 'none';
        }
        renderPorts();
        syncJson();
        highlightCount(count);
        document.getElementById('portCountCustom').value = count;
    };

    const openEditor = (idx) => {
        selectedIdx = idx;
        renderPorts();
        const p = ports[idx];
        document.getElementById('portEditorTitle').textContent = `Puerto ${p.name}  (${idx + 1} / ${ports.length})`;
        document.getElementById('pvPortName').value = p.name;
        document.getElementById('pvPortStatus').value = p.status;
        document.getElementById('pvPortConnected').value = p.connected_to;
        document.getElementById('portEditor').style.display = '';
        document.getElementById('pvPortName').focus();
    };

    const closeEditor = () => {
        selectedIdx = null;
        renderPorts();
        document.getElementById('portEditor').style.display = 'none';
    };

    const init = () => {
        const obj = readObj();
        mnemonic = obj.mnemonic || '';
        const mnInput = document.getElementById('portMnemonic');
        if (mnInput) mnInput.value = mnemonic;
        const lbl = document.getElementById('pvDeviceLabel');
        if (lbl) lbl.textContent = mnemonic || '— sin mnemónico —';
        if (Array.isArray(obj.ports) && obj.ports.length) {
            ports = obj.ports.map((p, i) => ({
                name: p.name || `P${i + 1}`,
                status: p.status || (p.up ? 'up' : 'unused'),
                connected_to: p.connected_to || '',
            }));
            renderPorts();
            highlightCount(ports.length);
            document.getElementById('portCountCustom').value = ports.length;
        } else {
            renderPorts();
        }
    };

    document.getElementById('portGrid').addEventListener('click', (e) => {
        const btn = e.target.closest('.pv-port');
        if (!btn) return;
        const idx = +btn.dataset.pidx;
        if (idx === selectedIdx) { closeEditor(); } else { openEditor(idx); }
    });

    document.getElementById('pvPortSave').addEventListener('click', () => {
        if (selectedIdx === null) return;
        ports[selectedIdx] = {
            name: document.getElementById('pvPortName').value.trim() || `P${selectedIdx + 1}`,
            status: document.getElementById('pvPortStatus').value,
            connected_to: document.getElementById('pvPortConnected').value.trim(),
        };
        renderPorts();
        syncJson();
        const p = ports[selectedIdx];
        document.getElementById('portEditorTitle').textContent = `Puerto ${p.name}  (${selectedIdx + 1} / ${ports.length})`;
    });

    document.getElementById('portEditorClose').addEventListener('click', closeEditor);

    // Smart defaults when node type is changed (only when ports are empty)
    const nodeTypeSelect = document.getElementById('nodeTypeSelect');
    if (nodeTypeSelect) {
        nodeTypeSelect.addEventListener('change', () => {
            if (ports.length > 0) return; // don't overwrite configured ports
            const selected = nodeTypeSelect.options[nodeTypeSelect.selectedIndex];
            const slug = (selected?.dataset?.slug || '').toLowerCase();
            const def = Object.entries(DEVICE_DEFAULTS).find(([k]) => slug.includes(k))?.[1];
            if (!def) return;
            if (Array.isArray(def)) {
                ports = def.map(p => ({ name: p.name, status: p.status, connected_to: p.connected_to }));
                renderPorts();
                syncJson();
                highlightCount(ports.length);
                document.getElementById('portCountCustom').value = ports.length;
            } else if (def.count) {
                setPorts(def.count);
            }
        });
    }

    document.querySelectorAll('.port-count-btn').forEach(btn => {
        btn.addEventListener('click', () => setPorts(+btn.dataset.count));
    });

    const customInput = document.getElementById('portCountCustom');
    customInput.addEventListener('change', () => {
        const n = Math.max(1, Math.min(96, parseInt(customInput.value, 10) || 1));
        customInput.value = n;
        setPorts(n);
    });

    document.getElementById('portMnemonic').addEventListener('input', (e) => {
        mnemonic = e.target.value.trim();
        document.getElementById('pvDeviceLabel').textContent = mnemonic || '— sin mnemónico —';
        syncJson();
    });

    document.getElementById('toggleJsonBtn').addEventListener('click', () => {
        const sec = document.getElementById('jsonSection');
        sec.style.display = sec.style.display === 'none' ? '' : 'none';
    });

    document.getElementById('hideJsonBtn').addEventListener('click', () => {
        document.getElementById('jsonSection').style.display = 'none';
    });

    textarea.addEventListener('blur', init);

    init();
})();
</script>
<script>
(function () {
    'use strict';

    const summaryUrl = @json(url('/red/resumen'));
    const availabilityEl = document.getElementById('opsAvailability');
    const latencyEl = document.getElementById('opsLatency');
    const monitoredEl = document.getElementById('opsMonitored');
    const activeEl = document.getElementById('opsActiveNodes');
    const metaEl = document.getElementById('opsSummaryMeta');
    const refreshBtn = document.getElementById('btnRefreshOpsSummary');

    const loadSummary = async () => {
        if (!summaryUrl || !availabilityEl || !latencyEl || !monitoredEl || !activeEl || !metaEl) return;

        metaEl.textContent = 'Consultando métricas...';

        try {
            const response = await fetch(summaryUrl, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });

            if (!response.ok) {
                throw new Error('No se pudo cargar resumen');
            }

            const payload = await response.json();
            const summary = payload?.summary || {};

            availabilityEl.textContent = summary.availability_rate !== null && summary.availability_rate !== undefined
                ? `${summary.availability_rate}%`
                : '—';
            latencyEl.textContent = summary.avg_latency_ms !== null && summary.avg_latency_ms !== undefined
                ? `${summary.avg_latency_ms} ms`
                : '—';
            monitoredEl.textContent = `${summary.nodes_monitored ?? 0}`;
            activeEl.textContent = `${summary.nodes_active ?? 0}`;

            const now = new Date();
            metaEl.textContent = `Última actualización: ${now.toLocaleTimeString()}`;
        } catch (error) {
            availabilityEl.textContent = '—';
            latencyEl.textContent = '—';
            monitoredEl.textContent = '—';
            activeEl.textContent = '—';
            metaEl.textContent = 'No fue posible cargar el resumen operativo.';
        }
    };

    if (refreshBtn) {
        refreshBtn.addEventListener('click', loadSummary);
    }

    const branchFilter = document.getElementById('nodeFilterBranch');
    const statusFilter = document.getElementById('nodeFilterStatus');
    const monitoredOnlyFilter = document.getElementById('nodeFilterMonitoredOnly');
    const resetFilterBtn = document.getElementById('nodeFilterReset');
    const filterCountEl = document.getElementById('nodeFilterCount');
    const nodeRows = Array.from(document.querySelectorAll('tr.node-row'));
    const nodeFilterStorageKey = 'itcity2.admin.nodes.filters';

    const persistNodeFilters = () => {
        try {
            const payload = {
                branch: branchFilter?.value || '',
                status: statusFilter?.value || '',
                monitoredOnly: !!monitoredOnlyFilter?.checked,
            };
            localStorage.setItem(nodeFilterStorageKey, JSON.stringify(payload));
        } catch (error) {
        }
    };

    const restoreNodeFilters = () => {
        try {
            const raw = localStorage.getItem(nodeFilterStorageKey);
            if (!raw) return;

            const payload = JSON.parse(raw);
            if (branchFilter && typeof payload?.branch === 'string') branchFilter.value = payload.branch;
            if (statusFilter && typeof payload?.status === 'string') statusFilter.value = payload.status;
            if (monitoredOnlyFilter) monitoredOnlyFilter.checked = !!payload?.monitoredOnly;
        } catch (error) {
        }
    };

    const applyNodeFilters = () => {
        if (!nodeRows.length) return;

        const branchValue = (branchFilter?.value || '').trim();
        const statusValue = (statusFilter?.value || '').trim().toLowerCase();
        const monitoredOnly = !!monitoredOnlyFilter?.checked;

        let visibleCount = 0;

        nodeRows.forEach((row) => {
            const matchesBranch = !branchValue || row.dataset.branchId === branchValue;
            const rowStatus = (row.dataset.status || '').toLowerCase();
            const matchesStatus = !statusValue || rowStatus === statusValue;
            const matchesMonitored = !monitoredOnly || row.dataset.monitored === '1';

            const visible = matchesBranch && matchesStatus && matchesMonitored;
            row.style.display = visible ? '' : 'none';

            if (visible) {
                visibleCount += 1;
            }
        });

        if (filterCountEl) {
            filterCountEl.textContent = `${visibleCount} nodo(s) visibles de ${nodeRows.length}`;
        }
    };

    branchFilter?.addEventListener('change', () => {
        persistNodeFilters();
        applyNodeFilters();
    });
    statusFilter?.addEventListener('change', () => {
        persistNodeFilters();
        applyNodeFilters();
    });
    monitoredOnlyFilter?.addEventListener('change', () => {
        persistNodeFilters();
        applyNodeFilters();
    });
    resetFilterBtn?.addEventListener('click', () => {
        if (branchFilter) branchFilter.value = '';
        if (statusFilter) statusFilter.value = '';
        if (monitoredOnlyFilter) monitoredOnlyFilter.checked = false;
        persistNodeFilters();
        applyNodeFilters();
    });

    loadSummary();
    restoreNodeFilters();
    applyNodeFilters();
})();
</script>
<script>
(function () {
    'use strict';

    const navLinks = Array.from(document.querySelectorAll('.crud-nav-link[href^="#crud-"]'));
    const sectionIds = navLinks
        .map((link) => link.getAttribute('href'))
        .filter((href) => typeof href === 'string' && href.startsWith('#'));

    const sections = sectionIds
        .map((id) => document.querySelector(id))
        .filter((section) => !!section);

    if (!navLinks.length || !sections.length) {
        return;
    }

    const setActive = (id) => {
        navLinks.forEach((link) => {
            const isActive = link.getAttribute('href') === id;
            link.classList.toggle('active', isActive);
            if (isActive) {
                link.setAttribute('aria-current', 'true');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };

    const refreshActiveByScroll = () => {
        const offset = 170;
        let currentId = sectionIds[0] ?? null;

        sections.forEach((section) => {
            const top = section.getBoundingClientRect().top;
            if (top - offset <= 0) {
                currentId = `#${section.id}`;
            }
        });

        if (currentId) {
            setActive(currentId);
        }
    };

    navLinks.forEach((link) => {
        link.addEventListener('click', () => {
            const id = link.getAttribute('href');
            if (id) {
                setActive(id);
            }
        });
    });

    refreshActiveByScroll();
    window.addEventListener('scroll', refreshActiveByScroll, { passive: true });
})();
</script>
@endpush
