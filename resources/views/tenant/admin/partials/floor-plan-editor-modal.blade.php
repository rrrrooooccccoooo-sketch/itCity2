<div class="modal {{ $directFloorPlanMode ? '' : 'fade' }}" id="floorPlanEditorModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="{{ $directFloorPlanMode ? 'static' : 'true' }}" data-bs-keyboard="{{ $directFloorPlanMode ? 'false' : 'true' }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width:95vw; width:95vw;">
        <div class="modal-content floor-plan-editor-content">
            <div class="modal-header">
                <h5 class="modal-title" id="floorPlanEditorTitle">Editor de plano</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body floor-plan-editor-body">
                <div class="row g-3 floor-plan-editor-layout">
                    <div class="col-lg-3 floor-plan-editor-sidebar-col">
                        <div class="border rounded-3 p-3 floor-plan-editor-sidebar" style="position:sticky; top:0; max-height:calc(100vh - 140px); overflow-y:auto;">
                            <div class="small text-muted mb-2">Plano</div>
                            <div class="fw-semibold mb-2" id="fpMetaName">—</div>
                            <div class="small text-muted mb-3" id="fpMetaBranch">—</div>

                            <div class="border rounded-3 p-2 mb-3 bg-light-subtle">
                                <div class="fw-semibold small text-dark mb-2">Escala del plano</div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label mb-1">Ancho real (m)</label>
                                        <input type="number" min="0.1" step="0.1" id="fpScaleWidthMeters" class="form-control form-control-sm" placeholder="Ej. 24">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label mb-1">Alto real (m)</label>
                                        <input type="number" min="0.1" step="0.1" id="fpScaleHeightMeters" class="form-control form-control-sm" placeholder="Ej. 18">
                                    </div>
                                </div>
                                <div class="row g-2 mt-1">
                                    <div class="col-7">
                                        <label class="form-label mb-1">Distancia conocida (m)</label>
                                        <input type="number" min="0.1" step="0.1" id="fpCalibrationDistance" class="form-control form-control-sm" placeholder="Ej. 5.4">
                                    </div>
                                    <div class="col-5 d-flex align-items-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary w-100" id="fpCalibrationToggleBtn">Calibrar</button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2" id="fpCalibrationHint">Marca dos puntos sobre una distancia conocida para calcular la escala automáticamente.</small>
                                <small class="text-muted d-block mt-2" id="fpScaleSummary">Si defines la escala, la cobertura AP se calculará en metros reales.</small>
                            </div>

                            <div class="border rounded-3 p-2 mb-3 bg-light-subtle">
                                <div class="fw-semibold small text-dark mb-2">Alturas 3D</div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label mb-1">Muro (m)</label>
                                        <input type="number" min="0.5" max="20" step="0.1" id="fpWallHeightInput" class="form-control form-control-sm" value="2.8">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label mb-1">Puerta (m)</label>
                                        <input type="number" min="0.5" max="20" step="0.1" id="fpDoorHeightInput" class="form-control form-control-sm" value="2.1">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label mb-1">Ancho puerta (m)</label>
                                        <input type="number" min="0.4" max="6" step="0.1" id="fpDoorWidthInput" class="form-control form-control-sm" value="0.9">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label mb-1">Base ventana (m)</label>
                                        <input type="number" min="0" max="10" step="0.1" id="fpWindowBaseInput" class="form-control form-control-sm" value="1.0">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label mb-1">Ventana alto (m)</label>
                                        <input type="number" min="0.2" max="10" step="0.1" id="fpWindowHeightInput" class="form-control form-control-sm" value="1.2">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label mb-1">Ancho ventana (m)</label>
                                        <input type="number" min="0.3" max="8" step="0.1" id="fpWindowWidthInput" class="form-control form-control-sm" value="1.2">
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">Alturas y anchos alimentan puertas/ventanas paramétricas y vista 3D.</small>
                            </div>

                            <label class="form-label mb-1">AP / Nodo</label>
                            <select id="fpNodeSelect" class="form-select form-select-sm mb-2">
                                <option value="">Sin nodo</option>
                            </select>

                            <label class="form-label mb-1">Modelo AP (catálogo)</label>
                            <select id="fpApModelSelect" class="form-select form-select-sm mb-2">
                                <option value="">Sin modelo / manual</option>
                            </select>

                            <label class="form-label mb-1">Modo de edición</label>
                            <select id="fpEditMode" class="form-select form-select-sm mb-2">
                                <option value="access-point">Punto AP</option>
                                <option value="wall">Muro</option>
                                <option value="door">Puerta</option>
                                <option value="window">Ventana</option>
                                <option value="symbol">Símbolo TI</option>
                            </select>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="fpOrthogonalLockInput" checked>
                                <label class="form-check-label" for="fpOrthogonalLockInput">Bloqueo ortogonal (90°)</label>
                            </div>

                            <label class="form-label mb-1">Biblioteca TI</label>
                            <select id="fpSymbolType" class="form-select form-select-sm mb-2">
                                <option value="rack">Rack</option>
                                <option value="switch">Switch</option>
                                <option value="camera">Cámara</option>
                                <option value="desk">Escritorio</option>
                                <option value="printer">Impresora</option>
                                <option value="ups">UPS</option>
                            </select>
                            <label class="form-label mb-1">Tamaño símbolo (m)</label>
                            <input type="number" min="0.2" max="20" step="0.1" id="fpSymbolSizeInput" class="form-control form-control-sm mb-2" value="1.2">

                            <label class="form-label mb-1">Material del muro</label>
                            <select id="fpWallMaterial" class="form-select form-select-sm mb-2">
                                <option value="drywall">Tablaroca / Drywall (3 dB)</option>
                                <option value="brick">Ladrillo (8 dB)</option>
                                <option value="concrete">Concreto (12 dB)</option>
                                <option value="glass">Vidrio (2 dB)</option>
                                <option value="wood">Madera (5 dB)</option>
                                <option value="metal">Metal (18 dB)</option>
                                <option value="door">Puerta (1.5 dB)</option>
                                <option value="window">Ventana (1 dB)</option>
                            </select>
                            <div id="fpMaterialLegend" class="small text-muted mb-3"></div>

                            <label class="form-label mb-1">Capa</label>
                            <select id="fpLayerSelect" class="form-select form-select-sm mb-2">
                                <option value="access-point">Access point</option>
                                <option value="coverage">Cobertura</option>
                                <option value="critical">Crítico</option>
                            </select>

                            <label class="form-label mb-1">Radio heatmap (%)</label>
                            <input type="number" min="2" max="40" value="12" id="fpRadiusInput" class="form-control form-control-sm mb-2">

                            <label class="form-label mb-1">Cobertura real del AP (m)</label>
                            <input type="number" min="0.5" max="500" step="0.1" id="fpRadiusMetersInput" class="form-control form-control-sm mb-2" placeholder="Usa la escala del plano para radio real">

                            <label class="form-label mb-1">Señal (dBm, opcional)</label>
                            <input type="number" min="-120" max="0" value="-55" id="fpSignalInput" class="form-control form-control-sm mb-2">

                            <label class="form-label mb-1">Patrón de radiación</label>
                            <select id="fpPatternSelect" class="form-select form-select-sm mb-2">
                                <option value="omni-donut">Omnidireccional / dona</option>
                                <option value="sphere">Esférico</option>
                                <option value="sector-120">Sectorial 120°</option>
                                <option value="directional-60">Direccional 60°</option>
                            </select>

                            <label class="form-label mb-1">Montaje / orientación</label>
                            <select id="fpMountOrientationSelect" class="form-select form-select-sm mb-2">
                                <option value="ceiling">Techo</option>
                                <option value="wall-horizontal">Pared horizontal</option>
                                <option value="wall-vertical">Pared vertical</option>
                                <option value="desktop">Escritorio / mueble</option>
                                <option value="custom">Otro</option>
                            </select>

                            <label class="form-label mb-1">Altura de montaje AP (m)</label>
                            <input type="number" min="0.1" max="20" step="0.1" id="fpMountHeightInput" class="form-control form-control-sm mb-2" value="2.6">

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label mb-1">Azimut (°)</label>
                                    <input type="number" min="0" max="359.99" step="1" value="0" id="fpAzimuthInput" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Tilt (°)</label>
                                    <input type="number" min="-90" max="90" step="1" value="0" id="fpTiltInput" class="form-control form-control-sm">
                                </div>
                            </div>
                            <small class="text-muted d-block mb-3">En la vista 2D, los patrones dona/esfera se aproximan como omnidireccionales; el azimut sí afecta sectorial/direccional.</small>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="fpSnapApWallsInput" checked>
                                <label class="form-check-label" for="fpSnapApWallsInput">Snap AP a muros</label>
                            </div>

                            <label class="form-label mb-1">Filtro capa</label>
                            <select id="fpLayerFilter" class="form-select form-select-sm mb-3">
                                <option value="all">Todas</option>
                                <option value="access-point">Access point</option>
                                <option value="coverage">Cobertura</option>
                                <option value="critical">Crítico</option>
                                <option value="walls">Muros</option>
                            </select>

                            <div class="border rounded-3 p-2 mb-3 bg-light-subtle" id="fpNodeInsightsCard" style="display:none;">
                                <div class="fw-semibold small text-dark mb-1">Dispositivos del nodo</div>
                                <div class="small text-muted mb-2" id="fpNodeInsightsTitle">Selecciona un AP ligado a un nodo.</div>
                                <div class="small text-muted" id="fpNodeInsightsBody">Sin información de conectividad.</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="fpClearPointsBtn">Limpiar puntos</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="fpDeleteSelectedPointBtn">Eliminar AP seleccionado</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="fpClearWallsBtn">Limpiar muros</button>
                                <button type="button" class="btn btn-sm btn-primary" id="fpSavePointsBtn">Guardar puntos</button>
                            </div>
                            <small class="text-muted d-block mt-2" id="fpEditHint">Modo AP: clic para agregar, clic en AP para seleccionar/editar y arrastrar; Shift+clic o clic derecho sobre AP para eliminar. Modo muro/puerta/ventana: clic inicio + clic fin para crear; arrastra línea/extremos para ajustar; Shift+clic o clic derecho para eliminar.</small>
                        </div>
                    </div>
                    <div class="col-lg-9 floor-plan-editor-canvas-col">
                        <div class="border rounded-3 p-2 floor-plan-editor-stage" style="position:relative; background:#0f172a; min-height:680px;">
                            <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Vista del plano">
                                    <button type="button" class="btn btn-light active" id="fpView2dBtn">Vista 2D</button>
                                    <button type="button" class="btn btn-outline-light" id="fpView3dBtn">Vista 3D</button>
                                </div>
                                <small class="text-light-emphasis" id="fpViewModeHint">La vista 3D es visual y usa la escala actual del plano.</small>
                            </div>
                            <div id="fpUnsupportedNotice" class="text-warning small mb-2" style="display:none;"></div>
                            <div id="fpCanvasWrap" style="position:relative; width:100%; height:660px; overflow:hidden; background:#020617; border-radius:8px;">
                                <div id="fpZoomControls" style="position:absolute; top:12px; right:12px; z-index:20; display:flex; flex-direction:column; gap:6px;">
                                    <button type="button" class="btn btn-light btn-sm" id="fpZoomInBtn" title="Acercar (Ctrl + rueda)">+</button>
                                    <button type="button" class="btn btn-light btn-sm" id="fpZoomOutBtn" title="Alejar (Ctrl + rueda)">−</button>
                                    <button type="button" class="btn btn-light btn-sm" id="fpZoomResetBtn" title="Restablecer zoom">⟳</button>
                                </div>
                                <div id="fp3dViewport" style="position:absolute; inset:0; display:none; z-index:0;"></div>
                                <img id="fpCanvasImage" alt="Plano" style="max-width:100%; display:none; user-select:none;" draggable="false">
                                <iframe id="fpCanvasPdf" style="width:100%; height:660px; border:0; display:none;"></iframe>
                                <canvas id="fpHeatCanvas" style="position:absolute; inset:0; pointer-events:none; z-index:1;"></canvas>
                                <svg id="fpWallsLayer" style="position:absolute; inset:0; pointer-events:auto; z-index:2;"></svg>
                                <div id="fpOverlayLayer" style="position:absolute; inset:0; pointer-events:auto; z-index:3;"></div>
                                <div id="fpContextMenu" class="shadow rounded-2 border" style="position:absolute; display:none; min-width:220px; z-index:15; background:#ffffff;"></div>
                                <div id="fpSignalProbe" class="floor-plan-editor-signal-probe"></div>
                            </div>
                            <button type="button" class="btn btn-primary floor-plan-editor-save-fab" id="fpSavePointsFabBtn">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <small class="text-muted" id="fpFooterInfo">—</small>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>