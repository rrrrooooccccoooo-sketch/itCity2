<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Responsiva {{ $reference }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            line-height: 1.45;
        }
        .header {
            border-bottom: 1px solid #d1d5db;
            margin-bottom: 14px;
            padding-bottom: 8px;
        }
        .title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px;
        }
        .meta {
            color: #4b5563;
            font-size: 11px;
        }
        .block {
            margin-bottom: 14px;
        }
        .block h3 {
            font-size: 13px;
            margin: 0 0 6px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            vertical-align: top;
        }
        th {
            width: 28%;
            text-align: left;
            background: #f9fafb;
            font-weight: 600;
        }
        .signatures {
            margin-top: 36px;
        }
        .signature-row {
            width: 100%;
            table-layout: fixed;
        }
        .signature-cell {
            width: 50%;
            padding-top: 48px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #9ca3af;
            padding-top: 6px;
            margin: 0 14px;
        }
        .small {
            font-size: 11px;
            color: #4b5563;
        }
        .signature-image-wrap {
            height: 52px;
            margin-bottom: 4px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        .signature-image {
            max-width: 220px;
            max-height: 50px;
            object-fit: contain;
        }
        .verification-block {
            margin-top: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px;
            display: table;
            width: 100%;
        }
        .verification-left,
        .verification-right {
            display: table-cell;
            vertical-align: top;
        }
        .verification-left {
            width: 92px;
            text-align: center;
            padding-right: 8px;
        }
        .verification-right {
            font-size: 10px;
            color: #374151;
        }
        .verification-qr {
            width: 84px;
            height: 84px;
            object-fit: contain;
            border: 1px solid #d1d5db;
        }
        .verification-code {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 10px;
            word-break: break-all;
        }
        .signature-stamp {
            margin-top: 6px;
            font-size: 10px;
            color: #374151;
        }
    </style>
</head>
<body>
    @php
        $emptyLine = '______________________________';
        $assignedAtRaw = data_get($assignmentForm ?? [], 'assigned_at');
        $assignedAtFormatted = $assignedAtRaw ? \Illuminate\Support\Carbon::parse($assignedAtRaw)->format('d/m/Y') : null;
        $signatureSignedAtText = !empty($signatureDeliverySignedAt) ? \Illuminate\Support\Carbon::parse($signatureDeliverySignedAt)->format('d/m/Y H:i') : null;
        $signatureHashShort = !empty($signatureDeliveryHash) ? strtoupper(substr((string) $signatureDeliveryHash, 0, 16)) : null;
        $receivedSignatureDataUrl = data_get($assignmentForm ?? [], 'received_signature_data_url');
        $receivedSignatureHash = data_get($assignmentForm ?? [], 'received_signature_hash');
        $receivedSignatureSignedAtRaw = data_get($assignmentForm ?? [], 'received_signature_signed_at');
        $receivedSignatureSignedAtText = !empty($receivedSignatureSignedAtRaw) ? \Illuminate\Support\Carbon::parse($receivedSignatureSignedAtRaw)->format('d/m/Y H:i') : null;
        $receivedSignatureHashShort = !empty($receivedSignatureHash) ? strtoupper(substr((string) $receivedSignatureHash, 0, 16)) : null;
    @endphp
    <div class="header">
        <div class="title">Carta Responsiva de Equipo de Cómputo</div>
        <div class="meta">
            Folio: <strong>{{ $reference }}</strong> · Fecha: {{ $generatedAt->format('d/m/Y H:i') }} · Generado por: {{ $generatedBy }}
        </div>
    </div>

    <div class="block">
        <h3>Datos del activo</h3>
        <table>
            <tr>
                <th>Sede</th>
                <td>{{ optional($asset->branch)->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Nodo</th>
                <td>{{ optional($asset->node)->name ?? 'Sin asignar' }}</td>
            </tr>
            <tr>
                <th>Tipo</th>
                <td>{{ $equipmentTypeLabel }}</td>
            </tr>
            <tr>
                <th>Etiqueta / Hostname</th>
                <td>{{ $asset->asset_tag ?: '—' }} {{ $asset->hostname ?: '' }}</td>
            </tr>
            <tr>
                <th>Marca / Modelo</th>
                <td>{{ $asset->brand ?: '—' }} {{ $asset->model ?: '' }}</td>
            </tr>
            <tr>
                <th>Número de serie</th>
                <td>{{ $asset->serial_number ?: 'N/A' }}</td>
            </tr>
            <tr>
                <th>Estatus</th>
                <td>{{ $statusLabel }}</td>
            </tr>
            <tr>
                <th>Responsable asignado</th>
                <td>{{ $asset->assigned_user ?: 'Sin asignar' }}</td>
            </tr>
        </table>
    </div>

    <div class="block">
        <h3>Bitácora de cambio de asignación</h3>
        <table>
            <tr>
                <th>Número de serie</th>
                <td>{{ data_get($assignmentForm ?? [], 'serial_number') ?: $emptyLine }}</td>
            </tr>
            <tr>
                <th>Descripción</th>
                <td>{{ data_get($assignmentForm ?? [], 'description') ?: $emptyLine }}</td>
            </tr>
            <tr>
                <th>Folio factura</th>
                <td>{{ data_get($assignmentForm ?? [], 'invoice_folio') ?: $emptyLine }}</td>
            </tr>
            <tr>
                <th>Proveedor</th>
                <td>{{ data_get($assignmentForm ?? [], 'supplier') ?: $emptyLine }}</td>
            </tr>
            <tr>
                <th>Campus</th>
                <td>{{ data_get($assignmentForm ?? [], 'campus') ?: $emptyLine }}</td>
            </tr>
            <tr>
                <th>Fecha de entrega / asignación</th>
                <td>{{ $assignedAtFormatted ?: $emptyLine }}</td>
            </tr>
            <tr>
                <th>Quien recibe</th>
                <td>{{ data_get($assignmentForm ?? [], 'received_by') ?: $emptyLine }}</td>
            </tr>
            <tr>
                <th>Usuario asignado</th>
                <td>{{ data_get($assignmentForm ?? [], 'assigned_user') ?: $emptyLine }}</td>
            </tr>
            <tr>
                <th>Causa de cambio</th>
                <td>{{ data_get($assignmentForm ?? [], 'change_reason') ?: $emptyLine }}</td>
            </tr>
        </table>
    </div>

    <div class="block">
        <h3>Condiciones de resguardo</h3>
        <p>
            La persona responsable declara recibir el equipo descrito en este documento en condiciones de operación,
            comprometiéndose a su uso adecuado, resguardo físico y notificación inmediata ante cualquier incidencia.
        </p>
        <p>
            Cualquier movimiento, cambio de responsable, mantenimiento o baja deberá registrarse en el historial administrativo del activo.
        </p>
        @if (!empty($asset->notes))
            <p class="small"><strong>Notas del activo:</strong> {{ $asset->notes }}</p>
        @endif
    </div>

    <div class="verification-block">
        <div class="verification-left">
            @if (!empty($verificationQrDataUrl))
                <img src="{{ $verificationQrDataUrl }}" alt="QR verificación" class="verification-qr">
            @endif
        </div>
        <div class="verification-right">
            <div><strong>Sello de verificación</strong></div>
            <div class="small">Escanea el QR para abrir la validación de este folio en el panel.</div>
            <div><strong>Huella del documento:</strong></div>
            <div class="verification-code">{{ strtoupper($verificationDigest ?? '') }}</div>
            <div class="small" style="margin-top:6px;"><strong>URL de validación:</strong></div>
            <div class="verification-code">{{ $verificationUrl ?? 'N/A' }}</div>
        </div>
    </div>

    <div class="signatures">
        <table class="signature-row">
            <tr>
                <td class="signature-cell">
                    @if (!empty($signatureDeliveryDataUrl))
                        <div class="signature-image-wrap">
                            <img src="{{ $signatureDeliveryDataUrl }}" class="signature-image" alt="Firma entrega">
                        </div>
                    @endif
                    <div class="signature-line">
                        Entrega ({{ $generatedBy }})
                    </div>
                    @if (!empty($signatureDeliveryDataUrl) && $signatureSignedAtText)
                        <div class="signature-stamp">
                            Firmado digitalmente el {{ $signatureSignedAtText }}
                            @if ($signatureHashShort)
                                · Huella {{ $signatureHashShort }}
                            @endif
                        </div>
                    @endif
                </td>
                <td class="signature-cell">
                    @if (!empty($receivedSignatureDataUrl))
                        <div class="signature-image-wrap">
                            <img src="{{ $receivedSignatureDataUrl }}" class="signature-image" alt="Firma recibe">
                        </div>
                    @endif
                    <div class="signature-line">
                        Recibe (Responsable)
                    </div>
                    @if (!empty($receivedSignatureDataUrl) && $receivedSignatureSignedAtText)
                        <div class="signature-stamp">
                            Firmado digitalmente el {{ $receivedSignatureSignedAtText }}
                            @if ($receivedSignatureHashShort)
                                · Huella {{ $receivedSignatureHashShort }}
                            @endif
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
