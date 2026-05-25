<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitacora de asignacion - Activo {{ $asset->id }}</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 24px;
            color: #111827;
            background: #f8fafc;
        }
        .wrap {
            max-width: 1300px;
            margin: 0 auto;
        }
        .header {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 14px;
        }
        h1 {
            margin: 0 0 8px;
            font-size: 22px;
        }
        .meta {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.5;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
        }
        .entry-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
            background: #fff;
        }
        .entry-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 8px;
        }
        .entry-title {
            font-weight: 600;
            color: #0f172a;
        }
        .reason {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            border-radius: 8px;
            padding: 8px 10px;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .entry-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 8px 12px;
        }
        .entry-item {
            font-size: 13px;
        }
        .entry-item strong {
            color: #334155;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            vertical-align: top;
            text-align: left;
        }
        thead th {
            background: #f3f4f6;
            position: sticky;
            top: 0;
        }
        .muted {
            color: #6b7280;
        }
        .nowrap {
            white-space: nowrap;
        }
        @media print {
            body {
                margin: 0;
                background: #fff;
            }
            .header, .card {
                border: 0;
                border-radius: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>Bitacora de cambios de asignacion</h1>
        <div class="meta">
            <strong>Activo:</strong>
            {{ $asset->asset_tag ?: ('Activo #' . $asset->id) }}
            @if (!empty($asset->hostname)) · {{ $asset->hostname }} @endif
            <br>
            <strong>Serie:</strong> {{ $asset->serial_number ?: 'N/A' }}
            · <strong>Campus:</strong> {{ optional($asset->branch)->name ?: 'N/A' }}
            · <strong>Nodo:</strong> {{ optional($asset->node)->name ?: 'Sin asignar' }}
            · <strong>Usuario actual:</strong> {{ $asset->assigned_user ?: 'Sin asignar' }}
        </div>
    </div>

    <div class="card">
        @if ($assignmentLog->isEmpty())
            <p class="muted">Este activo aun no tiene registros en la bitacora de asignacion.</p>
        @else
            @foreach ($assignmentLog as $entry)
                @php
                    $displayChangeReason = $entry['change_reason'] ?: ($entry['interaction_note'] ?: 'Sin motivo capturado');
                @endphp
                <div class="entry-card">
                    <div class="entry-head">
                        <div>
                            <div class="entry-title">{{ $entry['assigned_user'] ?: 'Sin asignar' }}</div>
                            <div class="muted">Entrega: {{ $entry['by'] ?: 'Sistema' }} · Recibe: {{ $entry['received_by'] ?: 'No capturado' }}</div>
                        </div>
                        <div class="muted nowrap">{{ $entry['at_label'] }}</div>
                    </div>

                    <div class="reason">
                        <strong>Causa de cambio:</strong>
                        {{ $displayChangeReason }}
                    </div>

                    @if (!empty($entry['interaction_note']))
                        <div class="muted" style="margin-bottom:8px; font-size:13px;">
                            <strong>Nota interna:</strong> {{ $entry['interaction_note'] }}
                        </div>
                    @endif

                    @if (!empty($entry['transfer_request_id']) || !empty($entry['transfer_from_branch']) || !empty($entry['transfer_to_branch']) || !empty($entry['transfer_from_user']) || !empty($entry['transfer_to_user']))
                        <div class="muted" style="margin-bottom:8px; font-size:13px;">
                            <strong>Traspaso:</strong>
                            @if (!empty($entry['transfer_request_id']))
                                Solicitud #{{ $entry['transfer_request_id'] }} ·
                            @endif
                            @if (!empty($entry['transfer_priority']))
                                Prioridad {{ strtoupper((string) $entry['transfer_priority']) }} ·
                            @endif
                            @if (!empty($entry['transfer_from_user']) || !empty($entry['transfer_to_user']))
                                {{ $entry['transfer_from_user'] ?: 'N/A' }} → {{ $entry['transfer_to_user'] ?: 'N/A' }} ·
                            @endif
                            @if (!empty($entry['transfer_from_branch']) || !empty($entry['transfer_to_branch']))
                                {{ $entry['transfer_from_branch'] ?: 'N/A' }} → {{ $entry['transfer_to_branch'] ?: 'N/A' }}
                            @endif
                        </div>
                    @endif

                    <div class="entry-grid">
                        <div class="entry-item"><strong>Causa de cambio:</strong> {{ $displayChangeReason }}</div>
                        <div class="entry-item"><strong>Numero de serie:</strong> {{ $entry['serial_number'] ?: '—' }}</div>
                        <div class="entry-item"><strong>Descripcion:</strong> {{ $entry['description'] ?: '—' }}</div>
                        <div class="entry-item"><strong>Folio factura:</strong> {{ $entry['invoice_folio'] ?: '—' }}</div>
                        <div class="entry-item"><strong>Proveedor:</strong> {{ $entry['supplier'] ?: '—' }}</div>
                        <div class="entry-item"><strong>Campus:</strong> {{ $entry['campus'] ?: '—' }}</div>
                        <div class="entry-item"><strong>Fecha entrega/asignacion:</strong> {{ $entry['assigned_at_label'] ?: '—' }}</div>
                        <div class="entry-item"><strong>Quien entrega:</strong> {{ $entry['by'] ?: 'Sistema' }}</div>
                        <div class="entry-item"><strong>Quien recibe:</strong> {{ $entry['received_by'] ?: '—' }}</div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
</body>
</html>
