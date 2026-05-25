@extends('tenant.layouts.app')

@section('title', 'Memoria mnemotécnica')
@section('page_title', 'Memoria mnemotécnica de la red')

@section('topbar_actions')
    <a href="{{ url('/red/memoria-mnemotecnica') }}" target="_blank" class="btn btn-sm btn-outline-secondary">Ver JSON</a>
    <a href="{{ url('/admin') }}" class="btn btn-sm btn-outline-primary">Volver a admin</a>
@endsection

@section('content')
<style>
    .summary-filter-card { cursor: pointer; transition: transform .12s ease, box-shadow .12s ease; }
    .summary-filter-card:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(15, 23, 42, .08); }
    .summary-filter-card.active { outline: 2px solid #2563eb; outline-offset: -2px; }
</style>
<div class="card section-card mb-4">
    <div class="card-header bg-white fw-semibold">Filtros</div>
    <div class="card-body">
        <form id="memoryFilters" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Sede</label>
                <select class="form-select" id="filterBranch" name="branch_id">
                    <option value="">Todas</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) ($initialFilters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Estado</label>
                <select class="form-select" id="filterStatus" name="status">
                    <option value="" @selected(($initialFilters['status'] ?? '') === '')>Todos</option>
                    <option value="active" @selected(($initialFilters['status'] ?? '') === 'active')>active</option>
                    <option value="inactive" @selected(($initialFilters['status'] ?? '') === 'inactive')>inactive</option>
                    <option value="warning" @selected(($initialFilters['status'] ?? '') === 'warning')>warning</option>
                    <option value="error" @selected(($initialFilters['status'] ?? '') === 'error')>error</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-3 align-items-center">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" id="filterWithRelations" name="with_relations" value="1" @checked(($initialFilters['with_relations'] ?? false) === true)>
                    <label class="form-check-label" for="filterWithRelations">Solo con relaciones</label>
                </div>
                <button type="submit" class="btn btn-primary ms-auto">Aplicar</button>
                <button type="button" class="btn btn-outline-secondary" id="btnClearFilters">Limpiar</button>
            </div>
        </form>
    </div>
</div>

<div class="card section-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Resultados</span>
        <small class="text-muted" id="memoryMeta">Cargando...</small>
    </div>
    <div class="card-body border-bottom py-3">
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="border rounded-3 p-3 h-100 summary-filter-card" data-summary-filter="critical" title="Filtrar por alertas críticas">
                    <div class="small text-muted">Alertas críticas</div>
                    <div class="fw-bold fs-5 text-danger" id="summaryCritical">0</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="border rounded-3 p-3 h-100 summary-filter-card" data-summary-filter="warning" title="Filtrar por alertas warning">
                    <div class="small text-muted">Alertas warning</div>
                    <div class="fw-bold fs-5 text-warning" id="summaryWarning">0</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="border rounded-3 p-3 h-100 summary-filter-card" data-summary-filter="info" title="Filtrar por alertas info">
                    <div class="small text-muted">Alertas info</div>
                    <div class="fw-bold fs-5 text-info" id="summaryInfo">0</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="border rounded-3 p-3 h-100 summary-filter-card" data-summary-filter="" title="Quitar filtro de severidad">
                    <div class="small text-muted">Score promedio</div>
                    <div class="fw-bold fs-5" id="summaryAvgScore">0%</div>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Mnemónico</th>
                    <th>Nodo</th>
                    <th>Sede</th>
                    <th>Estado</th>
                    <th>Puertos</th>
                    <th>Relaciones</th>
                    <th>Score</th>
                    <th>Alertas</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="memoryTableBody">
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">Consultando memoria mnemotécnica...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const endpoint = @json(url('/red/memoria-mnemotecnica'));
    const tableBody = document.getElementById('memoryTableBody');
    const meta = document.getElementById('memoryMeta');
    const filtersForm = document.getElementById('memoryFilters');
    const clearBtn = document.getElementById('btnClearFilters');
    const filterBranch = document.getElementById('filterBranch');
    const filterStatus = document.getElementById('filterStatus');
    const filterWithRelations = document.getElementById('filterWithRelations');
    const summaryCritical = document.getElementById('summaryCritical');
    const summaryWarning = document.getElementById('summaryWarning');
    const summaryInfo = document.getElementById('summaryInfo');
    const summaryAvgScore = document.getElementById('summaryAvgScore');
    const summaryCards = Array.from(document.querySelectorAll('.summary-filter-card'));

    let allItems = [];
    let activeSeverityFilter = '';

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    const badgeClass = (status) => {
        const normalized = String(status || '').toLowerCase();
        if (['active', 'warning', 'error', 'inactive'].includes(normalized)) {
            return normalized;
        }

        return 'default';
    };

    const buildQuery = () => {
        const params = new URLSearchParams();

        if (filterBranch.value) params.set('branch_id', filterBranch.value);
        if (filterStatus.value) params.set('status', filterStatus.value);
        if (filterWithRelations.checked) params.set('with_relations', '1');

        return params.toString();
    };

    const alertBadgeClass = (level) => {
        const value = String(level || '').toLowerCase();

        if (value === 'critical') return 'bg-danger';
        if (value === 'warning') return 'bg-warning text-dark';
        if (value === 'info') return 'bg-info text-dark';

        return 'bg-secondary';
    };

    const renderRows = (items) => {
        if (!Array.isArray(items) || items.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">No se encontraron registros con los filtros actuales.</td></tr>';
            return;
        }

        tableBody.innerHTML = items.map((item) => {
            const relationsCount = (item?.relations?.outgoing?.length || 0) + (item?.relations?.incoming?.length || 0);
            const portsCount = item?.ports?.length || 0;
            const status = item?.status || 'inactive';
            const detailUrl = `/nodos/${item.node_id}`;
            const score = Number(item?.completeness_score ?? 0);
            const scoreClass = score >= 80 ? 'bg-success' : score >= 50 ? 'bg-warning text-dark' : 'bg-danger';
            const alerts = Array.isArray(item?.alerts) ? item.alerts : [];
            const alertHtml = alerts.length
                ? alerts.map((alert) => `<span class="badge ${alertBadgeClass(alert.level)} me-1 mb-1" title="${escapeHtml(alert.message || '')}">${escapeHtml(alert.code || 'alert')}</span>`).join('')
                : '<span class="badge bg-success">sin alertas</span>';

            return `
                <tr>
                    <td>
                        <div class="fw-semibold">${escapeHtml(item.mnemonic || '—')}</div>
                        <small class="text-muted">Fuente: ${escapeHtml(item.mnemonic_source || 'N/A')}</small>
                    </td>
                    <td>
                        <div>${escapeHtml(item.name || 'N/A')}</div>
                        <small class="text-muted">${escapeHtml(item.ip_address || 'sin IP')}</small>
                    </td>
                    <td>${escapeHtml(item?.branch?.name || 'N/A')}</td>
                    <td><span class="sb-badge ${badgeClass(status)}">${escapeHtml(status)}</span></td>
                    <td>${portsCount}</td>
                    <td>${relationsCount}</td>
                    <td><span class="badge ${scoreClass}">${score}%</span></td>
                    <td>${alertHtml}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="${detailUrl}" target="_blank">Ver nodo</a>
                    </td>
                </tr>
            `;
        }).join('');
    };

    const updateSummaryCardSelection = () => {
        summaryCards.forEach((card) => {
            const value = String(card.dataset.summaryFilter || '');
            card.classList.toggle('active', value === activeSeverityFilter);
        });
    };

    const getVisibleItems = () => {
        if (!activeSeverityFilter) {
            return allItems;
        }

        return allItems.filter((item) => {
            const alerts = Array.isArray(item?.alerts) ? item.alerts : [];
            return alerts.some((alert) => String(alert?.level || '').toLowerCase() === activeSeverityFilter);
        });
    };

    const applyTableFilter = () => {
        const visibleItems = getVisibleItems();
        renderRows(visibleItems);

        const suffix = activeSeverityFilter
            ? ` · filtrado por ${activeSeverityFilter}`
            : '';

        meta.textContent = `${visibleItems.length} registro(s) visibles de ${allItems.length}${suffix}`;
        updateSummaryCardSelection();
    };

    const renderSummary = (items) => {
        const rows = Array.isArray(items) ? items : [];

        let critical = 0;
        let warning = 0;
        let info = 0;
        let totalScore = 0;

        rows.forEach((item) => {
            const alerts = Array.isArray(item?.alerts) ? item.alerts : [];
            const score = Number(item?.completeness_score ?? 0);
            totalScore += Number.isFinite(score) ? score : 0;

            alerts.forEach((alert) => {
                const level = String(alert?.level || '').toLowerCase();
                if (level === 'critical') critical += 1;
                else if (level === 'warning') warning += 1;
                else if (level === 'info') info += 1;
            });
        });

        const avg = rows.length > 0 ? Math.round((totalScore / rows.length) * 10) / 10 : 0;

        if (summaryCritical) summaryCritical.textContent = String(critical);
        if (summaryWarning) summaryWarning.textContent = String(warning);
        if (summaryInfo) summaryInfo.textContent = String(info);
        if (summaryAvgScore) summaryAvgScore.textContent = `${avg}%`;
    };

    const load = async () => {
        meta.textContent = 'Cargando...';
        const query = buildQuery();
        const url = query ? `${endpoint}?${query}` : endpoint;

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('No fue posible cargar la memoria.');
            }

            const payload = await response.json();
            const items = payload?.items || [];
            allItems = Array.isArray(items) ? items : [];
            renderSummary(allItems);
            applyTableFilter();
        } catch (error) {
            tableBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">Error al cargar la memoria mnemotécnica.</td></tr>';
            allItems = [];
            renderSummary([]);
            meta.textContent = 'Error';
        }
    };

    summaryCards.forEach((card) => {
        card.addEventListener('click', () => {
            const value = String(card.dataset.summaryFilter || '').toLowerCase();
            activeSeverityFilter = activeSeverityFilter === value ? '' : value;
            applyTableFilter();
        });
    });

    filtersForm.addEventListener('submit', (event) => {
        event.preventDefault();
        load();
    });

    clearBtn.addEventListener('click', () => {
        filterBranch.value = '';
        filterStatus.value = '';
        filterWithRelations.checked = false;
        activeSeverityFilter = '';
        load();
    });

    load();
})();
</script>
@endpush
