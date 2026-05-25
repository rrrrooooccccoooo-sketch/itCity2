<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Red de {{ $branch->name }} | ITCity</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; height: 100%; background: #0f172a; font-family: system-ui, sans-serif; }

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
        .topo-btn.primary { background: #2563eb; color: #fff; border-color: #2563eb; }
        .topo-btn.primary:disabled { opacity: .45; cursor: default; }
        .topo-status { font-size: .75rem; color: #64748b; margin-left: auto; white-space: nowrap; }
        .topo-status.ok  { color: #4ade80; }
        .topo-status.err { color: #f87171; }

        .topo-layout { display: flex; height: calc(100vh - 52px); overflow: hidden; }
        .topo-panel {
            width: 245px; flex-shrink: 0; background: #1e293b; border-right: 1px solid #334155;
            overflow-y: auto; display: flex; flex-direction: column;
        }
        .topo-panel-section { padding: 12px 14px; border-bottom: 1px solid #334155; }
        .topo-panel-title { font-size: .67rem; text-transform: uppercase; letter-spacing: .09em; color: #475569; font-weight: 700; margin-bottom: 10px; }

        .topo-canvas-wrap { flex: 1; overflow: hidden; position: relative; background: #0f172a; }
        #networkWrapper { width: 100%; height: 100%; display: flex; flex-direction: column; }
        #networkCanvas { width: 100%; height: 100%; display: block; background: #0f172a; }

        .node-chip {
            border-radius: 999px;
            padding: 4px 10px;
            font-size: .73rem;
            background: #0f172a;
            color: #93c5fd;
            border: 1px solid #334155;
            display: inline-flex;
            gap: 8px;
            align-items: center;
        }

        .legend-edge { display: flex; align-items: center; gap: 7px; font-size: .72rem; color: #94a3b8; margin-bottom: 5px; }
        .edge-sample { display: inline-block; width: 28px; height: 3px; border-radius: 2px; flex-shrink: 0; }
        .edge-sample.gray    { background: #94a3b8; }
        .edge-sample.blue    { background: #2563eb; }
        .edge-sample.purple  { background: #9333ea; border-top: 3px dashed #9333ea; height: 0; }
        .edge-sample.orange  { background: transparent; border-top: 3px dashed #f97316; height: 0; }
        .edge-sample.teal    { background: transparent; border-top: 3px dotted #0891b2; height: 0; }
        .edge-sample.green   { background: transparent; border-top: 3px dashed #16a34a; height: 0; }

        .edge-flow { stroke-dasharray: 7 6; animation: edgeFlow 2.1s linear infinite; }
        @keyframes edgeFlow { from { stroke-dashoffset: 0; } to { stroke-dashoffset: -52; } }

        .ni-title { font-size: .64rem; text-transform: uppercase; letter-spacing: .08em; color: #475569; margin-bottom: 2px; }
        .ni-name { font-size: .92rem; font-weight: 700; color: #f1f5f9; margin-bottom: 6px; }
        .ni-meta { color: #94a3b8; font-size: .75rem; margin-bottom: 3px; }
        .ni-links { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .ni-pill {
            border-radius: 999px;
            padding: 2px 8px;
            font-size: .68rem;
            background: #0f172a;
            color: #94a3b8;
            border: 1px solid #334155;
            font-weight: 600;
        }
        .ni-edge-list { margin-top: 10px; display: flex; flex-direction: column; gap: 6px; max-height: 180px; overflow: auto; }
        .ni-edge-item {
            border: 1px solid #334155;
            border-radius: 8px;
            background: #111827;
            padding: 7px 8px;
            cursor: pointer;
            transition: border-color .12s ease, background .12s ease;
        }
        .ni-edge-item:hover {
            border-color: #3b82f6;
            background: #0f172a;
        }
        .ni-edge-item.active {
            border-color: #60a5fa;
            box-shadow: 0 0 0 1px rgba(96, 165, 250, .25) inset;
        }
        .ni-edge-main { font-size: .72rem; color: #e2e8f0; font-weight: 600; }
        .ni-edge-meta { font-size: .68rem; color: #94a3b8; margin-top: 2px; }
        .ni-edge-actions { margin-top: 6px; display: flex; justify-content: flex-end; }
        .ni-edge-actions .topo-btn { font-size: .66rem; padding: 2px 7px; }

        #nodeInspector {
            min-height: 170px;
            border: 1px solid #334155;
            border-radius: 10px;
            background: #0f172a;
            padding: 12px;
        }

        .topo-field-label { display:block; font-size:.68rem; color:#64748b; margin-bottom:4px; }
        .topo-field-select {
            width:100%; border:1px solid #334155; border-radius:8px; background:#0f172a; color:#cbd5e1;
            padding:6px 8px; font-size:.76rem;
        }
        .topo-check { display: flex; align-items: center; gap: 7px; font-size: .72rem; color: #94a3b8; margin-top: 8px; }

        .kbd { background: #0f172a; border: 1px solid #334155; border-radius: 4px; padding: 1px 5px; font-family: monospace; font-size: .7rem; color: #64748b; }

        #presentationBar {
            display: none;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-bottom: 1px solid #334155;
            background: #1e293b;
        }
        #presentationBar .ps-title {
            color: #94a3b8;
            font-size: .85rem;
            font-weight: 600;
            letter-spacing: .05em;
        }
        #presentationBar .ps-countdown {
            background: #0f172a;
            color: #38bdf8;
            border-radius: 8px;
            border: 1px solid #334155;
            padding: 4px 12px;
            font-size: .8rem;
            font-family: monospace;
        }

        #networkWrapper:fullscreen,
        #networkWrapper:-webkit-full-screen {
            background: #080d18;
            display: flex;
            flex-direction: column;
            padding: 0;
        }
        #networkWrapper:fullscreen #networkCanvas,
        #networkWrapper:-webkit-full-screen #networkCanvas {
            flex: 1;
            height: auto;
            box-shadow: 0 0 60px rgba(37,99,235,.35);
        }
        #networkWrapper:fullscreen .fs-hide,
        #networkWrapper:-webkit-full-screen .fs-hide {
            display: none !important;
        }
        #networkWrapper:fullscreen .fs-bar,
        #networkWrapper:-webkit-full-screen .fs-bar {
            display: flex !important;
        }
        #btnPresentation.active {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }
    </style>
</head>
<body>
<header class="topo-toolbar fs-hide">
    <div class="topo-brand">
        <a href="{{ url('/') }}">🏙 ITCity</a>
        <span style="margin: 0 4px; color:#475569">/</span>
        <span>Topología de sede · {{ $branch->name }}</span>
    </div>

    <button id="btnAutoLayout" class="topo-btn">⊞ Auto-layout</button>
    <button id="btnZoomIn" class="topo-btn">＋</button>
    <button id="btnZoomOut" class="topo-btn">－</button>
    <button id="btnResetView" class="topo-btn">⌂</button>

    <span class="topo-sep">|</span>

    <button id="btnPresentation" class="topo-btn">🎥 Presentación</button>
    <button id="btnSaveLayout" class="topo-btn primary" disabled>💾 Guardar layout</button>
    <button id="btnExportPng" class="topo-btn">📷 PNG</button>
    <button id="btnExportPdf" class="topo-btn">🧾 PDF</button>

    <span class="topo-sep">|</span>

    <a href="{{ url('/admin') }}" class="topo-btn">⚙ Admin</a>
    <a href="{{ url('/sede/' . $branch->id) }}" class="topo-btn">← Sede</a>
    <a href="{{ url('/') }}" class="topo-btn">← Ciudad</a>

    <span id="layoutStatus" class="topo-status">
        <span class="kbd">Arrastra</span> nodos para ajustar &nbsp;
        <span class="kbd">Doble clic</span> abrir ficha
    </span>
</header>

<div class="topo-layout fs-hide">
    <aside class="topo-panel">
        <div class="topo-panel-section">
            <div class="topo-panel-title">Resumen</div>
            <div class="d-flex flex-wrap gap-2">
                <span class="node-chip">Nodos: {{ $graphNodes->count() }}</span>
                <span class="node-chip">Conexiones: {{ $graphEdges->count() }}</span>
            </div>
        </div>

        <div class="topo-panel-section">
            <div class="topo-panel-title">Filtros</div>
            <label class="topo-field-label" for="filterType">Tipo de nodo</label>
            <select id="filterType" class="topo-field-select">
                <option value="">Todos</option>
            </select>

            <label class="topo-field-label mt-2" for="filterStatus">Estado</label>
            <select id="filterStatus" class="topo-field-select">
                <option value="">Todos</option>
                <option value="active">Activo</option>
                <option value="inactive">Inactivo</option>
                <option value="warning">Warning</option>
                <option value="error">Error</option>
            </select>

            <label class="topo-check">
                <input class="form-check-input" type="checkbox" id="toggleEdgeLabels" checked>
                Mostrar etiquetas de enlaces
            </label>
        </div>

        <div class="topo-panel-section">
            <div class="topo-panel-title">Leyenda por tipo</div>
            <div id="legendByType" class="d-flex flex-wrap gap-2"></div>
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
            <div class="topo-panel-title">Leyenda por estado</div>
            <div id="legendByStatus" class="d-flex flex-wrap gap-2"></div>
        </div>

        <div class="topo-panel-section" style="border-bottom:none;">
            <div class="topo-panel-title">Inspector</div>
            <div id="nodeInspector">
                <div class="ni-title">Inspector de nodo</div>
                <div class="ni-meta">Selecciona un nodo en el mapa para ver conexiones y acciones rápidas.</div>
            </div>
        </div>
    </aside>

    <div class="topo-canvas-wrap">
        <div id="networkWrapper">
            <div id="presentationBar" class="fs-bar">
                <span class="ps-title">ITCity — {{ $branch->name }} — Modo Presentación</span>
                <span id="psCountdown" class="ps-countdown">Actualizando...</span>
                <button id="btnExitPresentation" class="topo-btn ms-auto">Salir</button>
            </div>
            <svg id="networkCanvas" viewBox="0 0 1200 620" preserveAspectRatio="xMidYMid meet"></svg>
        </div>
    </div>
</div>

<script>
    (function () {
        const nodes = @json($graphNodes);
        const edges = @json($graphEdges);
        const branchId = @json($branch->id);
        const saveLayoutUrl = @json(url('/sede/' . $branch->id . '/red/layout'));
        const statusUrl     = @json(url('/sede/' . $branch->id . '/estado'));
        const csrfToken = @json(csrf_token());
        const nodeDeleteUrlBase = @json(url('/red/nodos'));
        const edgeDeleteUrlBase = @json(url('/red/relacion'));

        const localLayoutKey = `itcity.network.layout.v2:${location.host}:branch:${branchId}`;

        const POLL_INTERVAL = 15; // seconds between status refreshes

        const svg = document.getElementById('networkCanvas');
        const width = 1200;
        const height = 620;
        const AUTO_LAYOUT_MARGIN = 8;
        const MIN_ZOOM = 0.35;
        const MAX_ZOOM = 2.4;
        const centerX = width / 2;
        const centerY = height / 2;
        const radius = Math.min(width, height) * 0.34;

        const nodeMap = new Map();
        const edgeElements = [];
        const edgeLabelElements = [];
        const nodeElements = new Map();
        let selectedNodeId = null;
        let selectedEdgeId = null;
        let draggingNodeId = null;
        let nodeDragMoved = false;
        let dragOffset = { x: 0, y: 0 };
        let isPanning = false;
        let panStart = { x: 0, y: 0 };
        let panX = 0;
        let panY = 0;
        let zoom = 1;
        let autoSaveTimer = null;
        let isSavingLayout = false;
        let hasPendingSave = false;

        const autoLayoutButton = document.getElementById('btnAutoLayout');
        const zoomInButton = document.getElementById('btnZoomIn');
        const zoomOutButton = document.getElementById('btnZoomOut');
        const resetViewButton = document.getElementById('btnResetView');
        const saveButton = document.getElementById('btnSaveLayout');
        const exportPngButton = document.getElementById('btnExportPng');
        const exportPdfButton = document.getElementById('btnExportPdf');
        const statusLabel = document.getElementById('layoutStatus');
        const filterType = document.getElementById('filterType');
        const filterStatus = document.getElementById('filterStatus');
        const toggleEdgeLabels = document.getElementById('toggleEdgeLabels');
        const legendByType = document.getElementById('legendByType');
        const legendByStatus = document.getElementById('legendByStatus');
        const nodeInspector = document.getElementById('nodeInspector');
        const btnPresentation = document.getElementById('btnPresentation');
        const btnExitPresentation = document.getElementById('btnExitPresentation');
        const psCountdown = document.getElementById('psCountdown');
        const networkWrapper = document.getElementById('networkWrapper');

        const setStatus = (message, tone = '') => {
            if (!statusLabel) return;
            statusLabel.textContent = message;
            statusLabel.className = `topo-status${tone ? ' ' + tone : ''}`;
        };

        const buildLayoutPayload = () => ({
            nodes: Array.from(nodeMap.values()).map(node => ({
                id: node.id,
                x: Number(node.x.toFixed(2)),
                y: Number(node.y.toFixed(2)),
            })),
        });

        const persistLayout = async ({ silent = false, manual = false } = {}) => {
            if (isSavingLayout || !hasPendingSave) return;

            const payload = buildLayoutPayload();
            isSavingLayout = true;
            if (saveButton) saveButton.disabled = true;
            if (!silent) setStatus('Guardando layout...');

            try {
                const response = await fetch(saveLayoutUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok) throw new Error(`Error ${response.status}`);

                hasPendingSave = false;
                localStorage.setItem(localLayoutKey, JSON.stringify({
                    saved_at: Date.now(),
                    nodes: payload.nodes,
                }));

                if (!silent) {
                    setStatus('Layout guardado correctamente.', 'ok');
                    if (manual) {
                        window.itcityAlert({
                            icon: 'success',
                            title: 'Layout guardado',
                            text: 'El diseño de la sede se guardó correctamente.',
                            toast: true,
                            position: 'top-end',
                        });
                    }
                }
            } catch (error) {
                if (!silent) {
                    setStatus('No se pudo guardar el layout.', 'err');
                    if (manual) {
                        window.itcityAlert({
                            icon: 'error',
                            title: 'Error al guardar layout',
                            text: 'No fue posible guardar el diseño en este momento.',
                            toast: true,
                            position: 'top-end',
                        });
                    }
                }
                if (saveButton) saveButton.disabled = false;
            } finally {
                isSavingLayout = false;
                if (!hasPendingSave && saveButton) saveButton.disabled = true;
            }
        };

        const scheduleAutoSave = () => {
            if (autoSaveTimer) {
                clearTimeout(autoSaveTimer);
            }
            autoSaveTimer = setTimeout(() => {
                persistLayout({ silent: true });
            }, 1100);
        };

        const removeEdgeFromCanvas = (edgeId) => {
            const edgeIndex = edgeElements.findIndex((item) => Number(item.edge.id) === Number(edgeId));
            if (edgeIndex >= 0) {
                const [removed] = edgeElements.splice(edgeIndex, 1);
                removed?.line?.remove();
            }

            const labelIndex = edgeLabelElements.findIndex((item) => Number(item.edge.id) === Number(edgeId));
            if (labelIndex >= 0) {
                const [removedLabel] = edgeLabelElements.splice(labelIndex, 1);
                removedLabel?.label?.remove();
                removedLabel?.labelBg?.remove();
            }

            if (Number(selectedEdgeId) === Number(edgeId)) {
                selectedEdgeId = null;
            }
        };

        const removeNodeFromCanvas = (nodeId) => {
            const connectedIds = edgeElements
                .filter(({ edge }) => Number(edge.from) === Number(nodeId) || Number(edge.to) === Number(nodeId))
                .map(({ edge }) => edge.id);

            connectedIds.forEach((edgeId) => removeEdgeFromCanvas(edgeId));

            const nodeEl = nodeElements.get(nodeId);
            nodeEl?.group?.remove();
            nodeElements.delete(nodeId);
            nodeMap.delete(nodeId);

            if (Number(selectedNodeId) === Number(nodeId)) selectedNodeId = null;
            if (Number(selectedEdgeId) === Number(nodeId)) selectedEdgeId = null;

            renderLegend();
            applyFilters();
        };

        // ── Status colours ──
        const nodeColor = (status) => {
            switch ((status || '').toLowerCase()) {
                case 'active':   return '#22c55e';
                case 'warning':  return '#f59e0b';
                case 'error':    return '#ef4444';
                case 'inactive': return '#64748b';
                default:         return '#2563eb';
            }
        };

        const create = (tag, attrs) => {
            const element = document.createElementNS('http://www.w3.org/2000/svg', tag);
            Object.entries(attrs).forEach(([key, value]) => element.setAttribute(key, value));
            return element;
        };

        const strokeByType = (typeName) => {
            const value = String(typeName || '').toLowerCase();
            if (value.includes('router')) return '#f59e0b';
            if (value.includes('switch')) return '#8b5cf6';
            if (value.includes('firewall')) return '#ef4444';
            if (value.includes('database') || value.includes('db') || value.includes('sql')) return '#14b8a6';
            if (value.includes('load-balancer') || value.includes('balancer')) return '#0ea5e9';
            if (value.includes('pbx') || value.includes('telefon')) return '#10b981';
            if (value.includes('camera')) return '#64748b';
            if (value.includes('printer') || value.includes('print')) return '#334155';
            if (value.includes('server')) return '#14b8a6';
            return '#ffffff';
        };

        const EDGE_STYLE = (relType) => {
            const t = (relType || '').toLowerCase();
            if (t.includes('vpn'))    return { stroke: '#9333ea', dash: '9 5', w: 2.5, m: 'url(#arr-vpn)', anim: false };
            if (t.includes('wan'))    return { stroke: '#f97316', dash: '11 5', w: 2, m: 'url(#arr-wan)', anim: false };
            if (t.includes('fiber'))  return { stroke: '#2563eb', dash: null, w: 2.5, m: 'url(#arr-fiber)', anim: true };
            if (t.includes('wire') || t.includes('wifi') || t.includes('inalambr'))
                                      return { stroke: '#0891b2', dash: '4 4', w: 2, m: 'url(#arr-teal)', anim: false };
            if (t.includes('inter') || t.includes('campus'))
                                      return { stroke: '#16a34a', dash: '13 5', w: 3, m: 'url(#arr-green)', anim: false };
            return { stroke: '#94a3b8', dash: null, w: 2, m: 'url(#arr)', anim: true };
        };

        const EDGE_DISPLAY_LABEL = (edge) => {
            const raw = String(edge?.label || 'linked_to');
            const t = raw.toLowerCase();

            if (t.includes('vpn')) return 'VPN';
            if (t.includes('wan')) return 'WAN / Internet';
            if (t.includes('fiber')) return 'Fibra óptica';
            if (t.includes('wire') || t.includes('wifi') || t.includes('inalambr')) return 'Inalámbrico';
            if (t.includes('inter') || t.includes('campus')) return 'Inter-campus';
            if (t.includes('linked') || t.includes('direct')) return 'Enlace directo';

            return raw;
        };

        const renderEdgeInspector = (edgeId) => {
            if (!nodeInspector) return;
            const record = edgeElements.find(({ edge }) => Number(edge.id) === Number(edgeId));
            if (!record) {
                renderInspector(selectedNodeId);
                return;
            }

            const edge = record.edge;
            const fromNode = nodeMap.get(edge.from);
            const toNode = nodeMap.get(edge.to);
            const style = EDGE_STYLE(edge.label);

            nodeInspector.innerHTML = `
                <div class="ni-title">Inspector de enlace</div>
                <div class="ni-name" style="color:${style.stroke}">${EDGE_DISPLAY_LABEL(edge)}</div>
                <div class="ni-meta">Origen: ${fromNode?.label || edge.from}</div>
                <div class="ni-meta">Destino: ${toNode?.label || edge.to}</div>
                <div class="ni-meta">Tipo: ${edge.label || 'N/A'}</div>
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete-edge" data-edge-id="${edge.id}">Eliminar conexión</button>
                    ${fromNode?.detail_url ? `<a class="btn btn-sm btn-outline-primary" href="${fromNode.detail_url}">Ver origen</a>` : ''}
                    ${toNode?.detail_url ? `<a class="btn btn-sm btn-dark" href="${toNode.detail_url}">Ver destino</a>` : ''}
                </div>
            `;
        };

        const renderInspector = (nodeId) => {
            if (!nodeInspector) return;
            if (!nodeId || !nodeMap.has(nodeId)) {
                nodeInspector.innerHTML = `
                    <div class="ni-title">Inspector de nodo</div>
                    <div class="text-muted small mb-0">Selecciona un nodo en el mapa para ver conexiones y acciones rápidas.</div>
                `;
                return;
            }

            const node = nodeMap.get(nodeId);
            const incoming = edgeElements.filter(({ edge }) => edge.to === nodeId).length;
            const outgoing = edgeElements.filter(({ edge }) => edge.from === nodeId).length;
            const connected = edgeElements
                .filter(({ edge }) => edge.to === nodeId || edge.from === nodeId)
                .map(({ edge }) => {
                    const isOutgoing = edge.from === nodeId;
                    const peerId = isOutgoing ? edge.to : edge.from;
                    const peerNode = nodeMap.get(peerId);
                    const direction = isOutgoing ? '→ Saliente' : '← Entrante';
                    return {
                        id: edge.id,
                        direction,
                        relation: EDGE_DISPLAY_LABEL(edge),
                        peerName: peerNode?.label || `Nodo #${peerId}`,
                        peerType: peerNode?.type || 'N/A',
                    };
                });

            const connectedHtml = connected.length
                ? `<div class="ni-edge-list">${connected.map((item) => `
                    <div class="ni-edge-item ${selectedEdgeId === item.id ? 'active' : ''}" data-edge-id="${item.id}">
                        <div class="ni-edge-main">${item.direction} · ${item.relation}</div>
                        <div class="ni-edge-meta">Conectado con: ${item.peerName} (${item.peerType})</div>
                        <div class="ni-edge-actions">
                            <button type="button" class="topo-btn" data-action="delete-edge" data-edge-id="${item.id}">Eliminar enlace</button>
                        </div>
                    </div>
                `).join('')}</div>`
                : '<div class="ni-meta" style="margin-top:8px">Sin enlaces conectados.</div>';

            nodeInspector.innerHTML = `
                <div class="ni-title">Inspector de nodo</div>
                <div class="ni-name">${node.label}</div>
                <div class="ni-meta">Tipo: ${node.type || 'N/A'} · Estado: ${node.status || 'N/A'}</div>
                <div class="ni-meta">IP: ${node.ip || 'N/A'}</div>
                <div class="ni-links">
                    <span class="ni-pill">Entrantes: ${incoming}</span>
                    <span class="ni-pill">Salientes: ${outgoing}</span>
                </div>
                ${connectedHtml}
                <div class="d-flex gap-2 mt-3">
                    <a class="btn btn-sm btn-outline-primary" href="${node.detail_url}">Ver ficha</a>
                    <a class="btn btn-sm btn-dark" href="/admin?edit_node=${node.id}">Configurar nodo</a>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete-node" data-node-id="${node.id}">Eliminar nodo</button>
                </div>
            `;
        };

        const applySelection = () => {
            nodeElements.forEach((value, nodeId) => {
                const edgeLinked = selectedEdgeId
                    ? edgeElements.some(({ edge }) => edge.id === selectedEdgeId && (edge.from === nodeId || edge.to === nodeId))
                    : false;
                const selected = selectedNodeId === nodeId;
                value.circle.setAttribute('stroke-width', selected ? '5' : (edgeLinked ? '4' : '3'));
                value.circle.setAttribute('stroke', selected ? '#f8fafc' : strokeByType(nodeMap.get(nodeId)?.type));
            });

            edgeElements.forEach(({ edge, line, style }) => {
                const highlighted = selectedEdgeId && edge.id === selectedEdgeId;
                const active = selectedNodeId && (edge.from === selectedNodeId || edge.to === selectedNodeId);

                if (highlighted) {
                    line.style.opacity = '1';
                    line.style.strokeWidth = String((style?.w || 2) + 1.3);
                    return;
                }

                if (selectedEdgeId) {
                    line.style.opacity = '.12';
                    line.style.strokeWidth = String(style?.w || 2);
                    return;
                }

                line.style.opacity = active ? '1' : '.35';
                line.style.strokeWidth = active ? String(Math.max(style?.w || 2, 3.2)) : String(style?.w || 2);
            });

            edgeLabelElements.forEach(({ edge, label, labelBg }) => {
                const highlighted = selectedEdgeId && edge.id === selectedEdgeId;
                const active = selectedNodeId && (edge.from === selectedNodeId || edge.to === selectedNodeId);

                if (highlighted) {
                    labelBg.style.opacity = '1';
                    label.style.opacity = '1';
                    return;
                }

                if (selectedEdgeId) {
                    labelBg.style.opacity = '.12';
                    label.style.opacity = '.12';
                    return;
                }

                labelBg.style.opacity = active ? '1' : '.45';
                label.style.opacity = active ? '1' : '.45';
            });

            if (selectedEdgeId) {
                renderEdgeInspector(selectedEdgeId);
            } else {
                renderInspector(selectedNodeId);
            }
        };

        const defs = create('defs', {});
        defs.innerHTML = `
            <marker id="arr"       markerWidth="9" markerHeight="7" refX="8" refY="3.5" orient="auto" markerUnits="strokeWidth"><polygon points="0 0,9 3.5,0 7" fill="#94a3b8"/></marker>
            <marker id="arr-vpn"   markerWidth="9" markerHeight="7" refX="8" refY="3.5" orient="auto" markerUnits="strokeWidth"><polygon points="0 0,9 3.5,0 7" fill="#9333ea"/></marker>
            <marker id="arr-wan"   markerWidth="9" markerHeight="7" refX="8" refY="3.5" orient="auto" markerUnits="strokeWidth"><polygon points="0 0,9 3.5,0 7" fill="#f97316"/></marker>
            <marker id="arr-fiber" markerWidth="9" markerHeight="7" refX="8" refY="3.5" orient="auto" markerUnits="strokeWidth"><polygon points="0 0,9 3.5,0 7" fill="#2563eb"/></marker>
            <marker id="arr-teal"  markerWidth="9" markerHeight="7" refX="8" refY="3.5" orient="auto" markerUnits="strokeWidth"><polygon points="0 0,9 3.5,0 7" fill="#0891b2"/></marker>
            <marker id="arr-green" markerWidth="9" markerHeight="7" refX="8" refY="3.5" orient="auto" markerUnits="strokeWidth"><polygon points="0 0,9 3.5,0 7" fill="#16a34a"/></marker>
        `;
        svg.appendChild(defs);

        const viewport = create('g', { id: 'viewport' });
        svg.appendChild(viewport);

        nodes.forEach((node, index) => {
            const angle = (index / Math.max(nodes.length, 1)) * Math.PI * 2;
            const jitter = (index % 2 === 0 ? 1 : -1) * 18;
            const defaultX = centerX + Math.cos(angle) * (radius + jitter);
            const defaultY = centerY + Math.sin(angle) * (radius + jitter * .6);
            const x = Number.isFinite(Number(node.layout_x)) ? Number(node.layout_x) : defaultX;
            const y = Number.isFinite(Number(node.layout_y)) ? Number(node.layout_y) : defaultY;
            nodeMap.set(node.id, { ...node, x, y });
        });

        try {
            const localSnapshotRaw = localStorage.getItem(localLayoutKey);
            if (localSnapshotRaw) {
                const localSnapshot = JSON.parse(localSnapshotRaw);
                if (Array.isArray(localSnapshot?.nodes)) {
                    localSnapshot.nodes.forEach((item) => {
                        const node = nodeMap.get(item.id);
                        if (!node) return;
                        if (Number.isFinite(Number(item.x))) node.x = Number(item.x);
                        if (Number.isFinite(Number(item.y))) node.y = Number(item.y);
                    });
                }
            }
        } catch (_) {}

        edges.forEach((edge) => {
            const from = nodeMap.get(edge.from);
            const to = nodeMap.get(edge.to);
            if (!from || !to) return;

            const st = EDGE_STYLE(edge.label);

            const line = create('line', {
                x1: from.x,
                y1: from.y,
                x2: to.x,
                y2: to.y,
                stroke: st.stroke,
                'stroke-width': String(st.w),
                'stroke-linecap': 'round',
                'marker-end': st.m,
                opacity: '.9'
            });
            if (st.dash) {
                line.setAttribute('stroke-dasharray', st.dash);
            }
            if (st.anim) {
                line.classList.add('edge-flow');
            }
            line.style.cursor = 'pointer';
            viewport.appendChild(line);
            edgeElements.push({ edge, line, style: st });

            const midX = (from.x + to.x) / 2;
            const midY = (from.y + to.y) / 2;
            const labelBg = create('rect', {
                x: midX - 32,
                y: midY - 12,
                width: 64,
                height: 20,
                rx: 8,
                fill: '#0f172a',
                stroke: '#334155',
                'stroke-width': '1'
            });
            viewport.appendChild(labelBg);

            const label = create('text', {
                x: midX,
                y: midY + 3,
                'text-anchor': 'middle',
                'font-size': '10',
                fill: st.stroke
            });
            label.textContent = EDGE_DISPLAY_LABEL(edge).slice(0, 22);
            label.style.cursor = 'pointer';
            viewport.appendChild(label);
            labelBg.style.cursor = 'pointer';
            edgeLabelElements.push({ edge, label, labelBg });

            const onSelectEdge = (event) => {
                event.stopPropagation();
                selectedNodeId = null;
                selectedEdgeId = edge.id;
                applySelection();
            };

            line.addEventListener('click', onSelectEdge);
            label.addEventListener('click', onSelectEdge);
            labelBg.addEventListener('click', onSelectEdge);
        });

        const clamp = (value, min, max) => Math.max(min, Math.min(max, value));

        const chipClass = 'node-chip';

        const renderLegend = () => {
            const typeCount = {};
            const statusCount = {};

            Array.from(nodeMap.values()).forEach((node) => {
                const typeKey = node.type || 'Sin tipo';
                const statusKey = (node.status || 'desconocido').toLowerCase();
                typeCount[typeKey] = (typeCount[typeKey] || 0) + 1;
                statusCount[statusKey] = (statusCount[statusKey] || 0) + 1;
            });

            legendByType.innerHTML = '';
            legendByStatus.innerHTML = '';

            Object.keys(typeCount).sort().forEach((typeName) => {
                const chip = document.createElement('span');
                chip.className = chipClass;
                chip.textContent = `${typeName}: ${typeCount[typeName]}`;
                legendByType.appendChild(chip);
            });

            Object.keys(statusCount).sort().forEach((statusName) => {
                const chip = document.createElement('span');
                chip.className = chipClass;
                chip.textContent = `${statusName}: ${statusCount[statusName]}`;
                legendByStatus.appendChild(chip);
            });
        };

        const exportSvgAsPng = () => {
            const serializer = new XMLSerializer();
            const svgClone = svg.cloneNode(true);
            svgClone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            svgClone.setAttribute('width', String(width));
            svgClone.setAttribute('height', String(height));

            const svgString = serializer.serializeToString(svgClone);
            const blob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);

            const image = new Image();
            image.onload = () => {
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const context = canvas.getContext('2d');

                if (!context) {
                    URL.revokeObjectURL(url);
                    return;
                }

                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, width, height);
                context.drawImage(image, 0, 0);

                const pngUrl = canvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.href = pngUrl;
                link.download = `itcity-red-sede-{{ $branch->id }}.png`;
                link.click();

                URL.revokeObjectURL(url);
            };
            image.src = url;
        };

        const exportAsPdf = () => {
            window.print();
        };

        const markDirty = () => {
            hasPendingSave = true;
            saveButton.disabled = false;
            setStatus('Cambios pendientes. Presiona "Guardar layout".');
            scheduleAutoSave();

            try {
                localStorage.setItem(localLayoutKey, JSON.stringify({
                    saved_at: Date.now(),
                    nodes: buildLayoutPayload().nodes,
                }));
            } catch (_) {}
        };

        const applyViewportTransform = () => {
            viewport.setAttribute('transform', `translate(${panX} ${panY}) scale(${zoom})`);
        };

        const centerViewOnNodes = ({ keepZoom = true, autoFit = false } = {}) => {
            if (!keepZoom && !autoFit) {
                zoom = 1;
            }

            if (!nodeMap.size) {
                panX = 0;
                panY = 0;
                applyViewportTransform();
                return;
            }

            const values = Array.from(nodeMap.values());
            const minX = Math.min(...values.map((node) => Number(node.x) || 0));
            const maxX = Math.max(...values.map((node) => Number(node.x) || 0));
            const minY = Math.min(...values.map((node) => Number(node.y) || 0));
            const maxY = Math.max(...values.map((node) => Number(node.y) || 0));

            if (autoFit) {
                const fitPadding = 95;
                const spanX = Math.max(120, maxX - minX);
                const spanY = Math.max(90, maxY - minY);
                const zoomX = width / (spanX + fitPadding * 2);
                const zoomY = height / (spanY + fitPadding * 2);
                zoom = clamp(Math.min(zoomX, zoomY), MIN_ZOOM, MAX_ZOOM);
            }

            const nodesCenterX = (minX + maxX) / 2;
            const nodesCenterY = (minY + maxY) / 2;

            panX = (width / (2 * zoom)) - nodesCenterX;
            panY = (height / (2 * zoom)) - nodesCenterY;

            applyViewportTransform();
        };

        const zoomTo = (nextZoom) => {
            zoom = clamp(nextZoom, MIN_ZOOM, MAX_ZOOM);
            applyViewportTransform();
        };

        const getMousePosition = (event) => {
            const point = svg.createSVGPoint();
            point.x = event.clientX;
            point.y = event.clientY;
            const matrix = viewport.getScreenCTM();
            if (!matrix) {
                return { x: 0, y: 0 };
            }
            return point.matrixTransform(matrix.inverse());
        };

        const getScreenPosition = (event) => ({ x: event.clientX, y: event.clientY });

        const redrawEdges = () => {
            edgeElements.forEach(({ edge, line }) => {
                const from = nodeMap.get(edge.from);
                const to = nodeMap.get(edge.to);
                if (!from || !to) return;
                line.setAttribute('x1', from.x);
                line.setAttribute('y1', from.y);
                line.setAttribute('x2', to.x);
                line.setAttribute('y2', to.y);
            });

            edgeLabelElements.forEach(({ edge, label, labelBg }) => {
                const from = nodeMap.get(edge.from);
                const to = nodeMap.get(edge.to);
                if (!from || !to) return;
                const midX = (from.x + to.x) / 2;
                const midY = (from.y + to.y) / 2;
                labelBg.setAttribute('x', midX - 32);
                labelBg.setAttribute('y', midY - 12);
                label.setAttribute('x', midX);
                label.setAttribute('y', midY + 3);
            });
        };

        const redrawNode = (nodeId) => {
            const element = nodeElements.get(nodeId);
            const node = nodeMap.get(nodeId);
            if (!element || !node) return;

            element.circle.setAttribute('cx', node.x);
            element.circle.setAttribute('cy', node.y);
            element.text.setAttribute('x', node.x);
            element.text.setAttribute('y', node.y + 5);
            element.tag.setAttribute('x', node.x);
            element.tag.setAttribute('y', node.y + 48);
        };

        const getCurrentFilters = () => ({
            type: (filterType.value || '').trim().toLowerCase(),
            status: (filterStatus.value || '').trim().toLowerCase(),
            showEdgeLabels: toggleEdgeLabels.checked,
        });

        const nodeMatchesFilters = (node, filters) => {
            const typeOk = !filters.type || (node.type || '').toLowerCase() === filters.type;
            const statusOk = !filters.status || (node.status || '').toLowerCase() === filters.status;
            return typeOk && statusOk;
        };

        const applyFilters = () => {
            const filters = getCurrentFilters();
            const visibleNodeIds = new Set();

            nodeMap.forEach((node) => {
                const match = nodeMatchesFilters(node, filters);
                const element = nodeElements.get(node.id);
                if (!element) return;

                if (match) {
                    visibleNodeIds.add(node.id);
                    element.group.style.opacity = '1';
                    element.group.style.pointerEvents = 'auto';
                } else {
                    element.group.style.opacity = '.14';
                    element.group.style.pointerEvents = 'none';
                }
            });

            edgeElements.forEach(({ edge, line }) => {
                const visible = visibleNodeIds.has(edge.from) && visibleNodeIds.has(edge.to);
                line.style.display = visible ? 'block' : 'none';
            });

            edgeLabelElements.forEach(({ edge, label, labelBg }) => {
                const visibleNodes = visibleNodeIds.has(edge.from) && visibleNodeIds.has(edge.to);
                const showLabel = visibleNodes && filters.showEdgeLabels;
                label.style.display = showLabel ? 'block' : 'none';
                labelBg.style.display = showLabel ? 'block' : 'none';
            });
        };

        Array.from(nodeMap.values()).forEach(node => {
            const group = create('g', { style: 'cursor:pointer' });

            const circle = create('circle', {
                cx: node.x,
                cy: node.y,
                r: 30,
                fill: nodeColor(node.status),
                stroke: strokeByType(node.type),
                'stroke-width': '3'
            });

            const title = create('title', {});
            title.textContent = `${node.label} (${node.type})\nIP: ${node.ip || 'N/A'}\nEstado: ${node.status}`;
            circle.appendChild(title);

            const text = create('text', {
                x: node.x,
                y: node.y + 5,
                'text-anchor': 'middle',
                'font-size': '11',
                'font-weight': '700',
                fill: '#ffffff'
            });
            text.textContent = (node.label || '').slice(0, 11);

            const tag = create('text', {
                x: node.x,
                y: node.y + 48,
                'text-anchor': 'middle',
                'font-size': '11',
                fill: '#cbd5e1'
            });
            tag.textContent = node.type;

            group.dataset.nodeId = String(node.id);

            group.appendChild(circle);
            group.appendChild(text);
            group.appendChild(tag);

            nodeElements.set(node.id, { group, circle, text, tag });

            group.addEventListener('mousedown', (event) => {
                const mouse = getMousePosition(event);
                draggingNodeId = node.id;
                nodeDragMoved = false;
                dragOffset = {
                    x: mouse.x - node.x,
                    y: mouse.y - node.y,
                };
                event.stopPropagation();
                event.preventDefault();
            });

            group.addEventListener('click', () => {
                if (nodeDragMoved) {
                    return;
                }
                selectedNodeId = node.id;
                selectedEdgeId = null;
                applySelection();
            });

            group.addEventListener('dblclick', () => {
                window.location.href = node.detail_url;
            });

            viewport.appendChild(group);
        });

        svg.addEventListener('mousedown', (event) => {
            if (event.target !== svg) {
                return;
            }

            isPanning = true;
            panStart = getScreenPosition(event);
            svg.style.cursor = 'grabbing';
        });

        svg.addEventListener('wheel', (event) => {
            event.preventDefault();
            const direction = event.deltaY > 0 ? -0.1 : 0.1;
            zoomTo(zoom + direction);
        }, { passive: false });

        if (zoomInButton) {
            zoomInButton.addEventListener('click', () => zoomTo(zoom + 0.12));
        }
        if (zoomOutButton) {
            zoomOutButton.addEventListener('click', () => zoomTo(zoom - 0.12));
        }
        if (resetViewButton) {
            resetViewButton.addEventListener('click', () => {
                centerViewOnNodes({ keepZoom: false, autoFit: true });
                setStatus('Vista reiniciada.');
            });
        }

        if (autoLayoutButton) {
            autoLayoutButton.addEventListener('click', () => {
            const byType = {};
            Array.from(nodeMap.values()).forEach((node) => {
                const key = node.type || 'Nodo';
                if (!byType[key]) {
                    byType[key] = [];
                }
                byType[key].push(node);
            });

            const typeKeys = Object.keys(byType);
            const columns = Math.max(1, Math.ceil(Math.sqrt(typeKeys.length)));
            const rows = Math.max(1, Math.ceil(typeKeys.length / columns));
            const cellWidth = width / columns;
            const cellHeight = height / rows;

            typeKeys.forEach((typeKey, typeIndex) => {
                const col = typeIndex % columns;
                const row = Math.floor(typeIndex / columns);
                const clusterCenterX = (col * cellWidth) + (cellWidth / 2);
                const clusterCenterY = (row * cellHeight) + (cellHeight / 2);
                const groupNodes = byType[typeKey];
                const clusterRadius = Math.min(cellWidth, cellHeight) * 0.22;

                groupNodes.forEach((node, index) => {
                    const angle = (index / Math.max(groupNodes.length, 1)) * Math.PI * 2;
                    node.x = clamp(clusterCenterX + Math.cos(angle) * clusterRadius, AUTO_LAYOUT_MARGIN, width - AUTO_LAYOUT_MARGIN);
                    node.y = clamp(clusterCenterY + Math.sin(angle) * clusterRadius, AUTO_LAYOUT_MARGIN, height - AUTO_LAYOUT_MARGIN);
                    redrawNode(node.id);
                });
            });

            redrawEdges();
            markDirty();
            setStatus('Auto-layout aplicado. Guarda para persistir.');
            applyFilters();
            renderLegend();
            });
        }

        if (exportPngButton) {
            exportPngButton.addEventListener('click', exportSvgAsPng);
        }
        if (exportPdfButton) {
            exportPdfButton.addEventListener('click', exportAsPdf);
        }

        // ── Presentation Mode ──────────────────────────────────────────────
        let pollTimer = null;
        let countdownTimer = null;
        let secondsLeft = POLL_INTERVAL;
        let presentationActive = false;

        const updateNodeStatuses = () => {
            $.getJSON(statusUrl).done(function (data) {
                if (!data || !Array.isArray(data.nodes)) return;
                data.nodes.forEach((item) => {
                    const node = nodeMap.get(item.id);
                    const el = nodeElements.get(item.id);
                    if (!node || !el) return;
                    node.status = item.status;
                    el.circle.setAttribute('fill', nodeColor(item.status));
                    el.circle.querySelector('title').textContent =
                        `${node.label} (${node.type})\nIP: ${node.ip || 'N/A'}\nEstado: ${item.status}`;
                });
                applyFilters();
                renderLegend();
            });
        };

        const startCountdown = () => {
            secondsLeft = POLL_INTERVAL;
            if (countdownTimer) clearInterval(countdownTimer);
            countdownTimer = setInterval(() => {
                secondsLeft -= 1;
                if (secondsLeft <= 0) {
                    secondsLeft = POLL_INTERVAL;
                    updateNodeStatuses();
                }
                psCountdown.textContent = `Próx. actualiz.: ${secondsLeft}s`;
            }, 1000);
            psCountdown.textContent = `Próx. actualiz.: ${secondsLeft}s`;
        };

        const enterPresentationMode = () => {
            presentationActive = true;
            btnPresentation.classList.add('active');
            updateNodeStatuses();
            startCountdown();
            const req = networkWrapper.requestFullscreen
                ? networkWrapper.requestFullscreen()
                : networkWrapper.webkitRequestFullscreen
                ? networkWrapper.webkitRequestFullscreen()
                : Promise.resolve();
            req && req.catch(() => {});
        };

        const exitPresentationMode = () => {
            presentationActive = false;
            btnPresentation.classList.remove('active');
            if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
            if (countdownTimer) { clearInterval(countdownTimer); countdownTimer = null; }
            const exitFn = document.exitFullscreen || document.webkitExitFullscreen;
            if (exitFn && (document.fullscreenElement || document.webkitFullscreenElement)) {
                exitFn.call(document);
            }
        };

        if (btnPresentation) {
            btnPresentation.addEventListener('click', () => {
                presentationActive ? exitPresentationMode() : enterPresentationMode();
            });
        }

        if (btnExitPresentation) {
            btnExitPresentation.addEventListener('click', exitPresentationMode);
        }

        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement && presentationActive) {
                exitPresentationMode();
            }
        });
        document.addEventListener('webkitfullscreenchange', () => {
            if (!document.webkitFullscreenElement && presentationActive) {
                exitPresentationMode();
            }
        });
        // ───────────────────────────────────────────────────────────────────

        const knownTypes = Array.from(new Set(Array.from(nodeMap.values()).map(node => node.type).filter(Boolean))).sort();
        knownTypes.forEach((typeName) => {
            const option = document.createElement('option');
            option.value = String(typeName).toLowerCase();
            option.textContent = typeName;
            filterType.appendChild(option);
        });

        if (filterType) {
            filterType.addEventListener('change', applyFilters);
        }
        if (filterStatus) {
            filterStatus.addEventListener('change', applyFilters);
        }
        if (toggleEdgeLabels) {
            toggleEdgeLabels.addEventListener('change', applyFilters);
        }

        window.addEventListener('mousemove', (event) => {
            if (draggingNodeId !== null) {
                const node = nodeMap.get(draggingNodeId);
                if (!node) return;

                const mouse = getMousePosition(event);
                node.x = mouse.x - dragOffset.x;
                node.y = mouse.y - dragOffset.y;

                redrawNode(draggingNodeId);
                redrawEdges();
                nodeDragMoved = true;
                markDirty();
                return;
            }

            if (isPanning) {
                const current = getScreenPosition(event);
                const dx = (current.x - panStart.x) / zoom;
                const dy = (current.y - panStart.y) / zoom;

                panX += dx;
                panY += dy;
                panStart = current;
                applyViewportTransform();
            }
        });

        window.addEventListener('mouseup', () => {
            draggingNodeId = null;
            isPanning = false;
            svg.style.cursor = 'default';
            setTimeout(() => {
                nodeDragMoved = false;
            }, 0);
        });

        document.addEventListener('keydown', async (event) => {
            if (event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement || event.target instanceof HTMLSelectElement) {
                return;
            }

            if ((event.key === 'Delete' || event.key === 'Backspace') && selectedEdgeId) {
                event.preventDefault();
                const edgeId = selectedEdgeId;
                const confirmed = await window.itcityConfirm({
                    title: 'Eliminar enlace',
                    text: '¿Deseas eliminar este enlace de la topología?',
                    icon: 'warning',
                    confirmButtonText: 'Sí, eliminar',
                });
                if (!confirmed) return;

                try {
                    const response = await fetch(`${edgeDeleteUrlBase}/${edgeId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    });
                    if (!response.ok) throw new Error(`Error ${response.status}`);

                    removeEdgeFromCanvas(edgeId);
                    applySelection();
                    setStatus('Enlace eliminado.', 'ok');
                    window.itcityAlert({ icon: 'success', title: 'Enlace eliminado', text: 'La conexión fue eliminada.', toast: true, position: 'top-end' });
                } catch (_) {
                    setStatus('No se pudo eliminar el enlace.', 'err');
                    window.itcityAlert({ icon: 'error', title: 'Error al eliminar', text: 'No se pudo eliminar el enlace.', toast: true, position: 'top-end' });
                }
            }
        });

        svg.addEventListener('click', (event) => {
            if (event.target === svg || event.target === viewport) {
                selectedNodeId = null;
                selectedEdgeId = null;
                applySelection();
            }
        });

        if (nodeInspector) {
            nodeInspector.addEventListener('click', (event) => {
                const deleteEdgeBtn = event.target.closest('[data-action="delete-edge"]');
                if (deleteEdgeBtn) {
                    event.stopPropagation();
                    const edgeId = Number(deleteEdgeBtn.getAttribute('data-edge-id'));
                    if (!Number.isFinite(edgeId)) return;

                    window.itcityConfirm({
                        title: 'Eliminar enlace',
                        text: '¿Deseas eliminar este enlace de la topología?',
                        icon: 'warning',
                        confirmButtonText: 'Sí, eliminar',
                    }).then(async (ok) => {
                        if (!ok) return;
                        try {
                            const response = await fetch(`${edgeDeleteUrlBase}/${edgeId}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                            });
                            if (!response.ok) throw new Error(`Error ${response.status}`);

                            removeEdgeFromCanvas(edgeId);
                            applySelection();
                            setStatus('Enlace eliminado.', 'ok');
                            window.itcityAlert({ icon: 'success', title: 'Enlace eliminado', text: 'La conexión fue eliminada.', toast: true, position: 'top-end' });
                        } catch (error) {
                            setStatus('No se pudo eliminar el enlace.', 'err');
                            window.itcityAlert({ icon: 'error', title: 'Error al eliminar', text: 'No se pudo eliminar el enlace.', toast: true, position: 'top-end' });
                        }
                    });
                    return;
                }

                const deleteNodeBtn = event.target.closest('[data-action="delete-node"]');
                if (deleteNodeBtn) {
                    event.stopPropagation();
                    const nodeId = Number(deleteNodeBtn.getAttribute('data-node-id'));
                    if (!Number.isFinite(nodeId)) return;

                    window.itcityConfirm({
                        title: 'Eliminar nodo',
                        text: '¿Deseas eliminar este nodo? También se removerán sus enlaces.',
                        icon: 'warning',
                        confirmButtonText: 'Sí, eliminar',
                    }).then(async (ok) => {
                        if (!ok) return;
                        try {
                            const response = await fetch(`${nodeDeleteUrlBase}/${nodeId}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                            });
                            if (!response.ok) throw new Error(`Error ${response.status}`);

                            removeNodeFromCanvas(nodeId);
                            selectedNodeId = null;
                            selectedEdgeId = null;
                            applySelection();
                            setStatus('Nodo eliminado.', 'ok');
                            window.itcityAlert({ icon: 'success', title: 'Nodo eliminado', text: 'El nodo y sus enlaces se eliminaron.', toast: true, position: 'top-end' });
                        } catch (error) {
                            setStatus('No se pudo eliminar el nodo.', 'err');
                            window.itcityAlert({ icon: 'error', title: 'Error al eliminar', text: 'No se pudo eliminar el nodo.', toast: true, position: 'top-end' });
                        }
                    });
                    return;
                }

                const edgeItem = event.target.closest('[data-edge-id]');
                if (!edgeItem) return;

                const edgeId = Number(edgeItem.getAttribute('data-edge-id'));
                if (!Number.isFinite(edgeId)) return;

                selectedNodeId = null;
                selectedEdgeId = selectedEdgeId === edgeId ? null : edgeId;
                applySelection();
            });
        }

        if (saveButton) {
            saveButton.addEventListener('click', () => {
                persistLayout({ manual: true });
            });
        }

        window.addEventListener('beforeunload', () => {
            if (!hasPendingSave) return;
            try {
                localStorage.setItem(localLayoutKey, JSON.stringify({
                    saved_at: Date.now(),
                    nodes: buildLayoutPayload().nodes,
                }));
            } catch (_) {}
        });

        centerViewOnNodes({ keepZoom: false, autoFit: true });
        applyFilters();
        renderLegend();
        applySelection();

        if (!nodes.length) {
            const emptyText = create('text', {
                x: centerX,
                y: centerY,
                'text-anchor': 'middle',
                'font-size': '20',
                fill: '#64748b'
            });
            emptyText.textContent = 'No hay nodos para dibujar en esta sede.';
            viewport.appendChild(emptyText);
        }
    })();
</script>
</body>
</html>
