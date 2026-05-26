@extends('tenant.layouts.app')

@section('title', $node->name)
@section('page_title', $node->name)

@section('topbar_actions')
    <a href="{{ url('/admin?edit_node=' . $node->id . '&branch_id=' . $node->branch_id) }}" class="btn btn-sm btn-dark">Editar nodo</a>
    <a href="{{ url('/sede/' . $node->branch_id) }}" class="btn btn-sm btn-outline-secondary">Volver a sede</a>
@endsection

@section('content')
@php
    $statusClass = match(strtolower($node->status ?? '')) {
        'active'   => 'active',
        'warning'  => 'warning',
        'error'    => 'error',
        'inactive' => 'inactive',
        default    => 'default',
    };
@endphp

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card ic-card h-100">
            <div class="card-header">Información general</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0" style="font-size:.875rem">
                    <tbody>
                        <tr><td class="text-muted ps-4" style="width:38%">Sede</td><td>{{ optional($node->branch)->name }}</td></tr>
                        <tr><td class="text-muted ps-4">Tipo</td><td>{{ optional($node->nodeType)->name }}</td></tr>
                        <tr><td class="text-muted ps-4">Espacio físico</td><td>{{ optional($node->physicalSpace)->name ? optional($node->physicalSpace)->name . ' (' . strtoupper(optional($node->physicalSpace)->space_type) . ')' : 'N/A' }}</td></tr>
                        <tr><td class="text-muted ps-4">Estado</td><td><span class="sb-badge {{ $statusClass }}">{{ $node->status ?? 'N/A' }}</span></td></tr>
                        <tr><td class="text-muted ps-4">IP</td><td><code style="font-size:.82rem">{{ $node->ip_address ?? '—' }}</code></td></tr>
                        <tr><td class="text-muted ps-4">MAC</td><td><code style="font-size:.82rem">{{ $node->mac_address ?? '—' }}</code></td></tr>
                        <tr><td class="text-muted ps-4">Cableado</td><td>{{ $node->cable_type ?? '—' }}</td></tr>
                        <tr><td class="text-muted ps-4">Ubicación</td><td>Piso {{ $node->floor ?? '—' }}, Cuarto {{ $node->room ?? '—' }}</td></tr>
                        <tr><td class="text-muted ps-4">Monitoreado</td><td>{{ $node->is_monitored ? '<span class="text-success fw-semibold">✔ Sí</span>' : '<span class="text-muted">✖ No</span>' }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card ic-card h-100">
            <div class="card-header">Métricas bajo demanda</div>
            <div class="card-body d-flex flex-column" style="gap:0">
                <div class="d-flex gap-2 mb-3 flex-wrap">
                    <button id="btnLoadMetrics" class="btn btn-primary btn-sm"
                        data-url="{{ url('/nodos/' . $node->id . '/metricas') }}">Consultar métricas</button>
                    <button id="btnProbeNode" class="btn btn-outline-dark btn-sm"
                        data-url="{{ url('/nodos/' . $node->id . '/sondeo') }}">Sondeo automático</button>
                </div>
                <div id="metricsPanel" class="rounded-3 p-3 flex-grow-1" style="background:#f8fafc;min-height:120px">
                    <p class="text-muted small mb-0">Presiona el botón para ver CPU, RAM y disco.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card ic-card h-100">
            <div class="card-header">Conexiones salientes</div>
            <ul class="list-group list-group-flush" style="font-size:.875rem">
                @forelse ($outgoingRelations as $rel)
                    <li class="list-group-item">
                        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8" class="mb-1">{{ $rel->relation_type }}</div>
                        <div class="fw-semibold">{{ optional($rel->toNode)->name ?? 'N/A' }}</div>
                        <div class="text-muted small">{{ optional($rel->toNode)->ip_address ?? 'sin IP' }}</div>
                        @if ($rel->notes) <div class="small text-muted">{{ $rel->notes }}</div> @endif
                    </li>
                @empty
                    <li class="list-group-item text-muted small">Sin conexiones salientes.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card ic-card h-100">
            <div class="card-header">Conexiones entrantes</div>
            <ul class="list-group list-group-flush" style="font-size:.875rem">
                @forelse ($incomingRelations as $rel)
                    <li class="list-group-item">
                        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8" class="mb-1">{{ $rel->relation_type }}</div>
                        <div class="fw-semibold">{{ optional($rel->fromNode)->name ?? 'N/A' }}</div>
                        <div class="text-muted small">{{ optional($rel->fromNode)->ip_address ?? 'sin IP' }}</div>
                        @if ($rel->notes) <div class="small text-muted">{{ $rel->notes }}</div> @endif
                    </li>
                @empty
                    <li class="list-group-item text-muted small">Sin conexiones entrantes.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<div class="card ic-card">
    <div class="card-header">Sistemas alojados en este nodo</div>
    <div class="table-responsive">
        <table class="table table-hover ic-table mb-0">
            <thead>
                <tr>
                    <th>Sistema</th>
                    <th>Versión</th>
                    <th>Proveedor</th>
                    <th>Proyecto</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($softwareSystems as $system)
                    <tr>
                        <td class="fw-semibold">{{ $system->name }}</td>
                        <td>{{ $system->version ?? '—' }}</td>
                        <td>{{ $system->vendor ?? '—' }}</td>
                        <td>{{ $system->project_name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4 small">No hay sistemas ligados a este nodo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card ic-card mt-4">
    <div class="card-header">Historial de sondeos automáticos</div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-6 col-lg-3">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <div class="small text-muted">Éxito últimos 20</div>
                    <div class="fw-bold" style="font-size:1.2rem">{{ $probeSuccessRate !== null ? $probeSuccessRate . '%' : 'N/A' }}</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="border rounded-3 p-3 h-100 bg-light">
                    <div class="small text-muted">Latencia promedio</div>
                    <div class="fw-bold" style="font-size:1.2rem">{{ $probeAvgLatency !== null ? $probeAvgLatency . ' ms' : 'N/A' }}</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Reachable</th>
                        <th>Latencia</th>
                        <th>Puertos abiertos</th>
                        <th>Mensaje</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($probeHistory as $probe)
                        <tr>
                            <td>{{ optional($probe->probed_at)->format('Y-m-d H:i:s') ?? 'N/A' }}</td>
                            <td>
                                @if ($probe->reachable)
                                    <span class="badge text-bg-success">Sí</span>
                                @else
                                    <span class="badge text-bg-danger">No</span>
                                @endif
                            </td>
                            <td>{{ $probe->latency_ms !== null ? $probe->latency_ms . ' ms' : 'N/A' }}</td>
                            <td>
                                @php $ports = is_array($probe->open_ports) ? $probe->open_ports : []; @endphp
                                {{ count($ports) ? implode(', ', $ports) : 'Ninguno' }}
                            </td>
                            <td class="small text-muted">{{ $probe->message ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Aún no hay sondeos guardados para este nodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#btnLoadMetrics').on('click', function () {
        const $p  = $('#metricsPanel');
        const url = $(this).data('url');
        $p.html('<p class="text-muted small mb-0">Consultando métricas…</p>');
        $.get(url).done(function (r) {
            $p.html(`
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
            `);
        }).fail(function () {
            $p.html('<p class="text-muted small mb-0">No fue posible consultar las métricas para este nodo.</p>');
            window.itcityAlert({
                icon: 'error',
                title: 'Error al consultar métricas',
                text: 'No se pudieron consultar las métricas.',
                toast: true,
                position: 'top-end',
            });
        });
    });

    $('#btnProbeNode').on('click', function () {
        const $p  = $('#metricsPanel');
        const url = $(this).data('url');
        $p.html('<p class="text-muted small mb-0">Ejecutando sondeo automático…</p>');

        $.get(url).done(function (r) {
            const reachableBadge = r.reachable
                ? '<span class="badge text-bg-success">Reachable</span>'
                : '<span class="badge text-bg-danger">No responde</span>';

            const latencyText = (r.latency_ms === null || r.latency_ms === undefined)
                ? 'N/A'
                : `${r.latency_ms} ms`;

            const checked = Array.isArray(r.checked_ports) ? r.checked_ports.join(', ') : 'N/A';
            const open = Array.isArray(r.open_ports) && r.open_ports.length ? r.open_ports.join(', ') : 'Ninguno detectado';

            $p.html(`
                <div class="fw-semibold mb-2" style="font-size:.88rem">Sondeo automático · ${r.node_name}</div>
                <div class="mb-2">Estado: ${reachableBadge}</div>
                <div class="mb-2">IP: <strong>${r.ip || 'N/A'}</strong></div>
                <div class="mb-2">Latencia: <strong>${latencyText}</strong></div>
                <div class="mb-2">Puertos revisados: <strong>${checked}</strong></div>
                <div class="mb-2">Puertos abiertos: <strong>${open}</strong></div>
                <div style="font-size:.72rem;color:#94a3b8">${r.message || ''} · ${r.updated_at || ''}</div>
            `);
        }).fail(function (xhr) {
            const msg = xhr?.responseJSON?.message || 'No se pudo ejecutar el sondeo automático.';
            $p.html('<p class="text-muted small mb-0">El sondeo no se pudo completar.</p>');
            window.itcityAlert({
                icon: 'error',
                title: 'Error de sondeo automático',
                text: msg,
                toast: true,
                position: 'top-end',
            });
        });
    });
});
</script>
@endpush
