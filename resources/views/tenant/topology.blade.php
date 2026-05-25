<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Topología Global | ITCity</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; height: 100%; background: #0f172a; font-family: system-ui, sans-serif; }

        /* ── Toolbar ─────────────────────────────────────────── */
        .topo-toolbar {
            height: 52px; display: flex; align-items: center; gap: 8px;
            padding: 0 16px; background: #1e293b; border-bottom: 1px solid #334155;
            flex-shrink: 0; flex-wrap: wrap;
        }
        .topo-brand { color: #94a3b8; font-size: .82rem; font-weight: 600; margin-right: 8px; white-space: nowrap; }
        .topo-brand a { color: #60a5fa; text-decoration: none; }
        .topo-sep { color: #475569; }
        .topo-btn {
            border: 1px solid #334155; background: #1e293b; color: #cbd5e1;
            border-radius: 7px; padding: 5px 11px; font-size: .78rem; font-weight: 600;
            cursor: pointer; transition: background .12s, color .12s; white-space: nowrap;
            text-decoration: none; display: inline-flex; align-items: center; gap: 4px;
        }
        .topo-btn:hover { background: #334155; color: #f1f5f9; }
        .topo-btn.active { background: #2563eb; color: #fff; border-color: #2563eb; }
        .topo-btn.danger { border-color: #dc2626; color: #fca5a5; }
        .topo-btn.danger:hover { background: #7f1d1d; }
        .topo-btn.primary { background: #2563eb; color: #fff; border-color: #2563eb; }
        .topo-btn.primary:disabled { opacity: .45; cursor: default; }
        .topo-status { font-size: .75rem; color: #64748b; margin-left: auto; white-space: nowrap; }
        .topo-status.ok  { color: #4ade80; }
        .topo-status.err { color: #f87171; }

        /* ── Layout ──────────────────────────────────────────── */
        .topo-layout { display: flex; height: calc(100vh - 52px); overflow: hidden; }

        /* ── Left panel ──────────────────────────────────────── */
        .topo-panel {
            width: 210px; flex-shrink: 0; background: #1e293b; border-right: 1px solid #334155;
            overflow-y: auto; display: flex; flex-direction: column; gap: 1px;
        }
        .topo-panel-section { padding: 12px 14px; border-bottom: 1px solid #334155; }
        .topo-panel-title { font-size: .67rem; text-transform: uppercase; letter-spacing: .09em; color: #475569; font-weight: 700; margin-bottom: 10px; }
        .layer-controls { display: grid; grid-template-columns: 1fr; gap: 6px; }
        .layer-btn { width: 100%; justify-content: center; font-size: .72rem; }

        .legend-item { display: flex; align-items: center; gap: 7px; font-size: .74rem; color: #94a3b8; margin-bottom: 6px; }
        .legend-dot  { width: 11px; height: 11px; border-radius: 3px; flex-shrink: 0; }
        .legend-cnt  { margin-left: auto; font-size: .68rem; color: #475569; }

        .legend-edge { display: flex; align-items: center; gap: 7px; font-size: .72rem; color: #94a3b8; margin-bottom: 5px; }
        .edge-sample { display: inline-block; width: 28px; height: 3px; border-radius: 2px; flex-shrink: 0; }
        .edge-sample.gray    { background: #94a3b8; }
        .edge-sample.blue    { background: #2563eb; }
        .edge-sample.purple  { background: #9333ea; border-top: 3px dashed #9333ea; height: 0; }
        .edge-sample.orange  { background: transparent; border-top: 3px dashed #f97316; height: 0; }
        .edge-sample.teal    { background: transparent; border-top: 3px dotted #0891b2; height: 0; }
        .edge-sample.green   { background: transparent; border-top: 3px dashed #16a34a; height: 0; }

        /* ── Inspector ───────────────────────────────────────── */
        .inspector-empty { font-size: .76rem; color: #475569; }
        .insp-type { font-size: .64rem; text-transform: uppercase; letter-spacing: .08em; color: #475569; margin-bottom: 2px; }
        .insp-name { font-size: .92rem; font-weight: 700; color: #f1f5f9; margin-bottom: 6px; }
        .insp-meta { font-size: .75rem; color: #94a3b8; margin-bottom: 3px; }
        .insp-status.active   { color: #4ade80; }
        .insp-status.warning  { color: #fbbf24; }
        .insp-status.error    { color: #f87171; }
        .insp-status.inactive { color: #64748b; }
        .insp-pills { display: flex; flex-wrap: wrap; gap: 4px; margin: 8px 0; }
        .insp-pill  { background: #0f172a; border: 1px solid #334155; border-radius: 999px; padding: 2px 8px; font-size: .68rem; color: #94a3b8; }
        .insp-actions { display: flex; flex-direction: column; gap: 5px; margin-top: 10px; }
        .insp-actions .topo-btn { justify-content: center; font-size: .75rem; }
        .drill-list { margin-top: 10px; display: flex; flex-direction: column; gap: 6px; max-height: 220px; overflow: auto; }
        .drill-item {
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 8px;
            background: #0f172a;
        }
        .drill-item-main { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
        .drill-item-name { font-size: .74rem; color: #e2e8f0; font-weight: 600; }
        .drill-item-meta { font-size: .68rem; color: #64748b; margin-top: 3px; }
        .drill-item-actions { display: flex; gap: 5px; margin-top: 6px; }
        .drill-item-actions .topo-btn { padding: 3px 7px; font-size: .67rem; }
        .node-modal-tabs { display: flex; gap: 8px; margin-bottom: 12px; }
        .node-modal-tab {
            border: 1px solid #334155;
            background: #0f172a;
            color: #cbd5e1;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: .75rem;
            font-weight: 600;
            cursor: pointer;
        }
        .node-modal-tab.active { background: #2563eb; border-color: #2563eb; color: #fff; }
        .node-pane { display: none; }
        .node-pane.active { display: block; }
        .software-list { max-height: 180px; overflow: auto; display: flex; flex-direction: column; gap: 8px; margin-bottom: 10px; }
        .software-item { border: 1px solid #334155; border-radius: 8px; padding: 8px; background: #0f172a; }
        .software-item .title { font-size: .78rem; color: #e2e8f0; font-weight: 700; }
        .software-item .meta { font-size: .68rem; color: #94a3b8; }
        .software-item .actions { margin-top: 6px; display: flex; gap: 6px; }

        /* ── Canvas ──────────────────────────────────────────── */
        .topo-canvas-wrap { flex: 1; overflow: hidden; position: relative; background: #0f172a; }
        #topoCanvas { width: 100%; height: 100%; display: block; }

        /* ── SVG styles ──────────────────────────────────────── */
        .edge-flow { stroke-dasharray: 7 5; animation: edgeFlow 1.8s linear infinite; }
        @keyframes edgeFlow { from { stroke-dashoffset: 0; } to { stroke-dashoffset: -48; } }

        /* ── Edge type modal ─────────────────────────────────── */
        .topo-modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 9000;
            display: flex; align-items: center; justify-content: center;
        }
        .topo-modal {
            background: #1e293b; border: 1px solid #334155; border-radius: 14px;
            padding: 24px; min-width: 340px; box-shadow: 0 24px 60px rgba(0,0,0,.5);
        }
        .topo-modal-title { font-size: 1rem; font-weight: 700; color: #f1f5f9; margin-bottom: 16px; }
        .topo-modal-subtitle { font-size: .78rem; color: #94a3b8; margin-bottom: 12px; }
        .edge-type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; }
        .edge-type-btn {
            border-radius: 10px; padding: 12px 10px; cursor: pointer;
            background: #0f172a; border: 2px solid transparent; color: #f1f5f9;
            display: flex; flex-direction: column; align-items: center; gap: 4px;
            transition: border-color .12s, background .12s; font-size: .78rem; font-weight: 600;
        }
        .edge-type-btn:hover { background: #1e293b; }
        .edge-type-btn.active { background: #1d4ed8; border-color: #60a5fa !important; }
        .etb-icon { font-size: 1.5rem; }
        .topo-field { margin-bottom: 10px; }
        .topo-field label { display: block; font-size: .72rem; color: #94a3b8; margin-bottom: 4px; }
        .topo-field input,
        .topo-field select,
        .topo-field textarea {
            width: 100%;
            border: 1px solid #334155;
            border-radius: 8px;
            background: #0f172a;
            color: #e2e8f0;
            padding: 8px 10px;
            font-size: .77rem;
        }
        .topo-field textarea { min-height: 62px; resize: vertical; }
        .node-type-preview {
            border: 1px solid #334155;
            border-radius: 12px;
            background: #0f172a;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }
        .node-type-preview-shape {
            width: 62px;
            height: 62px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            color: #fff;
            font-size: .82rem;
            font-weight: 800;
            letter-spacing: .04em;
            box-shadow: 0 10px 22px rgba(0,0,0,.22);
            flex-shrink: 0;
        }
        .node-type-preview-shape span { position: relative; z-index: 2; }
        .node-type-preview-shape.variant-default { background:#1d4ed8; border-radius:999px; }
        .node-type-preview-shape.variant-router { background:#b45309; clip-path:polygon(25% 6%, 75% 6%, 100% 50%, 75% 94%, 25% 94%, 0% 50%); }
        .node-type-preview-shape.variant-switch { background:#6d28d9; width:72px; height:46px; border-radius:10px; }
        .node-type-preview-shape.variant-firewall { background:#b91c1c; clip-path:polygon(50% 0%, 92% 18%, 92% 62%, 50% 100%, 8% 62%, 8% 18%); }
        .node-type-preview-shape.variant-access-point { background:#4338ca; border-radius:999px; }
        .node-type-preview-shape.variant-vpn-gateway { background:#7c3aed; border-radius:10px; transform:rotate(45deg); }
        .node-type-preview-shape.variant-vpn-gateway span { transform:rotate(-45deg); }
        .node-type-preview-shape.variant-server { background:#0e7490; border-radius:10px; }
        .node-type-preview-shape.variant-database { background:#0f766e; border-radius:18px; }
        .node-type-preview-shape.variant-database::before, .node-type-preview-shape.variant-database::after { content:''; position:absolute; left:9px; right:9px; height:10px; border:1.5px solid rgba(255,255,255,.42); border-radius:999px / 60%; }
        .node-type-preview-shape.variant-database::before { top:10px; }
        .node-type-preview-shape.variant-database::after { bottom:10px; }
        .node-type-preview-shape.variant-load-balancer { background:#0284c7; border-radius:12px; width:74px; height:48px; }
        .node-type-preview-shape.variant-load-balancer::before { content:'⇄'; position:absolute; font-size:1.2rem; opacity:.26; }
        .node-type-preview-shape.variant-pbx { background:#047857; border-radius:16px; }
        .node-type-preview-shape.variant-pbx::before { content:''; position:absolute; inset:15px; border:2px dashed rgba(255,255,255,.24); border-radius:10px; }
        .node-type-preview-shape.variant-ip-camera { background:#475569; border-radius:999px; }
        .node-type-preview-shape.variant-ip-camera::before { content:''; position:absolute; width:14px; height:14px; border-radius:999px; background:rgba(255,255,255,.22); box-shadow:0 0 0 5px rgba(255,255,255,.08); }
        .node-type-preview-shape.variant-printer { background:#334155; border-radius:10px; width:72px; height:50px; }
        .node-type-preview-shape.variant-printer::before { content:''; position:absolute; top:-8px; width:32px; height:16px; background:#e2e8f0; border-radius:4px 4px 0 0; }
        .node-type-preview-shape.variant-storage { background:#0f766e; border-radius:10px; }
        .node-type-preview-meta { min-width: 0; }
        .node-type-preview-name { color:#f1f5f9; font-size:.86rem; font-weight:700; }
        .node-type-preview-slug { color:#94a3b8; font-family:Consolas, monospace; font-size:.72rem; }
        .node-type-preview-help { color:#64748b; font-size:.72rem; margin-top:3px; }
        .topo-check { display: flex; align-items: center; gap: 7px; font-size: .74rem; color: #cbd5e1; margin-bottom: 12px; }
        .topo-check input { accent-color: #16a34a; }
        .topo-modal-footer { display: flex; justify-content: flex-end; gap: 8px; }

        /* ── Shortcut hint ───────────────────────────────────── */
        .kbd { background: #0f172a; border: 1px solid #334155; border-radius: 4px; padding: 1px 5px; font-family: monospace; font-size: .7rem; color: #64748b; }
    </style>
</head>
<body>

{{-- ── Toolbar ─────────────────────────────────────────────── --}}
<header class="topo-toolbar">
    <div class="topo-brand">
        <a href="{{ url('/') }}">🏙 ITCity</a>
        <span style="margin: 0 4px; color:#475569">/</span>
        <span>Topología Global</span>
    </div>

    <button id="btnSelect"  class="topo-btn active" title="Seleccionar (V)">▶ Seleccionar</button>
    <button id="btnConnect" class="topo-btn" title="Conectar nodos (C)">⚡ Conectar</button>

    <span class="topo-sep">|</span>

    <button id="btnAutoLayout" class="topo-btn" title="Distribuir nodos automáticamente">⊞ Auto-layout</button>
    <button id="btnFocusServers" class="topo-btn" title="Enfocar servidores en el diagrama">🖥 Servidores</button>
    <button id="btnAddNode" class="topo-btn" title="Agregar nuevo elemento">＋ Nuevo elemento</button>
    <button id="btnZoomIn"     class="topo-btn">＋</button>
    <button id="btnZoomOut"    class="topo-btn">－</button>
    <button id="btnResetView"  class="topo-btn">⌂</button>

    <span class="topo-sep">|</span>

    <button id="btnSaveLayout" class="topo-btn primary" disabled>💾 Guardar layout</button>
    <button id="btnExportPng"  class="topo-btn">📷 PNG</button>

    <span class="topo-sep">|</span>

    <a href="{{ url('/admin') }}" class="topo-btn">⚙ Admin</a>
    <a href="{{ url('/') }}"      class="topo-btn">← Ciudad</a>

    <span class="topo-status" id="topoStatus">
        <span class="kbd">V</span> Seleccionar &nbsp;
        <span class="kbd">C</span> Conectar &nbsp;
        <span class="kbd">Esc</span> Cancelar
    </span>
</header>

{{-- ── Main layout ──────────────────────────────────────────── --}}
<div class="topo-layout">

    {{-- Left panel --}}
    <aside class="topo-panel">
        <div class="topo-panel-section">
            <div class="topo-panel-title">Dispositivos</div>
            <div id="legendTypes"></div>
        </div>
        <div class="topo-panel-section">
            <div class="topo-panel-title">Tipos de conexión</div>
            <div class="legend-edge"><span class="edge-sample gray"></span> Enlace directo</div>
            <div class="legend-edge"><span class="edge-sample blue" style="height:3px"></span> Fibra óptica</div>
            <div class="legend-edge"><span class="edge-sample purple"></span> VPN</div>
            <div class="legend-edge"><span class="edge-sample orange"></span> WAN / Internet</div>
            <div class="legend-edge"><span class="edge-sample teal"></span> Inalámbrico</div>
            <div class="legend-edge"><span class="edge-sample green"></span> Inter-campus</div>
        </div>
        <div class="topo-panel-section">
            <div class="topo-panel-title">Capas</div>
            <div class="layer-controls">
                <button id="btnLayerAll" class="topo-btn layer-btn active" type="button">🌐 Todas</button>
                <button id="btnLayerAP" class="topo-btn layer-btn" type="button">📶 Access Point</button>
                <button id="btnLayerServers" class="topo-btn layer-btn" type="button">🖥 Servidores</button>
                <button id="btnLayerSoftware" class="topo-btn layer-btn" type="button">💿 Software</button>
                <button id="btnLayerCriticalLinks" class="topo-btn layer-btn" type="button">🚨 Enlaces críticos</button>
            </div>
        </div>
        <div class="topo-panel-section">
            <div class="topo-panel-title">Traceroute visual</div>
            <div class="topo-field" style="margin-bottom:8px;">
                <label for="traceSourceSelect">Origen</label>
                <select id="traceSourceSelect"></select>
            </div>
            <div class="topo-field" style="margin-bottom:8px;">
                <label for="traceTargetSelect">Destino (nodo o software)</label>
                <select id="traceTargetSelect"></select>
            </div>
            <div class="topo-modal-footer" style="justify-content:stretch; gap:6px;">
                <button id="btnRunTrace" class="topo-btn primary" type="button" style="flex:1; justify-content:center;">Trazar</button>
                <button id="btnClearTrace" class="topo-btn" type="button" style="flex:1; justify-content:center;">Limpiar</button>
            </div>
            <div id="traceSummary" class="inspector-empty" style="margin-top:8px;">Selecciona origen y destino para resaltar la ruta.</div>
        </div>
        <div class="topo-panel-section" id="inspectorSection">
            <div class="topo-panel-title">Inspector</div>
            <div id="inspectorContent" class="inspector-empty">Haz clic en un nodo o conexión.</div>
        </div>
        <div class="topo-panel-section" id="drilldownSection">
            <div class="topo-panel-title">Drill-down</div>
            <div id="drilldownContent" class="inspector-empty">Selecciona una sede o un nodo para explorar el detalle.</div>
        </div>
    </aside>

    {{-- Canvas --}}
    <div class="topo-canvas-wrap" id="canvasWrap">
        <svg id="topoCanvas" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
            <defs>
                <marker id="arr"       markerWidth="9" markerHeight="7" refX="8" refY="3.5" orient="auto" markerUnits="strokeWidth"><polygon points="0 0,9 3.5,0 7" fill="#64748b"/></marker>
                <marker id="arr-vpn"   markerWidth="9" markerHeight="7" refX="8" refY="3.5" orient="auto" markerUnits="strokeWidth"><polygon points="0 0,9 3.5,0 7" fill="#9333ea"/></marker>
                <marker id="arr-wan"   markerWidth="9" markerHeight="7" refX="8" refY="3.5" orient="auto" markerUnits="strokeWidth"><polygon points="0 0,9 3.5,0 7" fill="#f97316"/></marker>
                <marker id="arr-fiber" markerWidth="9" markerHeight="7" refX="8" refY="3.5" orient="auto" markerUnits="strokeWidth"><polygon points="0 0,9 3.5,0 7" fill="#2563eb"/></marker>
                <marker id="arr-green" markerWidth="9" markerHeight="7" refX="8" refY="3.5" orient="auto" markerUnits="strokeWidth"><polygon points="0 0,9 3.5,0 7" fill="#16a34a"/></marker>
                <marker id="arr-teal"  markerWidth="9" markerHeight="7" refX="8" refY="3.5" orient="auto" markerUnits="strokeWidth"><polygon points="0 0,9 3.5,0 7" fill="#0891b2"/></marker>
                <pattern id="bg-grid" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#1e293b" stroke-width="1"/>
                </pattern>
            </defs>
            <rect id="bgRect" x="0" y="0" width="1800" height="1100" fill="url(#bg-grid)"/>
            <g id="viewport">
                <g id="zonesLayer"></g>
                <g id="edgesLayer">
                    <line id="tempLine" x1="0" y1="0" x2="0" y2="0"
                        stroke="#38bdf8" stroke-width="2" stroke-dasharray="6 3" opacity="0" pointer-events="none"/>
                </g>
                <g id="nodesLayer"></g>
            </g>
        </svg>
    </div>
</div>

{{-- ── Edge type picker modal ───────────────────────────────── --}}
<div id="edgeModal" class="topo-modal-overlay" style="display:none">
    <div class="topo-modal">
        <div class="topo-modal-title">Tipo de conexión</div>
        <div class="topo-modal-subtitle" id="edgeModalNodes"></div>
        <div class="edge-type-grid" id="edgeTypeGrid"></div>
        <div class="topo-field">
            <label for="edgeFromNode">Nodo origen</label>
            <select id="edgeFromNode"></select>
        </div>
        <div class="topo-field">
            <label for="edgeToNode">Nodo destino</label>
            <select id="edgeToNode"></select>
        </div>
        <div class="topo-field">
            <label for="edgeFromEndpoint">Asignación en equipo origen (puerto/interfaz)</label>
            <input id="edgeFromEndpoint" type="text" maxlength="120" placeholder="Ej. Gi0/1, ETH1, VLAN-TRUNK">
        </div>
        <div class="topo-field">
            <label for="edgeToEndpoint">Asignación en equipo destino (puerto/interfaz)</label>
            <input id="edgeToEndpoint" type="text" maxlength="120" placeholder="Ej. Gi0/24, WAN1, UPLINK-A">
        </div>
        <div class="topo-field" id="edgeVpnProfileWrap" style="display:none">
            <label for="edgeVpnProfile">Perfil/Túnel VPN</label>
            <input id="edgeVpnProfile" type="text" maxlength="120" placeholder="Ej. VPN-CAMPUS-MTY-CDMX">
        </div>
        <div class="topo-field">
            <label for="edgePreferredWeight">Peso preferido para traceroute (opcional)</label>
            <input id="edgePreferredWeight" type="number" min="1" max="999" placeholder="1 = preferente, 9 = costoso">
        </div>
        <label class="topo-check" for="edgeInterCampus">
            <input id="edgeInterCampus" type="checkbox">
            <span>Conexión inter-campus</span>
        </label>
        <div class="topo-field">
            <label for="edgeNotes">Notas de enlace (opcional)</label>
            <textarea id="edgeNotes" maxlength="500" placeholder="Detalles técnicos, capacidad, proveedor, etc."></textarea>
        </div>
        <div class="topo-modal-footer">
            <button id="edgeModalCancel" class="topo-btn">Cancelar</button>
            <button id="edgeModalCreate" class="topo-btn primary" disabled>Guardar conexión</button>
        </div>
    </div>
</div>

<div id="nodeModal" class="topo-modal-overlay" style="display:none">
    <div class="topo-modal" style="min-width:420px; max-width:520px; width:94vw;">
        <div class="topo-modal-title" id="nodeModalTitle">Nuevo elemento</div>
        <div class="topo-modal-subtitle" id="nodeModalSubtitle">Configura características del elemento de red.</div>
        <div class="node-modal-tabs">
            <button id="nodeTabGeneral" class="node-modal-tab active">General</button>
            <button id="nodeTabSoftware" class="node-modal-tab">Software</button>
        </div>

        <div id="nodePaneGeneral" class="node-pane active">
            <div class="topo-field">
                <label for="nodeBranchId">Sede</label>
                <select id="nodeBranchId"></select>
            </div>
            <div class="topo-field">
                <label for="nodeTypeId">Tipo de elemento</label>
                <select id="nodeTypeId"></select>
            </div>
            <div class="node-type-preview">
                <div id="nodeTypePreviewShape" class="node-type-preview-shape variant-default"><span id="nodeTypePreviewIcon">N</span></div>
                <div class="node-type-preview-meta">
                    <div id="nodeTypePreviewName" class="node-type-preview-name">Nodo</div>
                    <div id="nodeTypePreviewSlug" class="node-type-preview-slug">slug: generic-node</div>
                    <div id="nodeTypePreviewHelp" class="node-type-preview-help">Vista rápida del elemento que se agregará al diagrama.</div>
                </div>
            </div>
            <div class="topo-field">
                <label for="nodeName">Nombre</label>
                <input id="nodeName" type="text" maxlength="150" placeholder="Ej. SRV-APP-CDMX-02">
            </div>
            <div class="topo-field">
                <label for="nodeIpAddress">IP</label>
                <input id="nodeIpAddress" type="text" maxlength="45" placeholder="Ej. 10.10.20.40">
            </div>
            <div class="topo-field">
                <label for="nodeStatus">Estado</label>
                <select id="nodeStatus">
                    <option value="active">Activo</option>
                    <option value="warning">Warning</option>
                    <option value="error">Error</option>
                    <option value="inactive">Inactivo</option>
                </select>
            </div>
            <div class="topo-field">
                <label for="nodeFloor">Piso</label>
                <input id="nodeFloor" type="text" maxlength="40" placeholder="PB">
            </div>
            <div class="topo-field">
                <label for="nodeRoom">Cuarto/Zona</label>
                <input id="nodeRoom" type="text" maxlength="80" placeholder="DC-01">
            </div>
            <div class="topo-field">
                <label for="nodeCableType">Cableado</label>
                <input id="nodeCableType" type="text" maxlength="80" placeholder="Fibra, Cat6, WiFi">
            </div>
            <label class="topo-check" for="nodeIsMonitored">
                <input id="nodeIsMonitored" type="checkbox" checked>
                <span>Monitoreado</span>
            </label>
            <div class="topo-field">
                <label for="nodeDetails">Características (JSON opcional)</label>
                <textarea id="nodeDetails" maxlength="2000" placeholder='Ej. {"rack":"R2","os":"Ubuntu 24.04"}'></textarea>
            </div>
        </div>

        <div id="nodePaneSoftware" class="node-pane">
            <div id="nodeSoftwareHint" class="inspector-empty" style="margin-bottom:10px;">Guarda primero el nodo para administrar software.</div>
            <div class="topo-field">
                <label for="softwareSearch">Buscar software</label>
                <div style="display:flex; gap:6px; align-items:center;">
                    <input id="softwareSearch" type="text" maxlength="120" placeholder="Filtrar por nombre, vendor o proyecto">
                    <button id="softwareSearchClear" class="topo-btn" type="button" title="Limpiar búsqueda">✕</button>
                </div>
            </div>
            <div id="softwareResultCount" class="insp-meta" style="margin-bottom:8px;">0 resultados</div>
            <div id="nodeSoftwareList" class="software-list"></div>
            <div class="topo-field">
                <label for="softwareName">Nombre software</label>
                <input id="softwareName" type="text" maxlength="150" placeholder="Ej. ERP ITCity">
            </div>
            <div class="topo-field">
                <label for="softwareVersion">Versión</label>
                <input id="softwareVersion" type="text" maxlength="80" placeholder="4.2.1">
            </div>
            <div class="topo-field">
                <label for="softwareVendor">Vendor</label>
                <input id="softwareVendor" type="text" maxlength="120" placeholder="ITCity Labs">
            </div>
            <div class="topo-field">
                <label for="softwareProject">Proyecto</label>
                <input id="softwareProject" type="text" maxlength="150" placeholder="Proyecto Core">
            </div>
            <div class="topo-field">
                <label for="softwareContactEmail">Email contacto</label>
                <input id="softwareContactEmail" type="email" maxlength="150" placeholder="owner@itcity.local">
            </div>
            <div class="topo-field">
                <label for="softwareContactPhone">Teléfono contacto</label>
                <input id="softwareContactPhone" type="text" maxlength="60" placeholder="+52 ...">
            </div>
            <div class="topo-field">
                <label for="softwareDetails">Detalles (JSON opcional)</label>
                <textarea id="softwareDetails" maxlength="2000" placeholder='Ej. {"stack":"Laravel"}'></textarea>
            </div>
            <div class="topo-modal-footer" style="justify-content:flex-start; margin-bottom:10px;">
                <button id="softwareSaveBtn" class="topo-btn primary">Guardar software</button>
                <button id="softwareResetBtn" class="topo-btn">Limpiar</button>
            </div>
        </div>

        <div class="topo-modal-footer">
            <button id="nodeModalCancel" class="topo-btn">Cancelar</button>
            <button id="nodeModalSave" class="topo-btn primary">Guardar</button>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const nodes          = @json($graphNodes);
    const edges          = @json($graphEdges);
    const zones          = @json($branchZones);
    const branchOptions  = @json($branchOptions);
    const nodeTypeOptions = @json($nodeTypeOptions);
    const csrfToken      = @json(csrf_token());
    const createRelUrl   = @json($createRelationUrl);
    const createNodeUrl  = @json($createNodeUrl);
    const createSoftwareUrl = @json($createSoftwareUrl);
    const saveLayoutUrl  = @json($saveLayoutUrl);
    const viewerProfileStorageKey = 'itcity.topology.viewer.v1';
    const personalLayoutStoragePrefix = 'itcity.topology.layout.v1';

    // ── SVG refs ───────────────────────────────────────────────────────
    const svg         = document.getElementById('topoCanvas');
    const viewport    = document.getElementById('viewport');
    const zonesLayer  = document.getElementById('zonesLayer');
    const edgesLayer  = document.getElementById('edgesLayer');
    const nodesLayer  = document.getElementById('nodesLayer');
    const tempLine    = document.getElementById('tempLine');
    const bgRect      = document.getElementById('bgRect');
    const saveLayoutBtn = document.getElementById('btnSaveLayout');

    let W = 1800, H = 1100;
    svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
    bgRect.setAttribute('width', W);
    bgRect.setAttribute('height', H);

    // ── State ──────────────────────────────────────────────────────────
    const nodeMap    = new Map();  // id → {node data + x, y}
    const edgeMap    = new Map();  // id → edge data
    const zoneMap    = new Map();  // branchId → zone geometry
    const nodeElMap  = new Map();  // nodeId → {group, shape, statusDot}
    const edgeElMap  = new Map();  // edgeId → {group, line, labelBg, labelEl, hit}

    let panX = 0, panY = 0, zoom = 1;
    let isPanning = false, panSX = 0, panSY = 0;
    let dragging = null, dragMoved = false, dragOX = 0, dragOY = 0;
    let resizingZoneId = null, resizeZoneDirection = null;
    let resizeZoneStartX = 0, resizeZoneStartY = 0, resizeZoneStartW = 0, resizeZoneStartH = 0;
    let resizeZoneStartLeft = 0, resizeZoneStartTop = 0;
    let draggingZoneId = null, draggingZoneMoved = false, dragZoneStartX = 0, dragZoneStartY = 0, dragZoneBaseX = 0, dragZoneBaseY = 0;
    let suppressZoneClick = false;
    const zoneDragNodeStart = new Map();
    let connectMode = false, connectSrc = null;
    let selNode = null, selEdge = null, selBranch = null;
    let dirty = false;
    let personalLayoutPersistTimer = null;
    let activeLayer = 'all';
    let activeTrace = null;

    const safeJsonParse = (value, fallback = {}) => {
        try {
            return value ? JSON.parse(value) : fallback;
        } catch {
            return fallback;
        }
    };

    const ensureViewerProfileId = () => {
        const current = window.localStorage.getItem(viewerProfileStorageKey);
        if (current) return current;

        const generated = window.crypto?.randomUUID?.() || `viewer-${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
        window.localStorage.setItem(viewerProfileStorageKey, generated);
        return generated;
    };

    const viewerProfileId = ensureViewerProfileId();
    const personalLayoutStorageKey = `${personalLayoutStoragePrefix}:${window.location.host}:${viewerProfileId}`;
    const personalLayoutSnapshot = safeJsonParse(window.localStorage.getItem(personalLayoutStorageKey), {});
    const personalLayoutNodes = personalLayoutSnapshot?.nodes && typeof personalLayoutSnapshot.nodes === 'object'
        ? personalLayoutSnapshot.nodes
        : {};
    const personalLayoutZones = personalLayoutSnapshot?.zones && typeof personalLayoutSnapshot.zones === 'object'
        ? personalLayoutSnapshot.zones
        : {};

    const persistPersonalLayout = () => {
        const nodesSnapshot = {};
        const zonesSnapshot = {};
        nodeMap.forEach((node) => {
            nodesSnapshot[node.id] = {
                x: Math.round((Number(node.x) || 0) * 100) / 100,
                y: Math.round((Number(node.y) || 0) * 100) / 100,
            };
        });

        zoneMap.forEach((zone) => {
            zonesSnapshot[zone.id] = {
                x: Math.round((Number(zone.x) || 0) * 100) / 100,
                y: Math.round((Number(zone.y) || 0) * 100) / 100,
                w: Math.round((Number(zone.w) || 0) * 100) / 100,
                h: Math.round((Number(zone.h) || 0) * 100) / 100,
            };
        });

        window.localStorage.setItem(personalLayoutStorageKey, JSON.stringify({
            viewer_id: viewerProfileId,
            host: window.location.host,
            updated_at: new Date().toISOString(),
            nodes: nodesSnapshot,
            zones: zonesSnapshot,
        }));
    };

    const schedulePersistPersonalLayout = (immediate = false) => {
        if (personalLayoutPersistTimer) {
            window.clearTimeout(personalLayoutPersistTimer);
            personalLayoutPersistTimer = null;
        }

        if (immediate) {
            persistPersonalLayout();
            return;
        }

        personalLayoutPersistTimer = window.setTimeout(() => {
            persistPersonalLayout();
            personalLayoutPersistTimer = null;
        }, 220);
    };

    // ── SVG helpers ────────────────────────────────────────────────────
    const mk = (tag, attrs = {}) => {
        const el = document.createElementNS('http://www.w3.org/2000/svg', tag);
        for (const [k, v] of Object.entries(attrs)) el.setAttribute(k, v);
        return el;
    };

    const svgPt = (e) => {
        const pt = svg.createSVGPoint();
        pt.x = e.clientX; pt.y = e.clientY;
        const ctm = nodesLayer.getScreenCTM();
        return ctm ? pt.matrixTransform(ctm.inverse()) : { x: 0, y: 0 };
    };

    const applyVP = () => {
        viewport.setAttribute('transform', `translate(${panX},${panY}) scale(${zoom})`);
    };

    const clamp = (v, lo, hi) => Math.max(lo, Math.min(hi, v));
    const esc = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const nodeSnapshotCache = new Map();
    const pendingNodeSnapshotLoads = new Set();

    // ── Zone layout ────────────────────────────────────────────────────
    const ZONE_BASE_W = 520;
    const ZONE_BASE_H = 380;
    const ZONE_GAP_X = 90;
    const ZONE_GAP_Y = 80;
    const ZONE_MARGIN = 56;
    const ZONE_TITLE_OFFSET = 30;
    const MIN_CELL_W = 96;
    const MIN_CELL_H = 84;
    const MIN_ZONE_W = 280;
    const MIN_ZONE_H = 220;

    const branchNodeCounts = nodes.reduce((acc, node) => {
        acc[node.branch_id] = (acc[node.branch_id] ?? 0) + 1;
        return acc;
    }, {});

    const calcZoneSize = (nodeCount) => {
        const count = Math.max(1, Number(nodeCount || 1));
        const cols = Math.max(2, Math.ceil(Math.sqrt(count)));
        const rows = Math.max(1, Math.ceil(count / cols));

        const reqW = (ZONE_MARGIN * 2) + (cols * MIN_CELL_W);
        const reqH = (ZONE_MARGIN * 2) + ZONE_TITLE_OFFSET + (rows * MIN_CELL_H);

        return {
            w: Math.max(ZONE_BASE_W, reqW),
            h: Math.max(ZONE_BASE_H, reqH),
        };
    };

    const zoneCount = zones.length;
    const ZONE_COLS = zoneCount <= 2 ? zoneCount : (zoneCount <= 4 ? 2 : Math.ceil(Math.sqrt(zoneCount)));

    const ZONE_PALETTES = [
        { bg: '#0c1a38', border: '#1e40af', text: '#93c5fd' },
        { bg: '#052e16', border: '#166534', text: '#86efac' },
        { bg: '#2d1b00', border: '#92400e', text: '#fcd34d' },
        { bg: '#1e0938', border: '#6b21a8', text: '#d8b4fe' },
        { bg: '#0f1f2e', border: '#0369a1', text: '#7dd3fc' },
        { bg: '#1a0a00', border: '#c2410c', text: '#fdba74' },
    ];

    const rowHeights = [];

    zones.forEach((zone, idx) => {
        const col = idx % ZONE_COLS;
        const row = Math.floor(idx / ZONE_COLS);
        const baseSize = calcZoneSize(branchNodeCounts[zone.id] ?? 1);
        const zoneOverride = personalLayoutZones[zone.id] || personalLayoutZones[String(zone.id)] || null;
        const size = {
            w: zoneOverride?.w != null ? Math.max(MIN_ZONE_W, Number(zoneOverride.w) || baseSize.w) : baseSize.w,
            h: zoneOverride?.h != null ? Math.max(MIN_ZONE_H, Number(zoneOverride.h) || baseSize.h) : baseSize.h,
        };

        if (!rowHeights[row] || rowHeights[row] < size.h) {
            rowHeights[row] = size.h;
        }

        const prevRowsHeight = rowHeights.slice(0, row).reduce((sum, h) => sum + h, 0);
        const rowY = 60 + prevRowsHeight + (row * ZONE_GAP_Y);

        const prevCols = zones
            .slice(row * ZONE_COLS, row * ZONE_COLS + col)
            .map((z) => {
                const prevBaseSize = calcZoneSize(branchNodeCounts[z.id] ?? 1);
                const prevOverride = personalLayoutZones[z.id] || personalLayoutZones[String(z.id)] || null;
                return prevOverride?.w != null ? Math.max(MIN_ZONE_W, Number(prevOverride.w) || prevBaseSize.w) : prevBaseSize.w;
            })
            .reduce((sum, w) => sum + w, 0);
        const colX = 60 + prevCols + (col * ZONE_GAP_X);
        const x = (zoneOverride?.x != null && Number.isFinite(+zoneOverride.x))
            ? +zoneOverride.x
            : colX;
        const y = (zoneOverride?.y != null && Number.isFinite(+zoneOverride.y))
            ? +zoneOverride.y
            : rowY;

        const palette = ZONE_PALETTES[idx % ZONE_PALETTES.length];
        zoneMap.set(zone.id, {
            id: zone.id,
            name: zone.name,
            city: zone.city,
            state: zone.state,
            x,
            y,
            w: size.w,
            h: size.h,
            palette,
        });
    });

    const rightMost = [...zoneMap.values()].reduce((max, z) => Math.max(max, z.x + z.w), 0);
    const bottomMost = [...zoneMap.values()].reduce((max, z) => Math.max(max, z.y + z.h), 0);
    W = Math.max(1800, rightMost + 120);
    H = Math.max(1100, bottomMost + 120);
    svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
    bgRect.setAttribute('width', W);
    bgRect.setAttribute('height', H);

    // ── Node initial positions ─────────────────────────────────────────
    const branchNodeBuckets = nodes.reduce((acc, node) => {
        if (!acc[node.branch_id]) acc[node.branch_id] = [];
        acc[node.branch_id].push(node);
        return acc;
    }, {});

    const branchIndices = {};
    nodes.forEach(node => {
        const zone = zoneMap.get(node.branch_id);
        branchIndices[node.branch_id] = (branchIndices[node.branch_id] ?? -1) + 1;
        const idx = branchIndices[node.branch_id];
        const branchNodes = branchNodeBuckets[node.branch_id] ?? [];
        const bNodes = branchNodes.length;
        const margin = ZONE_MARGIN;
        const usableW = Math.max(120, (zone?.w ?? ZONE_BASE_W) - margin * 2);
        const usableH = Math.max(120, (zone?.h ?? ZONE_BASE_H) - margin * 2 - ZONE_TITLE_OFFSET);
        const cols = Math.max(1, Math.ceil(Math.sqrt(bNodes)));
        const cellW = usableW / cols;
        const cellH = usableH / Math.max(1, Math.ceil(bNodes / cols));
        const c = idx % cols, r = Math.floor(idx / cols);
        const defaultX = zone ? zone.x + margin + c * cellW + cellW / 2 : 100 + idx * 120;
        const defaultY = zone ? zone.y + ZONE_TITLE_OFFSET + margin + r * cellH + cellH / 2 : 100;
        const personalCoords = personalLayoutNodes[node.id] || personalLayoutNodes[String(node.id)] || null;
        const x = (personalCoords?.x != null && Number.isFinite(+personalCoords.x))
            ? +personalCoords.x
            : ((node.layout_x != null && Number.isFinite(+node.layout_x)) ? +node.layout_x : defaultX);
        const y = (personalCoords?.y != null && Number.isFinite(+personalCoords.y))
            ? +personalCoords.y
            : ((node.layout_y != null && Number.isFinite(+node.layout_y)) ? +node.layout_y : defaultY);
        nodeMap.set(node.id, { ...node, x, y });
    });

    edges.forEach(e => edgeMap.set(e.id, e));

    // ── Device colors / shapes ─────────────────────────────────────────
    const TYPE_COLOR = (slug) => {
        const s = (slug || '').toLowerCase();
        if (s.includes('router'))              return { fill: '#b45309', stroke: '#78350f' };
        if (s.includes('switch'))              return { fill: '#6d28d9', stroke: '#4c1d95' };
        if (s.includes('firewall'))            return { fill: '#b91c1c', stroke: '#7f1d1d' };
        if (s.includes('access') || s.includes('ap')) return { fill: '#4338ca', stroke: '#312e81' };
        if (s.includes('vpn'))                 return { fill: '#7c3aed', stroke: '#4c1d95' };
        if (s.includes('database') || s.includes('db') || s.includes('sql'))
                                              return { fill: '#0f766e', stroke: '#115e59' };
        if (s.includes('load-balancer') || s.includes('balancer'))
                                              return { fill: '#0284c7', stroke: '#075985' };
        if (s.includes('pbx') || s.includes('telefon'))
                                              return { fill: '#047857', stroke: '#065f46' };
        if (s.includes('camera'))             return { fill: '#475569', stroke: '#1e293b' };
        if (s.includes('printer') || s.includes('print'))
                                              return { fill: '#334155', stroke: '#0f172a' };
        if (s.includes('server') || s.includes('serv') || s.includes('storage') || s.includes('nas'))
                                               return { fill: '#0e7490', stroke: '#164e63' };
        return { fill: '#1d4ed8', stroke: '#1e3a8a' };
    };

    const STATUS_COLOR = (status) => {
        switch ((status || '').toLowerCase()) {
            case 'active':   return '#22c55e';
            case 'warning':  return '#f59e0b';
            case 'error':    return '#ef4444';
            case 'inactive': return '#475569';
            default:         return '#64748b';
        }
    };

    const buildShape = (slug, fill, stroke) => {
        const s = (slug || '').toLowerCase();
        const sz = 22;
        if (s.includes('router')) {
            const pts = Array.from({ length: 6 }, (_, i) => {
                const a = (i / 6) * Math.PI * 2 - Math.PI / 6;
                return `${(Math.cos(a) * sz).toFixed(2)},${(Math.sin(a) * sz).toFixed(2)}`;
            }).join(' ');
            return mk('polygon', { points: pts, fill, stroke, 'stroke-width': '2.5' });
        }
        if (s.includes('switch'))
            return mk('rect', { x: -30, y: -15, width: 60, height: 30, rx: '5', fill, stroke, 'stroke-width': '2.5' });
        if (s.includes('firewall')) {
            const d = `M0,${-sz} L${sz*.95},${-sz*.55} L${sz*.95},${sz*.1} Q${sz*.95},${sz*.85} 0,${sz} Q${-sz*.95},${sz*.85} ${-sz*.95},${sz*.1} L${-sz*.95},${-sz*.55} Z`;
            return mk('path', { d, fill, stroke, 'stroke-width': '2.5' });
        }
        if (s.includes('access') || s.includes('ap'))
            return mk('circle', { cx: 0, cy: 0, r: sz.toString(), fill, stroke, 'stroke-width': '2.5' });
        if (s.includes('vpn'))
            return mk('polygon', { points: `0,${-sz} ${sz},0 0,${sz} ${-sz},0`, fill, stroke, 'stroke-width': '2.5' });
        if (s.includes('database') || s.includes('db') || s.includes('sql')) {
            const d = [
                `M ${-sz},${-10}`,
                `C ${-sz},${-18} ${sz},${-18} ${sz},${-10}`,
                `L ${sz},${10}`,
                `C ${sz},${18} ${-sz},${18} ${-sz},${10}`,
                'Z',
            ].join(' ');
            return mk('path', { d, fill, stroke, 'stroke-width': '2.5' });
        }
        if (s.includes('load-balancer') || s.includes('balancer'))
            return mk('rect', { x: -28, y: -18, width: 56, height: 36, rx: '12', fill, stroke, 'stroke-width': '2.5' });
        if (s.includes('pbx') || s.includes('telefon'))
            return mk('rect', { x: -24, y: -24, width: 48, height: 48, rx: '16', fill, stroke, 'stroke-width': '2.5' });
        if (s.includes('camera'))
            return mk('circle', { cx: 0, cy: 0, r: '20', fill, stroke, 'stroke-width': '2.5' });
        if (s.includes('printer') || s.includes('print'))
            return mk('rect', { x: -24, y: -18, width: 48, height: 36, rx: '8', fill, stroke, 'stroke-width': '2.5' });
        if (s.includes('server') || s.includes('serv') || s.includes('storage') || s.includes('nas'))
            return mk('rect', { x: -sz, y: -sz, width: sz*2, height: sz*2, rx: '4', fill, stroke, 'stroke-width': '2.5' });
        return mk('circle', { cx: 0, cy: 0, r: sz.toString(), fill, stroke, 'stroke-width': '2.5' });
    };

    const NODE_CHAR = (slug) => {
        const s = (slug || '').toLowerCase();
        if (s.includes('router')) return 'R';
        if (s.includes('switch')) return 'SW';
        if (s.includes('firewall')) return 'FW';
        if (s.includes('access') || s.includes('ap')) return 'AP';
        if (s.includes('vpn')) return 'VPN';
        if (s.includes('database') || s.includes('db') || s.includes('sql')) return 'DB';
        if (s.includes('load-balancer') || s.includes('balancer')) return 'LB';
        if (s.includes('pbx') || s.includes('telefon')) return 'PBX';
        if (s.includes('camera')) return 'CAM';
        if (s.includes('printer') || s.includes('print')) return 'PRN';
        if (s.includes('server') || s.includes('serv')) return 'SV';
        if (s.includes('storage') || s.includes('nas')) return 'ST';
        return 'N';
    };

    // ── Edge style ─────────────────────────────────────────────────────
    const EDGE_STYLE = (relType) => {
        const t = (relType || '').toLowerCase();
        if (t.includes('vpn'))    return { stroke: '#9333ea', dash: '9 5', w: 2.5, m: 'url(#arr-vpn)', anim: false };
        if (t.includes('wan'))    return { stroke: '#f97316', dash: '11 5', w: 2, m: 'url(#arr-wan)', anim: false };
        if (t.includes('fiber'))  return { stroke: '#2563eb', dash: null, w: 2.5, m: 'url(#arr-fiber)', anim: true };
        if (t.includes('wire') || t.includes('wifi') || t.includes('inalambr'))
                                  return { stroke: '#0891b2', dash: '4 4', w: 2, m: 'url(#arr-teal)', anim: false };
        if (t.includes('inter') || t.includes('campus'))
                                  return { stroke: '#16a34a', dash: '13 5', w: 3, m: 'url(#arr-green)', anim: false };
        return { stroke: '#475569', dash: null, w: 2, m: 'url(#arr)', anim: true };
    };

    const EDGE_DISPLAY_LABEL = (edge) => {
        let label = edge?.label || 'linked_to';
        const hasInterCampusText = label.toLowerCase().includes('campus');
        if (edge?.is_inter_campus && !hasInterCampusText) {
            label += ' · inter-campus';
        }
        return label;
    };

    const isAccessPointNode = (node) => {
        const key = `${node?.type_slug || ''} ${node?.type || ''}`.toLowerCase();
        return key.includes('access') || key.includes('ap') || key.includes('wifi') || key.includes('wireless');
    };

    const isServerNode = (node) => {
        const key = `${node?.type_slug || ''} ${node?.type || ''}`.toLowerCase();
        return key.includes('server') || key.includes('serv') || key.includes('database') || key.includes('db') || key.includes('sql') || key.includes('storage') || key.includes('nas');
    };

    const isCriticalEdge = (edge) => {
        const label = `${edge?.label || ''}`.toLowerCase();
        return !!edge?.is_inter_campus || label.includes('wan') || label.includes('vpn') || label.includes('fiber') || label.includes('inter-campus');
    };

    const hasSoftwareSystems = (node) => Number(node?.software_count || 0) > 0;

    const getLayerEdgeOpacity = (edge) => {
        if (activeLayer === 'all') return 1;
        if (activeLayer === 'critical_links') return isCriticalEdge(edge) ? 1 : 0.12;
        if (activeLayer === 'ap') {
            const fromNode = nodeMap.get(edge.from);
            const toNode = nodeMap.get(edge.to);
            return (isAccessPointNode(fromNode) || isAccessPointNode(toNode)) ? 0.95 : 0.12;
        }
        if (activeLayer === 'software') {
            const fromNode = nodeMap.get(edge.from);
            const toNode = nodeMap.get(edge.to);
            return (hasSoftwareSystems(fromNode) || hasSoftwareSystems(toNode)) ? 0.95 : 0.12;
        }
        if (activeLayer === 'servers') {
            const fromNode = nodeMap.get(edge.from);
            const toNode = nodeMap.get(edge.to);
            return (isServerNode(fromNode) || isServerNode(toNode)) ? 0.95 : 0.12;
        }
        return 1;
    };

    const getLayerNodeOpacity = (node) => {
        if (activeLayer === 'all') return 1;
        if (activeLayer === 'ap') return isAccessPointNode(node) ? 1 : 0.18;
        if (activeLayer === 'software') return hasSoftwareSystems(node) ? 1 : 0.18;
        if (activeLayer === 'servers') return isServerNode(node) ? 1 : 0.18;
        if (activeLayer === 'critical_links') {
            const hasCriticalEdge = [...edgeMap.values()].some((edge) => {
                if (!isCriticalEdge(edge)) return false;
                return Number(edge.from) === Number(node.id) || Number(edge.to) === Number(node.id);
            });
            return hasCriticalEdge ? 1 : 0.2;
        }
        return 1;
    };

    const getTraceNodeOpacity = (nodeId) => {
        if (!activeTrace) return 1;
        return activeTrace.nodeIds.has(Number(nodeId)) ? 1 : 0.12;
    };

    const getTraceEdgeOpacity = (edgeId) => {
        if (!activeTrace) return 1;
        return activeTrace.edgeIds.has(Number(edgeId)) ? 1 : 0.08;
    };

    // ── Render zones ───────────────────────────────────────────────────
    const renderZones = () => {
        zonesLayer.innerHTML = '';
        zoneMap.forEach(z => {
            const g = mk('g', { style: 'cursor:pointer', 'data-branch-id': String(z.id) });
            const rect = mk('rect', { x: z.x, y: z.y, width: z.w, height: z.h, rx: '14',
                fill: z.palette.bg, stroke: z.palette.border, 'stroke-width': '1.5' });
            g.appendChild(rect);
            const lbl = mk('text', { x: z.x + 18, y: z.y + 24, 'font-size': '13',
                'font-weight': '700', fill: z.palette.text, 'pointer-events': 'none' });
            lbl.textContent = z.name;
            g.appendChild(lbl);

            const assetsLbl = mk('text', {
                x: z.x + 18,
                y: z.y + 40,
                'font-size': '10',
                fill: '#94a3b8',
                'pointer-events': 'none',
            });
            assetsLbl.textContent = `Activos conectados: ${Number(z.connected_assets_count || 0)}`;
            g.appendChild(assetsLbl);

            const resizeHandles = [
                { dir: 'nw', x: z.x - 5, y: z.y - 5, w: 10, h: 10, cursor: 'nwse-resize' },
                { dir: 'n',  x: z.x + (z.w / 2) - 12, y: z.y - 4, w: 24, h: 8, cursor: 'ns-resize' },
                { dir: 'ne', x: z.x + z.w - 5, y: z.y - 5, w: 10, h: 10, cursor: 'nesw-resize' },
                { dir: 'e',  x: z.x + z.w - 4, y: z.y + (z.h / 2) - 12, w: 8, h: 24, cursor: 'ew-resize' },
                { dir: 'se', x: z.x + z.w - 5, y: z.y + z.h - 5, w: 10, h: 10, cursor: 'nwse-resize' },
                { dir: 's',  x: z.x + (z.w / 2) - 12, y: z.y + z.h - 4, w: 24, h: 8, cursor: 'ns-resize' },
                { dir: 'sw', x: z.x - 5, y: z.y + z.h - 5, w: 10, h: 10, cursor: 'nesw-resize' },
                { dir: 'w',  x: z.x - 4, y: z.y + (z.h / 2) - 12, w: 8, h: 24, cursor: 'ew-resize' },
            ];

            resizeHandles.forEach((handleDef) => {
                const resizeHandle = mk('rect', {
                    x: handleDef.x,
                    y: handleDef.y,
                    width: handleDef.w,
                    height: handleDef.h,
                    rx: 2,
                    fill: z.palette.text,
                    opacity: '.72',
                    style: `cursor:${handleDef.cursor}`,
                    'data-zone-resize-handle': String(z.id),
                    'data-zone-resize-dir': handleDef.dir,
                });

                resizeHandle.addEventListener('mousedown', (e) => {
                    e.stopPropagation();
                    const pt = svgPt(e);
                    resizingZoneId = z.id;
                    resizeZoneDirection = handleDef.dir;
                    resizeZoneStartX = pt.x;
                    resizeZoneStartY = pt.y;
                    resizeZoneStartW = z.w;
                    resizeZoneStartH = z.h;
                    resizeZoneStartLeft = z.x;
                    resizeZoneStartTop = z.y;
                });

                g.appendChild(resizeHandle);
            });

            g.addEventListener('mousedown', (e) => {
                if (e.target.closest('[data-zone-resize-handle]')) return;
                e.stopPropagation();

                const pt = svgPt(e);
                draggingZoneId = z.id;
                draggingZoneMoved = false;
                dragZoneStartX = pt.x;
                dragZoneStartY = pt.y;
                dragZoneBaseX = z.x;
                dragZoneBaseY = z.y;
                zoneDragNodeStart.clear();

                nodesForBranch(z.id).forEach((node) => {
                    zoneDragNodeStart.set(node.id, { x: node.x, y: node.y });
                });
            });

            g.addEventListener('click', (e) => {
                if (suppressZoneClick) {
                    suppressZoneClick = false;
                    return;
                }
                e.stopPropagation();
                selBranch = z.id;
                selNode = null;
                selEdge = null;
                applyHighlight();
                renderInspBranch(z.id);
                renderDrilldown(z.id);
            });

            zonesLayer.appendChild(g);
        });
    };

    // ── Render edge ────────────────────────────────────────────────────
    const buildEdge = (edge) => {
        const fn = nodeMap.get(edge.from);
        const tn = nodeMap.get(edge.to);
        if (!fn || !tn) return;

        const existing = edgeElMap.get(edge.id);
        if (existing) { existing.group.remove(); edgeElMap.delete(edge.id); }

        const st = EDGE_STYLE(edge.label);
        const sx = fn.x, sy = fn.y, tx = tn.x, ty = tn.y;

        const group = mk('g', { style: 'cursor:pointer', 'data-edge-id': String(edge.id) });
        const hit = mk('line', { x1: sx, y1: sy, x2: tx, y2: ty, stroke: 'transparent', 'stroke-width': '14' });
        const line = mk('line', { x1: sx, y1: sy, x2: tx, y2: ty, stroke: st.stroke,
            'stroke-width': st.w, 'stroke-linecap': 'round', 'marker-end': st.m });
        if (st.dash) line.setAttribute('stroke-dasharray', st.dash);
        if (st.anim) line.classList.add('edge-flow');

        const midX = (sx + tx) / 2, midY = (sy + ty) / 2;
        const lbg = mk('rect', { x: midX - 30, y: midY - 10, width: 60, height: 18, rx: '6',
            fill: '#0f172a', stroke: st.stroke, 'stroke-width': '1', opacity: '.9' });
        const ltx = mk('text', { x: midX, y: midY + 5, 'text-anchor': 'middle', 'font-size': '9',
            fill: st.stroke, 'font-weight': '700', 'pointer-events': 'none' });
        ltx.textContent = EDGE_DISPLAY_LABEL(edge).slice(0, 22);

        group.append(hit, line, lbg, ltx);

        group.addEventListener('click', (e) => {
            if (connectMode) return;
            e.stopPropagation();
            selEdge = edge.id; selNode = null;
            selBranch = fn?.branch_id || tn?.branch_id || null;
            applyHighlight();
            renderInspEdge(edge);
            if (selBranch) renderDrilldown(selBranch);
            if (e.ctrlKey || e.metaKey) return;
            window._editEdge(edge.id);
        });

        edgesLayer.insertBefore(group, tempLine); // keep tempLine on top
        edgeElMap.set(edge.id, { group, line, hit, lbg, ltx });
    };

    const updateEdgePos = (edgeId) => {
        const el = edgeElMap.get(edgeId);
        const edge = edgeMap.get(edgeId);
        if (!el || !edge) return;
        const fn = nodeMap.get(edge.from), tn = nodeMap.get(edge.to);
        if (!fn || !tn) return;
        const sx = fn.x, sy = fn.y, tx = tn.x, ty = tn.y;
        const midX = (sx + tx) / 2, midY = (sy + ty) / 2;
        for (const attr of ['x1','y1','x2','y2'])
            el.line.setAttribute(attr, attr === 'x1' ? sx : attr === 'y1' ? sy : attr === 'x2' ? tx : ty);
        for (const attr of ['x1','y1','x2','y2'])
            el.hit.setAttribute(attr, attr === 'x1' ? sx : attr === 'y1' ? sy : attr === 'x2' ? tx : ty);
        el.lbg.setAttribute('x', midX - 30); el.lbg.setAttribute('y', midY - 10);
        el.ltx.setAttribute('x', midX);      el.ltx.setAttribute('y', midY + 5);
    };

    const rebuildEdgesForNode = (nodeId) => {
        edgeMap.forEach(e => { if (e.from === nodeId || e.to === nodeId) updateEdgePos(e.id); });
    };

    // ── Render node ────────────────────────────────────────────────────
    const buildNode = (nd) => {
        const existing = nodeElMap.get(nd.id);
        if (existing) { existing.group.remove(); nodeElMap.delete(nd.id); }

        const tc = TYPE_COLOR(nd.type_slug || nd.type);
        const group = mk('g', {
            style: 'cursor:pointer',
            'data-node-id': String(nd.id),
            transform: `translate(${nd.x},${nd.y})`,
        });

        const shape = buildShape(nd.type_slug || nd.type, tc.fill, tc.stroke);
        group.appendChild(shape);

        // AP rings
        const ts = (nd.type_slug || nd.type || '').toLowerCase();
        if (ts.includes('access') || ts.includes('ap')) {
            group.appendChild(mk('circle', { cx: 0, cy: 0, r: 32, fill: 'none', stroke: tc.fill, 'stroke-width': '1.5', opacity: '.35', 'pointer-events': 'none' }));
            group.appendChild(mk('circle', { cx: 0, cy: 0, r: 42, fill: 'none', stroke: tc.fill, 'stroke-width': '1', opacity: '.18', 'pointer-events': 'none' }));
        }

        // Switch port dots
        if (ts.includes('switch')) {
            [-14, -5, 4, 13].forEach(dx => {
                group.appendChild(mk('circle', { cx: dx, cy: 0, r: 3, fill: '#e2e8f0', opacity: '.5', 'pointer-events': 'none' }));
            });
        }

        // Server rack lines
        if (ts.includes('server')) {
            [-8, -2, 4].forEach(dy => {
                group.appendChild(mk('line', { x1: -14, y1: dy, x2: 10, y2: dy, stroke: '#e2e8f0', 'stroke-width': '1.5', opacity: '.4', 'pointer-events': 'none' }));
                group.appendChild(mk('circle', { cx: 14, cy: dy, r: 2.5, fill: '#4ade80', opacity: '.6', 'pointer-events': 'none' }));
            });
        }

        if (ts.includes('database') || ts.includes('db') || ts.includes('sql')) {
            [-10, 0, 10].forEach(dy => {
                group.appendChild(mk('ellipse', { cx: 0, cy: dy, rx: 18, ry: 5, fill: 'none', stroke: '#ccfbf1', 'stroke-width': '1.4', opacity: '.45', 'pointer-events': 'none' }));
            });
        }

        if (ts.includes('load-balancer') || ts.includes('balancer')) {
            const arrows = mk('text', { x: 0, y: 4, 'text-anchor': 'middle', 'font-size': '16', fill: '#e0f2fe', opacity: '.28', 'pointer-events': 'none' });
            arrows.textContent = '⇄';
            group.appendChild(arrows);
        }

        if (ts.includes('pbx') || ts.includes('telefon')) {
            [-7, 0, 7].forEach(dx => {
                [-7, 0, 7].forEach(dy => {
                    group.appendChild(mk('circle', { cx: dx, cy: dy, r: 1.8, fill: '#d1fae5', opacity: '.45', 'pointer-events': 'none' }));
                });
            });
        }

        if (ts.includes('camera')) {
            group.appendChild(mk('circle', { cx: 0, cy: 0, r: 7, fill: '#cbd5e1', opacity: '.25', 'pointer-events': 'none' }));
            group.appendChild(mk('line', { x1: -10, y1: 16, x2: 10, y2: 16, stroke: '#cbd5e1', 'stroke-width': '1.8', opacity: '.35', 'pointer-events': 'none' }));
        }

        if (ts.includes('printer') || ts.includes('print')) {
            group.appendChild(mk('rect', { x: -12, y: -24, width: 24, height: 12, rx: '2', fill: '#e2e8f0', opacity: '.24', 'pointer-events': 'none' }));
        }

        // VPN lock body
        if (ts.includes('vpn')) {
            group.appendChild(mk('rect', { x: -9, y: 0, width: 18, height: 13, rx: '3', fill: '#e9d5ff', opacity: '.25', 'pointer-events': 'none' }));
        }

        // Firewall X mark
        if (ts.includes('firewall')) {
            const xMk = mk('text', { x: 0, y: 2, 'text-anchor': 'middle', 'font-size': '11', fill: '#fca5a5', 'font-weight': '900', 'pointer-events': 'none' });
            xMk.textContent = '✕';
            group.appendChild(xMk);
        }

        // Icon char
        const iconTxt = mk('text', { x: 0, y: ts.includes('firewall') ? 0 : 4,
            'text-anchor': 'middle', 'font-size': '10', 'font-weight': '800',
            fill: '#fff', 'pointer-events': 'none', 'user-select': 'none' });
        iconTxt.textContent = NODE_CHAR(nd.type_slug || nd.type);
        if (!ts.includes('firewall')) group.appendChild(iconTxt);

        // Status dot
        const sdot = mk('circle', { cx: 20, cy: -18, r: 5, fill: STATUS_COLOR(nd.status), stroke: '#0f172a', 'stroke-width': '2' });
        group.appendChild(sdot);

        // Connected assets badge
        const connectedAssetsCount = Number(nd.connected_assets_count || 0);
        if (connectedAssetsCount > 0) {
            const badge = mk('circle', { cx: 23, cy: 22, r: 11, fill: '#0f172a', stroke: '#38bdf8', 'stroke-width': '2' });
            const badgeTxt = mk('text', {
                x: 23,
                y: 26,
                'text-anchor': 'middle',
                'font-size': '9',
                'font-weight': '800',
                fill: '#38bdf8',
                'pointer-events': 'none',
            });
            badgeTxt.textContent = connectedAssetsCount > 99 ? '99+' : String(connectedAssetsCount);
            group.appendChild(badge);
            group.appendChild(badgeTxt);
        }

        // Name label
        const nlbl = mk('text', { x: 0, y: 42, 'text-anchor': 'middle', 'font-size': '11',
            'font-weight': '600', fill: '#e2e8f0', 'pointer-events': 'none' });
        nlbl.textContent = (nd.label || '').slice(0, 17);
        group.appendChild(nlbl);

        if (nd.ip) {
            const iplbl = mk('text', { x: 0, y: 55, 'text-anchor': 'middle', 'font-size': '9',
                fill: '#64748b', 'pointer-events': 'none' });
            iplbl.textContent = nd.ip;
            group.appendChild(iplbl);
        }

        const ttl = mk('title', {});
        ttl.textContent = `${nd.label}\n${nd.type}\nIP: ${nd.ip || 'N/A'}\nEstado: ${nd.status}\nActivos conectados: ${connectedAssetsCount}`;
        group.appendChild(ttl);

        // ── Events ──────────────────────────────────────────────────
        group.addEventListener('mousedown', (e) => {
            if (connectMode) return;
            const pt = svgPt(e);
            dragging = nd.id; dragMoved = false;
            dragOX = pt.x - nd.x; dragOY = pt.y - nd.y;
            e.stopPropagation();
        });

        group.addEventListener('click', (e) => {
            if (dragMoved) return;
            e.stopPropagation();
            if (connectMode) {
                if (!connectSrc) {
                    connectSrc = nd.id;
                    shape.setAttribute('stroke', '#facc15');
                    shape.setAttribute('stroke-width', '4');
                    setStatus('Ahora haz clic en el nodo destino...', '');
                } else if (connectSrc !== nd.id) {
                    const srcId = connectSrc;
                    connectSrc = null;
                    clearConnectHL();
                    openModal({ mode: 'create', fromId: srcId, toId: nd.id });
                }
                return;
            }
            selNode = nd.id; selEdge = null; selBranch = nd.branch_id;
            applyHighlight();
            renderInspNode(nd);
            renderDrilldown(nd.branch_id, nd.id);
            if (e.ctrlKey || e.metaKey) return;
            window._editNode(nd.id);
        });

        group.addEventListener('dblclick', () => {
            if (!connectMode) window.location.href = nd.detail_url;
        });

        nodesLayer.appendChild(group);
        nodeElMap.set(nd.id, { group, shape, sdot });
    };

    const clearConnectHL = () => {
        nodeElMap.forEach((el, id) => {
            const n = nodeMap.get(id);
            if (n) {
                const tc = TYPE_COLOR(n.type_slug || n.type);
                el.shape.setAttribute('stroke', tc.stroke);
                el.shape.setAttribute('stroke-width', '2.5');
            }
        });
    };

    const applyHighlight = () => {
        nodeElMap.forEach((el, id) => {
            const node = nodeMap.get(id);
            const selected = id === selNode;
            el.shape.setAttribute('stroke-width', selected ? '4.5' : '2.5');
            const baseLayerOpacity = getLayerNodeOpacity(node || {});
            const selectionOpacity = (!selNode || selected) ? 1 : 0.6;
            const traceOpacity = getTraceNodeOpacity(id);
            const finalOpacity = baseLayerOpacity * selectionOpacity * traceOpacity;
            el.shape.setAttribute('opacity', String(finalOpacity));
            el.group.setAttribute('opacity', String(finalOpacity));
        });
        edgeElMap.forEach((el, id) => {
            const edge = edgeMap.get(id);
            const active = id === selEdge;
            const baseLayerOpacity = getLayerEdgeOpacity(edge || {});
            const selectionOpacity = (!selEdge || active) ? 1 : 0.35;
            const traceOpacity = getTraceEdgeOpacity(id);
            const finalOpacity = baseLayerOpacity * selectionOpacity * traceOpacity;
            el.line.setAttribute('opacity', String(finalOpacity));
            el.group.setAttribute('opacity', String(finalOpacity));
        });
        zonesLayer.querySelectorAll('[data-branch-id]').forEach((zoneEl) => {
            const branchId = Number(zoneEl.getAttribute('data-branch-id'));
            zoneEl.setAttribute('opacity', (!selBranch || selBranch === branchId) ? '1' : '0.52');
        });
    };

    // ── Inspector ───────────────────────────────────────────────────
    const ic = document.getElementById('inspectorContent');
    const dc = document.getElementById('drilldownContent');

    const nodesForBranch = (branchId) => {
        return [...nodeMap.values()].filter(n => Number(n.branch_id) === Number(branchId));
    };

    const renderDrilldown = (branchId = null, selectedNodeId = null) => {
        if (!branchId) {
            dc.innerHTML = '<div class="inspector-empty">Selecciona una sede o un nodo para explorar el detalle.</div>';
            return;
        }

        const zone = zoneMap.get(branchId);
        const branchNodes = nodesForBranch(branchId).sort((a, b) => (a.label || '').localeCompare(b.label || ''));

        dc.innerHTML = `
            <div class="insp-type">Sede</div>
            <div class="insp-name">${zone?.name || 'N/A'}</div>
            <div class="insp-meta">Nodos: ${branchNodes.length}</div>
            <div class="insp-actions">
                <button class="topo-btn" onclick="window._newNode(${branchId})" style="width:100%;justify-content:center">＋ Nuevo elemento en sede</button>
                <a href="/sede/${branchId}" class="topo-btn" style="width:100%;justify-content:center">Abrir ficha sede</a>
                <a href="/sede/${branchId}/red" class="topo-btn" style="width:100%;justify-content:center">Abrir red de sede</a>
            </div>
            <div class="drill-list">
                ${branchNodes.map(node => `
                    <div class="drill-item" ${selectedNodeId === node.id ? 'style="border-color:#2563eb"' : ''}>
                        <div class="drill-item-main">
                            <span class="drill-item-name">${node.label}</span>
                            <span class="insp-status ${node.status || 'inactive'}">${node.status || 'N/A'}</span>
                        </div>
                        <div class="drill-item-meta">${node.type || 'Nodo'} · ${node.ip || 'sin IP'} · ${Number(node.connected_assets_count || 0)} activos</div>
                        <div class="drill-item-actions">
                            <button class="topo-btn" onclick="window._focusNode(${node.id})">Enfocar</button>
                            <button class="topo-btn" onclick="window._editNode(${node.id})">Características</button>
                            <a class="topo-btn" href="${node.detail_url}">Detalle</a>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    };

    window._focusNode = (nodeId) => {
        const node = nodeMap.get(nodeId);
        if (!node) return;
        selNode = nodeId;
        selEdge = null;
        selBranch = node.branch_id;
        applyHighlight();
        renderInspNode(node);
        renderDrilldown(node.branch_id, node.id);
        panX = Math.round((W / 2) - (node.x * zoom));
        panY = Math.round((H / 2) - (node.y * zoom));
        applyVP();
    };

    const renderInspBranch = (branchId) => {
        const zone = zoneMap.get(branchId);
        const branchNodes = nodesForBranch(branchId);
        const activeCount = branchNodes.filter(node => (node.status || '').toLowerCase() === 'active').length;
        const connectedAssets = branchNodes.reduce((acc, node) => acc + Number(node.connected_assets_count || 0), 0);
        const observedDevices = branchNodes.reduce((acc, node) => acc + Number(node.observed_devices_count || 0), 0);

        ic.innerHTML = `
            <div class="insp-type">Sede</div>
            <div class="insp-name">${zone?.name || 'N/A'}</div>
            <div class="insp-meta">Nodos totales: ${branchNodes.length}</div>
            <div class="insp-meta">Nodos activos: ${activeCount}</div>
            <div class="insp-meta">Activos conectados: ${connectedAssets}</div>
            <div class="insp-meta">Dispositivos observados: ${observedDevices}</div>
            <div class="insp-actions">
                <a href="/sede/${branchId}" class="topo-btn" style="width:100%;justify-content:center">Ver ficha de sede</a>
                <a href="/sede/${branchId}/red" class="topo-btn" style="width:100%;justify-content:center">Ver red de sede</a>
            </div>`;
    };

    const ownershipPill = (ownership) => {
        const tone = ownership?.tone || 'secondary';
        const label = ownership?.label || 'Sin clasificar';

        return `<span class="insp-status ${tone}">${esc(label)}</span>`;
    };

    const renderAssociatedAssetsHtml = (items) => {
        if (!items.length) {
            return '<div class="insp-meta" style="margin-top:6px;">Sin activos inventariados asociados.</div>';
        }

        return `
            <div class="insp-meta" style="margin-top:6px;margin-bottom:5px;">Activos inventariados:</div>
            <div class="drill-list" style="max-height:165px; margin-top:0; margin-bottom:8px;">
                ${items.map((asset) => `
                    <div class="drill-item" style="padding:6px 8px;">
                        <div class="drill-item-main">
                            <span class="drill-item-name">${esc(asset.label || 'Activo')}</span>
                            ${ownershipPill(asset.ownership)}
                        </div>
                        <div class="drill-item-meta">${esc(asset.equipment_type || 'Equipo')}${asset.hostname ? ` · ${esc(asset.hostname)}` : ''}${asset.domain_name ? ` · ${esc(asset.domain_name)}` : ''}</div>
                        <div class="drill-item-actions" style="margin-top:5px;">
                            <a class="topo-btn" href="${asset.detail_url || `/admin?edit_asset=${asset.id}#crud-assets`}">Editar activo</a>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    };

    const renderObservedDevicesHtml = (items) => {
        if (!items.length) {
            return '<div class="insp-meta" style="margin-top:6px;">Sin dispositivos descubiertos.</div>';
        }

        return `
            <div class="insp-meta" style="margin-top:6px;margin-bottom:5px;">Dispositivos observados:</div>
            <div class="drill-list" style="max-height:185px; margin-top:0; margin-bottom:8px;">
                ${items.map((device) => `
                    <div class="drill-item" style="padding:6px 8px;">
                        <div class="drill-item-main">
                            <span class="drill-item-name">${esc(device.hostname || device.mac_address || device.ip_address || 'Dispositivo detectado')}</span>
                            ${ownershipPill(device.ownership)}
                        </div>
                        <div class="drill-item-meta">${[
                            device.device_type || 'Desconocido',
                            device.ip_address,
                            device.mac_address,
                            device.domain_name,
                            device.switch_port ? `Puerto ${device.switch_port}` : null,
                            device.ssid ? `SSID ${device.ssid}` : null,
                        ].filter(Boolean).map(esc).join(' · ')}</div>
                        <div class="drill-item-meta">Fuente: ${esc(device.observed_via || 'discovery')}${device.vendor_name ? ` · ${esc(device.vendor_name)}` : ''}</div>
                        ${device.matched_asset ? `
                            <div class="drill-item-actions" style="margin-top:5px;">
                                <a class="topo-btn" href="${device.matched_asset.detail_url}">Activo relacionado</a>
                            </div>
                        ` : ''}
                    </div>
                `).join('')}
            </div>
        `;
    };

    const renderRelatedNodesHtml = (items) => {
        if (!items.length) {
            return '';
        }

        return `
            <div class="insp-meta" style="margin-top:6px;margin-bottom:5px;">Nodos relacionados:</div>
            <div class="drill-list" style="max-height:140px; margin-top:0; margin-bottom:8px;">
                ${items.map((peer) => `
                    <div class="drill-item" style="padding:6px 8px;">
                        <div class="drill-item-main">
                            <span class="drill-item-name">${esc(peer.name || 'Nodo')}</span>
                            <span class="insp-status ${(peer.status || '').toLowerCase() || 'inactive'}">${esc(peer.status || 'N/A')}</span>
                        </div>
                        <div class="drill-item-meta">${esc(peer.direction === 'incoming' ? 'Entrante' : 'Saliente')} · ${esc(peer.relation_type || 'linked_to')} · ${esc(peer.type || 'Nodo')}</div>
                        <div class="drill-item-actions" style="margin-top:5px;">
                            <a class="topo-btn" href="${peer.detail_url || `/nodos/${peer.id}`}">Ver nodo</a>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    };

    const renderPortsHtml = (items) => {
        if (!items.length) {
            return '';
        }

        return `
            <div class="insp-meta" style="margin-top:6px;margin-bottom:5px;">Puertos documentados:</div>
            <div class="drill-list" style="max-height:140px; margin-top:0; margin-bottom:8px;">
                ${items.map((port) => `
                    <div class="drill-item" style="padding:6px 8px;">
                        <div class="drill-item-main">
                            <span class="drill-item-name">${esc(port.name || 'Puerto')}</span>
                            <span class="insp-status ${(port.status || '').toLowerCase() || 'inactive'}">${esc(port.status || 'N/A')}</span>
                        </div>
                        <div class="drill-item-meta">${[port.vlan ? `VLAN ${port.vlan}` : null, port.speed, port.mac_address].filter(Boolean).map(esc).join(' · ')}</div>
                    </div>
                `).join('')}
            </div>
        `;
    };

    const loadNodeConnectionSnapshot = async (nodeId, { force = false } = {}) => {
        if (!force && nodeSnapshotCache.has(nodeId)) {
            return nodeSnapshotCache.get(nodeId);
        }
        if (pendingNodeSnapshotLoads.has(nodeId)) {
            return null;
        }

        pendingNodeSnapshotLoads.add(nodeId);
        try {
            const response = await fetch(`/red/nodos/${nodeId}`, {
                headers: { Accept: 'application/json' },
            });
            const data = await response.json();
            if (!response.ok || !data?.ok) {
                throw new Error(data?.message || `HTTP ${response.status}`);
            }

            const snapshot = data?.node?.connection_snapshot || null;
            if (snapshot) {
                nodeSnapshotCache.set(nodeId, snapshot);
                const currentNode = nodeMap.get(nodeId);
                if (currentNode) {
                    currentNode.connection_snapshot = snapshot;
                    currentNode.observed_devices_count = Number(snapshot?.summary?.observed_devices_count || currentNode.observed_devices_count || 0);
                    currentNode.connected_assets_count = Number(snapshot?.summary?.associated_assets_count || currentNode.connected_assets_count || 0);
                    nodeMap.set(nodeId, currentNode);
                }
            }

            if (selNode === nodeId) {
                const selectedNode = nodeMap.get(nodeId);
                if (selectedNode) renderInspNode(selectedNode);
            }

            return snapshot;
        } catch (error) {
            if (selNode === nodeId) {
                ic.innerHTML += `<div class="insp-meta" style="margin-top:8px;color:#fca5a5;">No se pudo cargar el detalle ampliado: ${esc(error?.message || 'error')}</div>`;
            }
            return null;
        } finally {
            pendingNodeSnapshotLoads.delete(nodeId);
        }
    };

    const renderInspNode = (nd) => {
        const conns = [...edgeMap.values()].filter(e => e.from === nd.id || e.to === nd.id).length;
        const snapshot = nd.connection_snapshot || nodeSnapshotCache.get(nd.id) || null;
        const connectedAssets = Array.isArray(snapshot?.associated_assets)
            ? snapshot.associated_assets
            : (Array.isArray(nd.connected_assets) ? nd.connected_assets : []);
        const observedDevices = Array.isArray(snapshot?.observed_devices) ? snapshot.observed_devices : [];
        const softwareSystems = Array.isArray(nd.software_systems) ? nd.software_systems : [];
        const relatedNodes = Array.isArray(snapshot?.related_nodes) ? snapshot.related_nodes : [];
        const ports = Array.isArray(snapshot?.ports) ? snapshot.ports : [];
        const managedObservedCount = Number(snapshot?.summary?.managed_observed_devices_count || 0);
        const externalObservedCount = Number(snapshot?.summary?.external_observed_devices_count || 0);
        const loadingSnapshot = !snapshot && !nodeSnapshotCache.has(nd.id);
        ic.innerHTML = `
            <div class="insp-type">${nd.type}</div>
            <div class="insp-name">${nd.label}</div>
            <div class="insp-meta">IP: ${nd.ip || 'N/A'}</div>
            <div class="insp-meta">Estado: <span class="insp-status ${nd.status}">${nd.status || '?'}</span></div>
            <div class="insp-pills">
                <span class="insp-pill">🔗 ${conns}</span>
                <span class="insp-pill">🖥 ${connectedAssets.length} activos</span>
                <span class="insp-pill">📡 ${snapshot ? observedDevices.length : Number(nd.observed_devices_count || 0)} observados</span>
                <span class="insp-pill">💿 ${softwareSystems.length} software</span>
            </div>
            ${snapshot ? `
                <div class="insp-pills" style="margin-top:6px;">
                    <span class="insp-pill">✅ ${managedObservedCount} propios descubiertos</span>
                    <span class="insp-pill">🚫 ${externalObservedCount} ajenos</span>
                </div>
            ` : ''}
            ${softwareSystems.length > 0 ? `
                <div class="insp-meta" style="margin-top:6px;margin-bottom:5px;">Software asociado:</div>
                <div class="drill-list" style="max-height:120px; margin-top:0; margin-bottom:8px;">
                    ${softwareSystems.map(sw => `
                        <div class="drill-item" style="padding:6px 8px;">
                            <div class="drill-item-main">
                                <span class="drill-item-name">${sw.name || 'Software'}</span>
                            </div>
                            <div class="drill-item-meta">${sw.version || 'sin versión'}</div>
                        </div>
                    `).join('')}
                </div>
            ` : ''}
            ${renderAssociatedAssetsHtml(connectedAssets)}
            ${snapshot ? renderObservedDevicesHtml(observedDevices) : ''}
            ${snapshot ? renderRelatedNodesHtml(relatedNodes) : ''}
            ${snapshot ? renderPortsHtml(ports) : ''}
            ${loadingSnapshot ? '<div class="insp-meta" style="margin-top:8px;">Cargando detalle ampliado del nodo...</div>' : ''}
            <div class="insp-actions">
                <button class="topo-btn" onclick="window._editNode(${nd.id})" style="width:100%;justify-content:center">✏ Características en modal</button>
                <button class="topo-btn" onclick="window._traceFromNode(${nd.id})" style="width:100%;justify-content:center">🧭 Usar como origen</button>
                <a href="${nd.detail_url}" class="topo-btn" style="width:100%;justify-content:center">Ver ficha</a>
                <a href="${nd.branch_url}" class="topo-btn" style="width:100%;justify-content:center">Ver sede</a>
                <a href="${nd.branch_network_url}" class="topo-btn" style="width:100%;justify-content:center">Ver red de sede</a>
                <a href="/admin?edit_node=${nd.id}" class="topo-btn" style="width:100%;justify-content:center">✏ Configurar</a>
            </div>`;

        if (!snapshot) {
            loadNodeConnectionSnapshot(nd.id);
        }
    };

    const renderInspEdge = (edge) => {
        const fn = nodeMap.get(edge.from), tn = nodeMap.get(edge.to);
        const st = EDGE_STYLE(edge.label);
        ic.innerHTML = `
            <div class="insp-type">Conexión</div>
            <div class="insp-name" style="color:${st.stroke}">${EDGE_DISPLAY_LABEL(edge)}</div>
            <div class="insp-meta">Desde: ${fn?.label || edge.from}</div>
            ${edge.from_endpoint ? `<div class="insp-meta">↳ Origen asignado a: ${edge.from_endpoint}</div>` : ''}
            <div class="insp-meta">Hasta: ${tn?.label || edge.to}</div>
            ${edge.to_endpoint ? `<div class="insp-meta">↳ Destino asignado a: ${edge.to_endpoint}</div>` : ''}
            ${edge.preferred_weight ? `<div class="insp-meta">Peso preferido traceroute: ${edge.preferred_weight}</div>` : ''}
            ${edge.vpn_profile ? `<div class="insp-meta">VPN perfil: ${edge.vpn_profile}</div>` : ''}
            ${edge.is_inter_campus ? `<div class="insp-meta">Tipo de alcance: Inter-campus</div>` : ''}
            ${edge.notes ? `<div class="insp-meta">Notas: ${edge.notes}</div>` : ''}
            <div class="insp-actions">
                <button class="topo-btn" onclick="window._editEdge(${edge.id})" style="width:100%;justify-content:center">✏ Editar conexión</button>
                <a href="${fn?.detail_url || '#'}" class="topo-btn" style="width:100%;justify-content:center">Ver nodo origen</a>
                <a href="${tn?.detail_url || '#'}" class="topo-btn" style="width:100%;justify-content:center">Ver nodo destino</a>
                <button class="topo-btn danger" onclick="window._delEdge(${edge.id})" style="width:100%;justify-content:center">🗑 Eliminar conexión</button>
            </div>`;
    };

    window._editEdge = (edgeId) => {
        const edge = edgeMap.get(edgeId);
        if (!edge) return;

        openModal({
            mode: 'edit',
            edgeId,
            fromId: edge.from,
            toId: edge.to,
            relationType: edge.label,
            preferredWeight: edge.preferred_weight,
            fromEndpoint: edge.from_endpoint,
            toEndpoint: edge.to_endpoint,
            isInterCampus: !!edge.is_inter_campus,
            vpnProfile: edge.vpn_profile,
            notes: edge.notes,
        });
    };

    window._delEdge = async (edgeId) => {
        const confirmed = await window.itcityConfirm({
            title: 'Eliminar conexión',
            text: '¿Eliminar esta conexión?',
            icon: 'warning',
            confirmButtonText: 'Sí, eliminar',
        });
        if (!confirmed) return;
        try {
            const r = await fetch(`/red/relacion/${edgeId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            if (!r.ok) throw new Error('Error ' + r.status);
            edgeMap.delete(edgeId);
            edgeElMap.get(edgeId)?.group.remove();
            edgeElMap.delete(edgeId);
            selEdge = null;
            ic.innerHTML = '<div class="inspector-empty">Conexión eliminada.</div>';
            setStatus('Conexión eliminada ✓', 'ok');
            window.itcityAlert({
                icon: 'success',
                title: 'Conexión eliminada',
                text: 'El enlace fue eliminado correctamente.',
                toast: true,
                position: 'top-end',
            });
        } catch (e) {
            setStatus('Error: ' + e.message, 'err');
            window.itcityAlert({
                icon: 'error',
                title: 'Error al eliminar conexión',
                text: e.message,
                toast: true,
                position: 'top-end',
            });
        }
    };

    // ── Edge modal ─────────────────────────────────────────────────────
    const EDGE_TYPES = [
        { type: 'linked_to',    label: 'Enlace directo', icon: '🔗' },
        { type: 'fiber',        label: 'Fibra óptica',   icon: '💡' },
        { type: 'vpn',          label: 'VPN',            icon: '🔐' },
        { type: 'wan',          label: 'WAN / Internet', icon: '🌐' },
        { type: 'wireless',     label: 'Inalámbrico',    icon: '📶' },
        { type: 'inter-campus', label: 'Inter-campus',   icon: '🏙' },
    ];

    const edgeModal = document.getElementById('edgeModal');
    const edgeTypeGrid = document.getElementById('edgeTypeGrid');
    const edgeModalNodes = document.getElementById('edgeModalNodes');
    const edgeFromNode = document.getElementById('edgeFromNode');
    const edgeToNode = document.getElementById('edgeToNode');
    const edgeFromEndpoint = document.getElementById('edgeFromEndpoint');
    const edgeToEndpoint = document.getElementById('edgeToEndpoint');
    const edgeInterCampus = document.getElementById('edgeInterCampus');
    const edgeVpnProfileWrap = document.getElementById('edgeVpnProfileWrap');
    const edgeVpnProfile = document.getElementById('edgeVpnProfile');
    const edgePreferredWeight = document.getElementById('edgePreferredWeight');
    const edgeNotes = document.getElementById('edgeNotes');
    const edgeModalCreate = document.getElementById('edgeModalCreate');
    let pendingEdge = null;
    let selectedEdgeType = null;
    let edgeModalMode = 'create';
    let editingEdgeId = null;

    const nodeOptionsHtml = (selectedId) => {
        return [...nodeMap.values()]
            .sort((a, b) => {
                const aKey = `${a.branch_name || ''} ${a.label || ''}`;
                const bKey = `${b.branch_name || ''} ${b.label || ''}`;
                return aKey.localeCompare(bKey);
            })
            .map(node => `<option value="${node.id}" ${Number(selectedId) === Number(node.id) ? 'selected' : ''}>${node.branch_name || 'Sede'} · ${node.label}</option>`)
            .join('');
    };

    const refreshModalNodeSubtitle = () => {
        const fromId = Number(edgeFromNode.value || 0);
        const toId = Number(edgeToNode.value || 0);
        const fromNode = nodeMap.get(fromId);
        const toNode = nodeMap.get(toId);
        const fromZone = fromNode ? zoneMap.get(fromNode.branch_id) : null;
        const toZone = toNode ? zoneMap.get(toNode.branch_id) : null;
        edgeModalNodes.textContent = `${fromNode?.label || 'N/A'} (${fromZone?.name || 'N/A'}) ↔ ${toNode?.label || 'N/A'} (${toZone?.name || 'N/A'})`;
    };

    const applyEdgeTypeSelection = (type) => {
        selectedEdgeType = type;
        edgeTypeGrid.querySelectorAll('.edge-type-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.type === type);
        });
        edgeModalCreate.disabled = !selectedEdgeType;
        const isVpn = (selectedEdgeType || '').toLowerCase().includes('vpn');
        edgeVpnProfileWrap.style.display = isVpn ? 'block' : 'none';
        if (!isVpn) edgeVpnProfile.value = '';
    };

    const openModal = ({ mode = 'create', edgeId = null, fromId, toId, relationType = null, preferredWeight = null, fromEndpoint = null, toEndpoint = null, isInterCampus = null, vpnProfile = null, notes = null }) => {
        const fromNode = nodeMap.get(fromId);
        const toNode = nodeMap.get(toId);
        pendingEdge = { fromId, toId, fromNode, toNode };
        edgeModalMode = mode;
        editingEdgeId = edgeId;
        selectedEdgeType = null;
        edgeModalCreate.disabled = true;
        edgeModalCreate.textContent = mode === 'edit' ? 'Guardar cambios' : 'Crear conexión';

        edgeFromNode.innerHTML = nodeOptionsHtml(fromId);
        edgeToNode.innerHTML = nodeOptionsHtml(toId);
        edgeFromEndpoint.value = '';
        edgeToEndpoint.value = '';
        edgeVpnProfile.value = '';
        edgePreferredWeight.value = '';
        edgeNotes.value = '';

        refreshModalNodeSubtitle();

        const isCrossCampus = !!fromNode && !!toNode && fromNode.branch_id !== toNode.branch_id;
        edgeInterCampus.checked = (isInterCampus ?? isCrossCampus);
        edgeFromEndpoint.value = fromEndpoint || '';
        edgeToEndpoint.value = toEndpoint || '';
        edgeVpnProfile.value = vpnProfile || '';
        edgePreferredWeight.value = preferredWeight != null ? preferredWeight : '';
        edgeNotes.value = notes || '';

        edgeTypeGrid.innerHTML = EDGE_TYPES.map(et => {
            const st = EDGE_STYLE(et.type);
            return `<button class="edge-type-btn" data-type="${et.type}" style="border-color:${st.stroke}">
                <span class="etb-icon">${et.icon}</span>
                <span class="etb-label">${et.label}</span>
            </button>`;
        }).join('');
        edgeTypeGrid.querySelectorAll('.edge-type-btn').forEach(btn => {
            btn.addEventListener('click', () => applyEdgeTypeSelection(btn.dataset.type));
        });
        applyEdgeTypeSelection(relationType);
        edgeFromNode.onchange = () => {
            refreshModalNodeSubtitle();
            const fromNodeSel = nodeMap.get(Number(edgeFromNode.value || 0));
            const toNodeSel = nodeMap.get(Number(edgeToNode.value || 0));
            edgeInterCampus.checked = !!fromNodeSel && !!toNodeSel && fromNodeSel.branch_id !== toNodeSel.branch_id;
        };
        edgeToNode.onchange = () => {
            refreshModalNodeSubtitle();
            const fromNodeSel = nodeMap.get(Number(edgeFromNode.value || 0));
            const toNodeSel = nodeMap.get(Number(edgeToNode.value || 0));
            edgeInterCampus.checked = !!fromNodeSel && !!toNodeSel && fromNodeSel.branch_id !== toNodeSel.branch_id;
        };
        edgeModal.style.display = 'flex';
    };

    const confirmEdge = async () => {
        if (!pendingEdge || !selectedEdgeType) return;
        const fromId = Number(edgeFromNode.value || 0);
        const toId = Number(edgeToNode.value || 0);
        if (fromId === toId) {
            setStatus('El nodo origen y destino deben ser distintos.', 'err');
            return;
        }
        const relationType = selectedEdgeType;
        const payload = {
            from_node_id: fromId,
            to_node_id: toId,
            relation_type: relationType,
            preferred_weight: edgePreferredWeight.value ? Number(edgePreferredWeight.value) : null,
            from_endpoint: edgeFromEndpoint.value.trim() || null,
            to_endpoint: edgeToEndpoint.value.trim() || null,
            is_inter_campus: edgeInterCampus.checked,
            vpn_profile: edgeVpnProfile.value.trim() || null,
            notes: edgeNotes.value.trim() || null,
        };
        try {
            const isEdit = edgeModalMode === 'edit' && editingEdgeId;
            const r = await fetch(isEdit ? `${createRelUrl}/${editingEdgeId}` : createRelUrl, {
                method: isEdit ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await r.json();
            if (!data.ok) throw new Error(data.message || 'Error');
            const newEdge = {
                id: isEdit ? Number(editingEdgeId) : data.id,
                from: fromId,
                to: toId,
                label: relationType,
                preferred_weight: data.preferred_weight ?? payload.preferred_weight,
                from_endpoint: payload.from_endpoint,
                to_endpoint: payload.to_endpoint,
                is_inter_campus: !!(data.is_inter_campus ?? payload.is_inter_campus),
                vpn_profile: payload.vpn_profile,
                notes: payload.notes,
            };
            edgeMap.set(newEdge.id, newEdge);
            buildEdge(newEdge);
            selEdge = newEdge.id;
            selNode = null;
            applyHighlight();
            renderInspEdge(newEdge);
            edgeModal.style.display = 'none';
            pendingEdge = null;
            editingEdgeId = null;
            edgeModalMode = 'create';
            if (!isEdit) setMode('select');
            setStatus(isEdit ? `Conexión "${relationType}" actualizada ✓` : `Conexión "${relationType}" creada ✓`, 'ok');
        } catch (e) { setStatus('Error: ' + e.message, 'err'); }
    };

    edgeModalCreate.addEventListener('click', confirmEdge);

    document.getElementById('edgeModalCancel').addEventListener('click', () => {
        const wasCreateMode = edgeModalMode === 'create';
        edgeModal.style.display = 'none'; pendingEdge = null; selectedEdgeType = null;
        editingEdgeId = null; edgeModalMode = 'create';
        connectSrc = null; clearConnectHL();
        tempLine.setAttribute('opacity', '0');
        if (wasCreateMode) setMode('select');
    });

    // ── Node modal ─────────────────────────────────────────────────────
    const nodeModal = document.getElementById('nodeModal');
    const nodeModalTitle = document.getElementById('nodeModalTitle');
    const nodeModalSubtitle = document.getElementById('nodeModalSubtitle');
    const nodeModalSave = document.getElementById('nodeModalSave');
    const nodeTabGeneral = document.getElementById('nodeTabGeneral');
    const nodeTabSoftware = document.getElementById('nodeTabSoftware');
    const nodePaneGeneral = document.getElementById('nodePaneGeneral');
    const nodePaneSoftware = document.getElementById('nodePaneSoftware');
    const nodeBranchId = document.getElementById('nodeBranchId');
    const nodeTypeId = document.getElementById('nodeTypeId');
    const nodeTypePreviewShape = document.getElementById('nodeTypePreviewShape');
    const nodeTypePreviewIcon = document.getElementById('nodeTypePreviewIcon');
    const nodeTypePreviewName = document.getElementById('nodeTypePreviewName');
    const nodeTypePreviewSlug = document.getElementById('nodeTypePreviewSlug');
    const nodeTypePreviewHelp = document.getElementById('nodeTypePreviewHelp');
    const nodeName = document.getElementById('nodeName');
    const nodeIpAddress = document.getElementById('nodeIpAddress');
    const nodeStatus = document.getElementById('nodeStatus');
    const nodeFloor = document.getElementById('nodeFloor');
    const nodeRoom = document.getElementById('nodeRoom');
    const nodeCableType = document.getElementById('nodeCableType');
    const nodeIsMonitored = document.getElementById('nodeIsMonitored');
    const nodeDetails = document.getElementById('nodeDetails');
    const nodeSoftwareHint = document.getElementById('nodeSoftwareHint');
    const softwareResultCount = document.getElementById('softwareResultCount');
    const nodeSoftwareList = document.getElementById('nodeSoftwareList');
    const softwareName = document.getElementById('softwareName');
    const softwareVersion = document.getElementById('softwareVersion');
    const softwareVendor = document.getElementById('softwareVendor');
    const softwareProject = document.getElementById('softwareProject');
    const softwareSearch = document.getElementById('softwareSearch');
    const softwareSearchClear = document.getElementById('softwareSearchClear');
    const softwareContactEmail = document.getElementById('softwareContactEmail');
    const softwareContactPhone = document.getElementById('softwareContactPhone');
    const softwareDetails = document.getElementById('softwareDetails');
    const softwareSaveBtn = document.getElementById('softwareSaveBtn');
    const softwareResetBtn = document.getElementById('softwareResetBtn');

    const nodeByIdUrl = (nodeId) => `${createNodeUrl}/${nodeId}`;
    const nodeSoftwareUrl = (nodeId) => `${createNodeUrl}/${nodeId}/software`;
    const softwareByIdUrl = (softwareId) => `${createSoftwareUrl}/${softwareId}`;
    let nodeModalMode = 'create';
    let editingNodeId = null;
    let softwareEditingId = null;
    let softwareCache = [];

    const switchNodeTab = (tab) => {
        const isGeneral = tab === 'general';
        nodeTabGeneral.classList.toggle('active', isGeneral);
        nodeTabSoftware.classList.toggle('active', !isGeneral);
        nodePaneGeneral.classList.toggle('active', isGeneral);
        nodePaneSoftware.classList.toggle('active', !isGeneral);
    };

    const resetSoftwareForm = (preserveSearch = false) => {
        softwareEditingId = null;
        softwareName.value = '';
        softwareVersion.value = '';
        softwareVendor.value = '';
        softwareProject.value = '';
        if (!preserveSearch) {
            softwareSearch.value = '';
        }
        softwareSearchClear.style.visibility = 'hidden';
        softwareContactEmail.value = '';
        softwareContactPhone.value = '';
        softwareDetails.value = '';
        softwareSaveBtn.textContent = 'Guardar software';

        if (preserveSearch) {
            softwareSearchClear.style.visibility = softwareSearch.value.trim() ? 'visible' : 'hidden';
        }
    };

    const syncSoftwareSearchClear = () => {
        softwareSearchClear.style.visibility = softwareSearch.value.trim() ? 'visible' : 'hidden';
    };

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const highlightMatch = (value, term) => {
        const safeValue = escapeHtml(value || '');
        const normalizedTerm = (term || '').trim();
        if (!normalizedTerm) return safeValue;

        const safeTerm = escapeHtml(normalizedTerm).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        if (!safeTerm) return safeValue;

        const regex = new RegExp(`(${safeTerm})`, 'ig');
        return safeValue.replace(regex, '<strong>$1</strong>');
    };

    const renderSoftwareList = () => {
        const term = (softwareSearch.value || '').trim().toLowerCase();
        const filteredSoftware = !term
            ? softwareCache
            : softwareCache.filter(sw => {
                const haystack = `${sw.name || ''} ${sw.vendor || ''} ${sw.project_name || ''} ${sw.version || ''} ${sw.contact_email || ''} ${sw.contact_phone || ''}`.toLowerCase();
                return haystack.includes(term);
            });

        softwareResultCount.textContent = term
            ? `${filteredSoftware.length} resultado(s) de ${softwareCache.length}`
            : `${softwareCache.length} resultado(s)`;

        if (!filteredSoftware.length) {
            if (!softwareCache.length) {
                nodeSoftwareList.innerHTML = '<div class="inspector-empty">Aún no hay software en este nodo.</div>';
            } else {
                nodeSoftwareList.innerHTML = '<div class="inspector-empty">Sin resultados para la búsqueda actual.</div>';
            }
            return;
        }

        nodeSoftwareList.innerHTML = filteredSoftware.map(sw => `
            <div class="software-item">
                <div class="title">${highlightMatch(sw.name || '', term) || 'sin nombre'}</div>
                <div class="meta">${highlightMatch(sw.version || 'sin versión', term)} · ${highlightMatch(sw.vendor || 'sin vendor', term)}</div>
                <div class="meta">${highlightMatch(sw.project_name || 'sin proyecto', term)} · ${highlightMatch(sw.contact_email || sw.contact_phone || 'sin contacto', term)}</div>
                <div class="actions">
                    <button class="topo-btn" onclick="window._editSoftware(${sw.id})">Editar</button>
                    <button class="topo-btn danger" onclick="window._deleteSoftware(${sw.id})">Eliminar</button>
                </div>
            </div>
        `).join('');
    };

    const loadNodeSoftware = async (nodeId) => {
        if (!nodeId) {
            softwareCache = [];
            renderSoftwareList();
            return;
        }

        const response = await fetch(nodeSoftwareUrl(nodeId), {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
        });
        const data = await response.json();
        if (!response.ok || !data.ok) throw new Error(data.message || 'No se pudo cargar software');
        softwareCache = Array.isArray(data.software) ? data.software : [];
        renderSoftwareList();
    };

    const populateSoftwareForm = (sw) => {
        softwareEditingId = sw.id;
        softwareName.value = sw.name || '';
        softwareVersion.value = sw.version || '';
        softwareVendor.value = sw.vendor || '';
        softwareProject.value = sw.project_name || '';
        softwareContactEmail.value = sw.contact_email || '';
        softwareContactPhone.value = sw.contact_phone || '';
        softwareDetails.value = sw.details ? JSON.stringify(sw.details, null, 2) : '';
        softwareSaveBtn.textContent = 'Guardar cambios';
    };

    const branchNameById = (branchId) => branchOptions.find(b => Number(b.id) === Number(branchId))?.name || 'Sede';

    const fillNodeSelects = () => {
        nodeBranchId.innerHTML = branchOptions
            .map(branch => `<option value="${branch.id}">${branch.name}</option>`)
            .join('');

        nodeTypeId.innerHTML = nodeTypeOptions
            .map(type => `<option value="${type.id}" data-slug="${type.slug || ''}" data-icon="${type.icon || ''}">${type.icon ? `${type.icon} · ` : ''}${type.name}</option>`)
            .join('');

        syncNodeTypePreview();
    };

    const resolveNodeTypePreviewVariant = (slug) => {
        const value = String(slug || '').toLowerCase();
        if (value.includes('router')) return 'variant-router';
        if (value.includes('switch')) return 'variant-switch';
        if (value.includes('firewall')) return 'variant-firewall';
        if (value.includes('access') || value.includes('ap')) return 'variant-access-point';
        if (value.includes('vpn')) return 'variant-vpn-gateway';
        if (value.includes('database') || value.includes('db') || value.includes('sql')) return 'variant-database';
        if (value.includes('load-balancer') || value.includes('balancer')) return 'variant-load-balancer';
        if (value.includes('pbx') || value.includes('telefon')) return 'variant-pbx';
        if (value.includes('camera')) return 'variant-ip-camera';
        if (value.includes('printer') || value.includes('print')) return 'variant-printer';
        if (value.includes('storage') || value.includes('nas')) return 'variant-storage';
        if (value.includes('server') || value.includes('serv')) return 'variant-server';
        return 'variant-default';
    };

    const syncNodeTypePreview = () => {
        if (!nodeTypeId || !nodeTypePreviewShape) return;
        const option = nodeTypeId.options[nodeTypeId.selectedIndex];
        const slug = option?.dataset?.slug || '';
        const icon = option?.dataset?.icon || 'N';
        const name = option?.textContent?.split('·').pop()?.trim() || option?.textContent || 'Nodo';
        const variant = resolveNodeTypePreviewVariant(slug);
        nodeTypePreviewShape.className = `node-type-preview-shape ${variant}`;
        nodeTypePreviewIcon.textContent = icon || 'N';
        nodeTypePreviewName.textContent = name;
        nodeTypePreviewSlug.textContent = `slug: ${slug || 'generic-node'}`;
        nodeTypePreviewHelp.textContent = `Se agregará como ${name.toLowerCase()} en la sede seleccionada.`;
    };

    const normalizeNodeForMap = (node) => {
        const selectedType = nodeTypeOptions.find(type => Number(type.id) === Number(node.node_type_id));
        return {
            id: Number(node.id),
            label: node.label || node.name,
            type: node.type || node.type_name || selectedType?.name || 'Nodo',
            type_slug: node.type_slug || selectedType?.slug || '',
            branch_id: Number(node.branch_id),
            branch_name: node.branch_name || branchNameById(node.branch_id),
            status: node.status || 'inactive',
            ip: node.ip || node.ip_address || null,
            layout_x: node.layout_x ?? null,
            layout_y: node.layout_y ?? null,
            x: Number.isFinite(Number(node.layout_x)) ? Number(node.layout_x) : undefined,
            y: Number.isFinite(Number(node.layout_y)) ? Number(node.layout_y) : undefined,
            branch_url: node.branch_url || `/sede/${node.branch_id}`,
            branch_network_url: node.branch_network_url || `/sede/${node.branch_id}/red`,
            detail_url: node.detail_url || `/nodos/${node.id}`,
            connections_url: node.connections_url || `/red/nodos/${node.id}`,
            connected_assets_count: Number(node.connected_assets_count || 0),
            observed_devices_count: Number(node.observed_devices_count || 0),
            connection_snapshot: node.connection_snapshot || null,
        };
    };

    const findNextNodePosition = (branchId) => {
        const zone = zoneMap.get(Number(branchId));
        const currentBranchNodes = nodesForBranch(Number(branchId));
        const idx = currentBranchNodes.length;
        const count = Math.max(1, idx + 1);
        const cols = Math.max(1, Math.ceil(Math.sqrt(count)));
        const rows = Math.max(1, Math.ceil(count / cols));
        const usableW = Math.max(120, (zone?.w ?? ZONE_BASE_W) - ZONE_MARGIN * 2);
        const usableH = Math.max(120, (zone?.h ?? ZONE_BASE_H) - ZONE_MARGIN * 2 - ZONE_TITLE_OFFSET);
        const cellW = usableW / cols;
        const cellH = usableH / rows;
        const c = idx % cols;
        const r = Math.floor(idx / cols);

        return {
            x: zone ? zone.x + ZONE_MARGIN + c * cellW + cellW / 2 : 100 + idx * 80,
            y: zone ? zone.y + ZONE_TITLE_OFFSET + ZONE_MARGIN + r * cellH + cellH / 2 : 100,
        };
    };

    const openNodeModalCreate = (branchId = null) => {
        nodeModalMode = 'create';
        editingNodeId = null;
        fillNodeSelects();
        switchNodeTab('general');
        resetSoftwareForm();
        softwareCache = [];
        renderSoftwareList();
        nodeSoftwareHint.textContent = 'Guarda primero el nodo para administrar software.';
        nodeSoftwareHint.style.display = 'block';
        nodeModalTitle.textContent = 'Nuevo elemento';
        nodeModalSubtitle.textContent = 'Agregar un nuevo nodo/equipo a la topología actual.';
        nodeModalSave.textContent = 'Crear elemento';

        nodeBranchId.value = String(branchId || selBranch || branchOptions[0]?.id || '');
        nodeTypeId.value = String(nodeTypeOptions[0]?.id || '');
        syncNodeTypePreview();
        nodeName.value = '';
        nodeIpAddress.value = '';
        nodeStatus.value = 'active';
        nodeFloor.value = '';
        nodeRoom.value = '';
        nodeCableType.value = '';
        nodeIsMonitored.checked = true;
        nodeDetails.value = '';

        nodeModal.style.display = 'flex';
    };

    const openNodeModalEdit = async (nodeId) => {
        fillNodeSelects();
        const node = nodeMap.get(Number(nodeId));
        if (!node) return;

        nodeModalMode = 'edit';
        editingNodeId = Number(nodeId);
        switchNodeTab('general');
        resetSoftwareForm();
        nodeSoftwareHint.style.display = 'none';
        nodeModalTitle.textContent = 'Características del elemento';
        nodeModalSubtitle.textContent = `${node.label} · ${node.type || 'Nodo'} · ${node.ip || 'sin IP'}`;
        nodeModalSave.textContent = 'Guardar cambios';

        try {
            const response = await fetch(nodeByIdUrl(nodeId), {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.message || 'No se pudo cargar el nodo');

            nodeBranchId.value = String(data.node.branch_id ?? node.branch_id ?? branchOptions[0]?.id ?? '');
            nodeTypeId.value = String(data.node.node_type_id ?? nodeTypeOptions[0]?.id ?? '');
            syncNodeTypePreview();
            nodeName.value = data.node.name || node.label || '';
            nodeIpAddress.value = data.node.ip_address || node.ip || '';
            nodeStatus.value = data.node.status || 'active';
            nodeFloor.value = data.node.floor || '';
            nodeRoom.value = data.node.room || '';
            nodeCableType.value = data.node.cable_type || '';
            nodeIsMonitored.checked = !!data.node.is_monitored;
            nodeDetails.value = data.node.details ? JSON.stringify(data.node.details, null, 2) : '';
        } catch (error) {
            setStatus('Error al cargar nodo: ' + error.message, 'err');
            return;
        }

        try {
            await loadNodeSoftware(editingNodeId);
        } catch (error) {
            setStatus('Error al cargar software: ' + error.message, 'err');
        }

        nodeModal.style.display = 'flex';
    };

    const saveNodeModal = async () => {
        const parsedBranchId = Number(nodeBranchId.value || 0);
        const parsedTypeId = Number(nodeTypeId.value || 0);
        if (!parsedBranchId || !parsedTypeId || !nodeName.value.trim()) {
            setStatus('Completa sede, tipo y nombre del elemento.', 'err');
            return;
        }

        let detailsPayload = null;
        const detailsText = nodeDetails.value.trim();
        if (detailsText) {
            try {
                detailsPayload = JSON.parse(detailsText);
            } catch (error) {
                setStatus('Características JSON inválidas.', 'err');
                return;
            }
        }

        const position = nodeModalMode === 'create'
            ? findNextNodePosition(parsedBranchId)
            : {
                x: nodeMap.get(Number(editingNodeId))?.x,
                y: nodeMap.get(Number(editingNodeId))?.y,
            };

        const payload = {
            branch_id: parsedBranchId,
            node_type_id: parsedTypeId,
            name: nodeName.value.trim(),
            status: nodeStatus.value,
            ip_address: nodeIpAddress.value.trim() || null,
            floor: nodeFloor.value.trim() || null,
            room: nodeRoom.value.trim() || null,
            cable_type: nodeCableType.value.trim() || null,
            is_monitored: nodeIsMonitored.checked,
            details: detailsPayload,
            layout_x: position.x ?? null,
            layout_y: position.y ?? null,
        };

        try {
            const isEdit = nodeModalMode === 'edit' && editingNodeId;
            const response = await fetch(isEdit ? nodeByIdUrl(editingNodeId) : createNodeUrl, {
                method: isEdit ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.message || 'No se pudo guardar el nodo');

            const normalizedNode = normalizeNodeForMap(data.node);
            if (!Number.isFinite(normalizedNode.x) || !Number.isFinite(normalizedNode.y)) {
                const autoPos = findNextNodePosition(normalizedNode.branch_id);
                normalizedNode.x = autoPos.x;
                normalizedNode.y = autoPos.y;
            }

            nodeMap.set(normalizedNode.id, normalizedNode);
            buildNode(normalizedNode);
            rebuildEdgesForNode(normalizedNode.id);
            buildLegend();
            buildTraceOptions();
            schedulePersistPersonalLayout(true);

            selNode = normalizedNode.id;
            selEdge = null;
            selBranch = normalizedNode.branch_id;
            applyHighlight();
            renderInspNode(normalizedNode);
            renderDrilldown(normalizedNode.branch_id, normalizedNode.id);
            if (!isEdit) {
                nodeModalMode = 'edit';
                editingNodeId = normalizedNode.id;
                nodeModalTitle.textContent = 'Características del elemento';
                nodeModalSubtitle.textContent = `${normalizedNode.label} · ${normalizedNode.type || 'Nodo'} · ${normalizedNode.ip || 'sin IP'}`;
                nodeModalSave.textContent = 'Guardar cambios';
                nodeSoftwareHint.style.display = 'none';
                try {
                    await loadNodeSoftware(editingNodeId);
                } catch {
                    softwareCache = [];
                    renderSoftwareList();
                }
                switchNodeTab('software');
            } else {
                nodeModal.style.display = 'none';
            }

            setStatus(isEdit ? 'Elemento actualizado ✓' : 'Elemento creado ✓', 'ok');
        } catch (error) {
            setStatus('Error al guardar elemento: ' + error.message, 'err');
        }
    };

    nodeTypeId.addEventListener('change', syncNodeTypePreview);

    window._newNode = (branchId = null) => openNodeModalCreate(branchId);
    window._editNode = (nodeId) => openNodeModalEdit(nodeId);
    window._editSoftware = (softwareId) => {
        const sw = softwareCache.find(item => Number(item.id) === Number(softwareId));
        if (!sw) return;
        populateSoftwareForm(sw);
        switchNodeTab('software');
    };
    window._deleteSoftware = async (softwareId) => {
        const confirmed = await window.itcityConfirm({
            title: 'Eliminar sistema',
            text: '¿Eliminar este software del nodo?',
            icon: 'warning',
            confirmButtonText: 'Sí, eliminar',
        });
        if (!confirmed) return;
        try {
            const response = await fetch(softwareByIdUrl(softwareId), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.message || 'No se pudo eliminar software');
            softwareCache = softwareCache.filter(sw => Number(sw.id) !== Number(softwareId));
            renderSoftwareList();
            resetSoftwareForm(true);
            setStatus('Software eliminado ✓', 'ok');
            window.itcityAlert({
                icon: 'success',
                title: 'Sistema eliminado',
                text: 'El software fue eliminado correctamente.',
                toast: true,
                position: 'top-end',
            });
        } catch (error) {
            setStatus('Error al eliminar software: ' + error.message, 'err');
            window.itcityAlert({
                icon: 'error',
                title: 'Error al eliminar sistema',
                text: error.message,
                toast: true,
                position: 'top-end',
            });
        }
    };

    const saveSoftware = async () => {
        if (!editingNodeId) {
            setStatus('Guarda primero el nodo para asignar software.', 'err');
            return;
        }
        if (!softwareName.value.trim()) {
            setStatus('Indica el nombre del software.', 'err');
            return;
        }

        let detailsPayload = null;
        const detailsText = softwareDetails.value.trim();
        if (detailsText) {
            try {
                detailsPayload = JSON.parse(detailsText);
            } catch {
                setStatus('Detalles de software JSON inválidos.', 'err');
                return;
            }
        }

        const payload = {
            node_id: editingNodeId,
            name: softwareName.value.trim(),
            version: softwareVersion.value.trim() || null,
            vendor: softwareVendor.value.trim() || null,
            contact_email: softwareContactEmail.value.trim() || null,
            contact_phone: softwareContactPhone.value.trim() || null,
            project_name: softwareProject.value.trim() || null,
            details: detailsPayload,
        };

        try {
            const isEdit = !!softwareEditingId;
            const response = await fetch(isEdit ? softwareByIdUrl(softwareEditingId) : createSoftwareUrl, {
                method: isEdit ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.message || 'No se pudo guardar software');

            if (isEdit) {
                softwareCache = softwareCache.map(sw => Number(sw.id) === Number(softwareEditingId) ? data.software : sw);
            } else {
                softwareCache.push(data.software);
                softwareCache.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
            }
            renderSoftwareList();
            resetSoftwareForm(true);
            setStatus(isEdit ? 'Software actualizado ✓' : 'Software agregado ✓', 'ok');
        } catch (error) {
            setStatus('Error al guardar software: ' + error.message, 'err');
        }
    };

    nodeTabGeneral.addEventListener('click', () => switchNodeTab('general'));
    nodeTabSoftware.addEventListener('click', () => switchNodeTab('software'));
    softwareSearch.addEventListener('input', () => {
        syncSoftwareSearchClear();
        renderSoftwareList();
    });
    softwareSearch.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        if (!softwareSearch.value.trim()) return;
        event.preventDefault();
        softwareSearch.value = '';
        syncSoftwareSearchClear();
        renderSoftwareList();
    });
    softwareSearchClear.addEventListener('click', () => {
        softwareSearch.value = '';
        syncSoftwareSearchClear();
        renderSoftwareList();
        softwareSearch.focus();
    });
    softwareSaveBtn.addEventListener('click', saveSoftware);
    softwareResetBtn.addEventListener('click', resetSoftwareForm);

    syncSoftwareSearchClear();

    document.getElementById('nodeModalCancel').addEventListener('click', () => {
        nodeModal.style.display = 'none';
        switchNodeTab('general');
        resetSoftwareForm();
    });
    nodeModalSave.addEventListener('click', saveNodeModal);

    // ── Mouse drag / pan ────────────────────────────────────────────
    svg.addEventListener('mousedown', (e) => {
        if (dragging !== null) return;
        if (!e.target.closest('[data-node-id]') && !e.target.closest('[data-edge-id]') && !e.target.closest('[data-branch-id]')) {
            if (connectMode) return;
            isPanning = true; panSX = e.clientX - panX; panSY = e.clientY - panY;
            svg.style.cursor = 'grabbing';
        }
    });

    window.addEventListener('mousemove', (e) => {
        if (draggingZoneId !== null) {
            const pt = svgPt(e);
            const zone = zoneMap.get(draggingZoneId);
            if (!zone) return;

            const dx = pt.x - dragZoneStartX;
            const dy = pt.y - dragZoneStartY;
            if (Math.abs(dx) > 1 || Math.abs(dy) > 1) draggingZoneMoved = true;

            zone.x = dragZoneBaseX + dx;
            zone.y = dragZoneBaseY + dy;

            zoneDragNodeStart.forEach((startPos, nodeId) => {
                const node = nodeMap.get(nodeId);
                if (!node) return;
                node.x = startPos.x + dx;
                node.y = startPos.y + dy;
                nodeElMap.get(node.id)?.group.setAttribute('transform', `translate(${node.x},${node.y})`);
                rebuildEdgesForNode(node.id);
            });

            renderZones();
            applyHighlight();
            markLayoutDirty('Campus movido con su contenido...');
        } else if (resizingZoneId !== null) {
            const pt = svgPt(e);
            const zone = zoneMap.get(resizingZoneId);
            if (!zone) return;

            const dx = pt.x - resizeZoneStartX;
            const dy = pt.y - resizeZoneStartY;
            const dir = resizeZoneDirection || 'se';

            let nextX = resizeZoneStartLeft;
            let nextY = resizeZoneStartTop;
            let nextW = resizeZoneStartW;
            let nextH = resizeZoneStartH;

            if (dir.includes('e')) {
                nextW = Math.max(MIN_ZONE_W, resizeZoneStartW + dx);
            }
            if (dir.includes('s')) {
                nextH = Math.max(MIN_ZONE_H, resizeZoneStartH + dy);
            }
            if (dir.includes('w')) {
                const desiredW = resizeZoneStartW - dx;
                if (desiredW >= MIN_ZONE_W) {
                    nextW = desiredW;
                    nextX = resizeZoneStartLeft + dx;
                } else {
                    nextW = MIN_ZONE_W;
                    nextX = resizeZoneStartLeft + (resizeZoneStartW - MIN_ZONE_W);
                }
            }
            if (dir.includes('n')) {
                const desiredH = resizeZoneStartH - dy;
                if (desiredH >= MIN_ZONE_H) {
                    nextH = desiredH;
                    nextY = resizeZoneStartTop + dy;
                } else {
                    nextH = MIN_ZONE_H;
                    nextY = resizeZoneStartTop + (resizeZoneStartH - MIN_ZONE_H);
                }
            }

            zone.x = nextX;
            zone.y = nextY;
            zone.w = nextW;
            zone.h = nextH;
            renderZones();
            applyHighlight();
            markLayoutDirty('Tamaño del campus actualizado...');
        } else if (dragging !== null) {
            const pt = svgPt(e);
            const nd = nodeMap.get(dragging);
            if (!nd) return;
            nd.x = pt.x - dragOX; nd.y = pt.y - dragOY;
            dragMoved = true;
            nodeElMap.get(dragging)?.group.setAttribute('transform', `translate(${nd.x},${nd.y})`);
            rebuildEdgesForNode(dragging);
            markLayoutDirty('Mi layout se actualiza automáticamente...');
        } else if (isPanning) {
            panX = e.clientX - panSX; panY = e.clientY - panSY; applyVP();
        } else if (connectMode && connectSrc) {
            const from = nodeMap.get(connectSrc);
            if (!from) return;
            const pt = svgPt(e);
            tempLine.setAttribute('x1', from.x); tempLine.setAttribute('y1', from.y);
            tempLine.setAttribute('x2', pt.x);   tempLine.setAttribute('y2', pt.y);
            tempLine.setAttribute('opacity', '0.8');
        }
    });

    window.addEventListener('mouseup', () => {
        if (draggingZoneId !== null) {
            if (draggingZoneMoved) {
                suppressZoneClick = true;
                schedulePersistPersonalLayout(true);
            }
            draggingZoneId = null;
            draggingZoneMoved = false;
            zoneDragNodeStart.clear();
        }
        if (resizingZoneId !== null) {
            resizingZoneId = null;
            resizeZoneDirection = null;
            schedulePersistPersonalLayout(true);
        }
        if (dragMoved) schedulePersistPersonalLayout(true);
        dragging = null; dragMoved = false;
        if (isPanning) { isPanning = false; svg.style.cursor = connectMode ? 'crosshair' : 'default'; }
    });

    window.addEventListener('beforeunload', () => {
        schedulePersistPersonalLayout(true);
    });

    svg.addEventListener('click', (e) => {
        if (e.target.closest('[data-node-id]') || e.target.closest('[data-edge-id]')) return;
        if (e.target.closest('[data-branch-id]')) return;
        if (connectMode && connectSrc) { connectSrc = null; clearConnectHL(); tempLine.setAttribute('opacity', '0'); return; }
        selNode = null; selEdge = null; selBranch = null; applyHighlight();
        ic.innerHTML = '<div class="inspector-empty">Haz clic en un nodo o conexión.</div>';
        renderDrilldown();
    });

    // ── Zoom ────────────────────────────────────────────────────────
    svg.addEventListener('wheel', (e) => {
        e.preventDefault();
        zoom = clamp(zoom + (e.deltaY > 0 ? -0.08 : 0.08), 0.15, 3);
        applyVP();
    }, { passive: false });

    // ── Toolbar buttons ─────────────────────────────────────────────
    const setStatus = (msg, cls = '') => {
        const el = document.getElementById('topoStatus');
        el.textContent = msg; el.className = 'topo-status ' + cls;
    };

    const markLayoutDirty = (message = 'Cambios pendientes...') => {
        if (!dirty) {
            dirty = true;
            saveLayoutBtn.disabled = false;
        }

        schedulePersistPersonalLayout();
        setStatus(message);
    };

    const setMode = (mode) => {
        connectMode = mode === 'connect';
        document.getElementById('btnSelect').classList.toggle('active', !connectMode);
        document.getElementById('btnConnect').classList.toggle('active', connectMode);
        svg.style.cursor = connectMode ? 'crosshair' : 'default';
        if (!connectMode) { connectSrc = null; clearConnectHL(); tempLine.setAttribute('opacity', '0'); setStatus('Modo selección.'); }
        else setStatus('Modo conexión — clic en nodo origen, luego en nodo destino.');
    };

    const focusServerNodes = () => {
        const servers = [...nodeMap.values()].filter(isServerNode);
        if (!servers.length) {
            setStatus('No se detectaron servidores en la vista actual.', 'err');
            return;
        }

        const minX = Math.min(...servers.map(n => n.x));
        const maxX = Math.max(...servers.map(n => n.x));
        const minY = Math.min(...servers.map(n => n.y));
        const maxY = Math.max(...servers.map(n => n.y));

        const contentW = Math.max(1, maxX - minX + 220);
        const contentH = Math.max(1, maxY - minY + 220);
        const fitX = W / contentW;
        const fitY = H / contentH;
        zoom = clamp(Math.min(fitX, fitY, 2.2), 0.25, 2.2);
        panX = Math.round((W / 2) - ((minX + maxX) / 2 * zoom));
        panY = Math.round((H / 2) - ((minY + maxY) / 2 * zoom));
        applyVP();

        const firstServer = servers[0];
        selNode = firstServer.id;
        selEdge = null;
        selBranch = firstServer.branch_id;
        applyHighlight();
        renderInspNode(firstServer);
        renderDrilldown(firstServer.branch_id, firstServer.id);
        setStatus(`Servidores enfocados (${servers.length})`, 'ok');
    };

    document.getElementById('btnSelect').addEventListener('click', () => setMode('select'));
    document.getElementById('btnConnect').addEventListener('click', () => setMode('connect'));
    document.getElementById('btnAddNode').addEventListener('click', () => openNodeModalCreate(selBranch));
    document.getElementById('btnFocusServers').addEventListener('click', focusServerNodes);

    const traceSourceSelect = document.getElementById('traceSourceSelect');
    const traceTargetSelect = document.getElementById('traceTargetSelect');
    const traceSummary = document.getElementById('traceSummary');
    const btnRunTrace = document.getElementById('btnRunTrace');
    const btnClearTrace = document.getElementById('btnClearTrace');

    const layerButtons = {
        all: document.getElementById('btnLayerAll'),
        ap: document.getElementById('btnLayerAP'),
        servers: document.getElementById('btnLayerServers'),
        software: document.getElementById('btnLayerSoftware'),
        critical_links: document.getElementById('btnLayerCriticalLinks'),
    };

    const setLayer = (layer) => {
        activeLayer = layer;
        Object.entries(layerButtons).forEach(([key, button]) => {
            if (!button) return;
            button.classList.toggle('active', key === layer);
        });
        applyHighlight();
        const messageByLayer = {
            all: 'Capas: vista completa.',
            ap: 'Capas: resaltando Access Point.',
            servers: 'Capas: resaltando servidores.',
            software: 'Capas: resaltando nodos con software.',
            critical_links: 'Capas: resaltando enlaces críticos.',
        };
        setStatus(messageByLayer[layer] || 'Capas actualizadas.');
    };

    const buildTraceOptions = () => {
        const sortedNodes = [...nodeMap.values()].sort((a, b) => `${a.branch_name || ''} ${a.label || ''}`.localeCompare(`${b.branch_name || ''} ${b.label || ''}`));

        if (traceSourceSelect) {
            traceSourceSelect.innerHTML = ['<option value="">Selecciona nodo origen...</option>']
                .concat(sortedNodes.map((node) => `<option value="node:${node.id}">${node.branch_name || 'Sede'} · ${node.label}</option>`))
                .join('');
        }

        if (traceTargetSelect) {
            const options = ['<option value="">Selecciona destino...</option>'];
            sortedNodes.forEach((node) => {
                options.push(`<option value="node:${node.id}">Nodo · ${node.branch_name || 'Sede'} · ${node.label}</option>`);
                (Array.isArray(node.software_systems) ? node.software_systems : []).forEach((software) => {
                    options.push(`<option value="software:${node.id}:${software.id}">Software · ${software.name}${software.version ? ` ${software.version}` : ''} · ${node.label}</option>`);
                });
            });
            traceTargetSelect.innerHTML = options.join('');
        }
    };

    const resolveTraceTargetNodeId = (value) => {
        const raw = String(value || '');
        if (!raw) return null;
        const parts = raw.split(':');
        if (parts[0] === 'node') return Number(parts[1] || 0) || null;
        if (parts[0] === 'software') return Number(parts[1] || 0) || null;
        return null;
    };

    const traceEdgeWeight = (edge) => {
        if (edge?.preferred_weight != null && Number(edge.preferred_weight) > 0) {
            return Number(edge.preferred_weight);
        }
        const label = String(edge?.label || '').toLowerCase();
        if (label.includes('fiber')) return 1;
        if (label.includes('linked')) return 2;
        if (label.includes('wire') || label.includes('wifi') || label.includes('inalambr')) return 3;
        if (label.includes('wan')) return 6;
        if (label.includes('vpn')) return 7;
        if (label.includes('inter-campus')) return 8;
        return 4;
    };

    const weightedShortestPath = (fromId, toId) => {
        if (!fromId || !toId) return null;
        if (fromId === toId) return { nodeIds: [fromId], edgeIds: [], totalWeight: 0 };

        const adjacency = new Map();
        nodeMap.forEach((_, nodeId) => adjacency.set(Number(nodeId), []));
        edgeMap.forEach((edge, edgeId) => {
            const from = Number(edge.from);
            const to = Number(edge.to);
            if (!adjacency.has(from)) adjacency.set(from, []);
            if (!adjacency.has(to)) adjacency.set(to, []);
            const weight = traceEdgeWeight(edge);
            adjacency.get(from).push({ next: to, edgeId: Number(edgeId), weight });
            adjacency.get(to).push({ next: from, edgeId: Number(edgeId), weight });
        });

        const distances = new Map();
        const prev = new Map();
        const unvisited = new Set(adjacency.keys());

        adjacency.forEach((_, nodeId) => distances.set(nodeId, Number.POSITIVE_INFINITY));
        distances.set(fromId, 0);

        while (unvisited.size) {
            let current = null;
            let currentDistance = Number.POSITIVE_INFINITY;

            unvisited.forEach((nodeId) => {
                const distance = distances.get(nodeId) ?? Number.POSITIVE_INFINITY;
                if (distance < currentDistance) {
                    currentDistance = distance;
                    current = nodeId;
                }
            });

            if (current === null || currentDistance === Number.POSITIVE_INFINITY) break;
            unvisited.delete(current);
            if (current === toId) break;

            for (const neighbor of (adjacency.get(current) || [])) {
                if (!unvisited.has(neighbor.next)) continue;
                const candidateDistance = currentDistance + neighbor.weight;
                if (candidateDistance < (distances.get(neighbor.next) ?? Number.POSITIVE_INFINITY)) {
                    distances.set(neighbor.next, candidateDistance);
                    prev.set(neighbor.next, {
                        nodeId: current,
                        edgeId: neighbor.edgeId,
                        weight: neighbor.weight,
                    });
                }
            }
        }

        if (!prev.has(toId)) return null;

        const nodeIds = [toId];
        const edgeIds = [];
        let totalWeight = 0;
        let cursor = toId;
        while (prev.has(cursor)) {
            const step = prev.get(cursor);
            edgeIds.push(step.edgeId);
            nodeIds.push(step.nodeId);
            totalWeight += Number(step.weight || 0);
            cursor = step.nodeId;
        }

        nodeIds.reverse();
        edgeIds.reverse();
        return { nodeIds, edgeIds, totalWeight };
    };

    const clearTrace = () => {
        activeTrace = null;
        if (traceSummary) traceSummary.textContent = 'Selecciona origen y destino para resaltar la ruta.';
        applyHighlight();
    };

    const runTrace = () => {
        const sourceId = resolveTraceTargetNodeId(traceSourceSelect?.value || '');
        const targetId = resolveTraceTargetNodeId(traceTargetSelect?.value || '');

        if (!sourceId || !targetId) {
            setStatus('Selecciona origen y destino para trazar la ruta.', 'err');
            return;
        }

        const path = weightedShortestPath(sourceId, targetId);
        if (!path) {
            clearTrace();
            if (traceSummary) traceSummary.textContent = 'No se encontró camino entre los elementos seleccionados.';
            setStatus('No existe una ruta entre origen y destino en la topología actual.', 'err');
            return;
        }

        activeTrace = {
            nodeIds: new Set(path.nodeIds.map(Number)),
            edgeIds: new Set(path.edgeIds.map(Number)),
        };

        const sourceNode = nodeMap.get(sourceId);
        const targetNode = nodeMap.get(targetId);
        selNode = targetId;
        selEdge = null;
        selBranch = targetNode?.branch_id || sourceNode?.branch_id || null;
        applyHighlight();
        if (targetNode) {
            renderInspNode(targetNode);
            renderDrilldown(targetNode.branch_id, targetNode.id);
        }

        if (traceSummary) {
            const hopDetails = path.edgeIds.map((edgeId, index) => {
                const edge = edgeMap.get(edgeId);
                const fromNode = nodeMap.get(path.nodeIds[index]);
                const toNode = nodeMap.get(path.nodeIds[index + 1]);
                return `<div>${index + 1}. ${fromNode?.label || '?'} → ${toNode?.label || '?'} <span style="color:#64748b">(${EDGE_DISPLAY_LABEL(edge)})</span></div>`;
            }).join('');
            traceSummary.innerHTML = `
                <div>Ruta encontrada: <strong>${path.nodeIds.length}</strong> nodos / <strong>${path.edgeIds.length}</strong> saltos / costo <strong>${path.totalWeight}</strong></div>
                <div style="margin-top:6px; font-size:.72rem; color:#94a3b8;">${hopDetails || 'Origen y destino son el mismo nodo.'}</div>
            `;
        }
        setStatus(`Ruta resaltada: ${sourceNode?.label || sourceId} → ${targetNode?.label || targetId}`, 'ok');
    };

    window._traceFromNode = (nodeId) => {
        if (traceSourceSelect) traceSourceSelect.value = `node:${nodeId}`;
        setStatus('Nodo seleccionado como origen para traceroute visual. Elige destino y pulsa Trazar.');
    };

    layerButtons.all?.addEventListener('click', () => setLayer('all'));
    layerButtons.ap?.addEventListener('click', () => setLayer('ap'));
    layerButtons.servers?.addEventListener('click', () => setLayer('servers'));
    layerButtons.software?.addEventListener('click', () => setLayer('software'));
    layerButtons.critical_links?.addEventListener('click', () => setLayer('critical_links'));
    btnRunTrace?.addEventListener('click', runTrace);
    btnClearTrace?.addEventListener('click', clearTrace);

    document.getElementById('btnZoomIn').addEventListener('click', () => { zoom = clamp(zoom + 0.15, 0.15, 3); applyVP(); });
    document.getElementById('btnZoomOut').addEventListener('click', () => { zoom = clamp(zoom - 0.15, 0.15, 3); applyVP(); });
    document.getElementById('btnResetView').addEventListener('click', () => { panX = 0; panY = 0; zoom = 1; applyVP(); });

    document.getElementById('btnAutoLayout').addEventListener('click', () => {
        const bIdx = {};
        nodeMap.forEach(nd => {
            const zone = zoneMap.get(nd.branch_id);
            bIdx[nd.branch_id] = (bIdx[nd.branch_id] ?? -1) + 1;
            const idx = bIdx[nd.branch_id];
            const bNodes = [...nodeMap.values()].filter(n => n.branch_id === nd.branch_id).length;
            const margin = ZONE_MARGIN;
            const usableW = Math.max(120, (zone?.w ?? ZONE_BASE_W) - margin * 2);
            const usableH = Math.max(120, (zone?.h ?? ZONE_BASE_H) - margin * 2 - ZONE_TITLE_OFFSET);
            const cols = Math.max(1, Math.ceil(Math.sqrt(bNodes)));
            const cellW = usableW / cols, cellH = usableH / Math.max(1, Math.ceil(bNodes / cols));
            const c = idx % cols, r = Math.floor(idx / cols);
            nd.x = zone ? zone.x + margin + c * cellW + cellW / 2 : 100 + idx * 120;
            nd.y = zone ? zone.y + ZONE_TITLE_OFFSET + margin + r * cellH + cellH / 2 : 100;
            nodeElMap.get(nd.id)?.group.setAttribute('transform', `translate(${nd.x},${nd.y})`);
        });
        edgeMap.forEach(e => updateEdgePos(e.id));
        markLayoutDirty('Auto-layout aplicado. Tu vista personal quedó actualizada.');
        schedulePersistPersonalLayout(true);
    });

    document.getElementById('btnSaveLayout').addEventListener('click', async () => {
        const payload = [...nodeMap.values()].map(n => ({ id: n.id, x: n.x, y: n.y }));
        try {
            const r = await fetch(saveLayoutUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ nodes: payload }),
            });
            const data = await r.json();
            if (!data.ok) throw new Error('Error al guardar');
            schedulePersistPersonalLayout(true);
            dirty = false; saveLayoutBtn.disabled = true;
            setStatus('Layout global guardado; tu vista personal quedó conservada ✓', 'ok');
        } catch (e) { setStatus('Error: ' + e.message, 'err'); }
    });

    document.getElementById('btnExportPng').addEventListener('click', () => {
        const clone = svg.cloneNode(true);
        clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        clone.setAttribute('width', W); clone.setAttribute('height', H);
        const blob = new Blob([new XMLSerializer().serializeToString(clone)], { type: 'image/svg+xml;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const img = new Image();
        img.onload = () => {
            const c = document.createElement('canvas'); c.width = W; c.height = H;
            const ctx = c.getContext('2d'); ctx.fillStyle = '#0f172a'; ctx.fillRect(0, 0, W, H);
            ctx.drawImage(img, 0, 0);
            const a = document.createElement('a'); a.href = c.toDataURL('image/png');
            a.download = 'itcity-topologia.png'; a.click(); URL.revokeObjectURL(url);
        };
        img.src = url;
    });

    // ── Keyboard shortcuts ──────────────────────────────────────────
    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        if (e.key === 'v' || e.key === 'V') setMode('select');
        if (e.key === 'c' || e.key === 'C') setMode('connect');
        if (e.key === 'Escape') {
            edgeModal.style.display = 'none'; pendingEdge = null;
            connectSrc = null; clearConnectHL(); tempLine.setAttribute('opacity', '0');
            setStatus('Cancelado.', '');
        }
        if ((e.key === 'Delete' || e.key === 'Backspace') && selEdge) {
            window._delEdge(selEdge);
        }
    });

    // ── Legend ───────────────────────────────────────────────────────
    const buildLegend = () => {
        const types = {};
        nodeMap.forEach(n => { types[n.type] = (types[n.type] || 0) + 1; });
        document.getElementById('legendTypes').innerHTML = Object.entries(types).map(([t, cnt]) => {
            const tc = TYPE_COLOR(t);
            return `<div class="legend-item"><span class="legend-dot" style="background:${tc.fill}"></span>${t}<span class="legend-cnt">${cnt}</span></div>`;
        }).join('');
    };

    // ── Initial render ───────────────────────────────────────────────
    renderZones();
    edgeMap.forEach(e => buildEdge(e));
    nodeMap.forEach(n => buildNode(n));
    buildLegend();
    buildTraceOptions();
    setLayer('all');
    applyVP();
})();
</script>
</body>
</html>
