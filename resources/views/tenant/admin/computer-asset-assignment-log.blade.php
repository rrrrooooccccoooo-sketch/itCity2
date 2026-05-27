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
        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(320px, .9fr);
            gap: 14px;
            align-items: start;
            margin-bottom: 14px;
        }
        .header {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 18px;
        }
        .summary-card {
            background: linear-gradient(135deg, #ffffff 0%, #f1f7ff 100%);
            color: #0f172a;
            border-radius: 10px;
            padding: 18px;
            border: 1px solid #dbe4f0;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .06);
        }
        .summary-title {
            font-size: 14px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .summary-stat {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .summary-stat div {
            background: #ffffff;
            border: 1px solid #dbe4f0;
            border-radius: 10px;
            padding: 10px 12px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .8);
        }
        .summary-stat strong {
            display: block;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 3px;
        }
        .summary-stat span {
            display: block;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.3;
            color: #0f172a;
        }
        .insights-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, .95fr);
            gap: 12px;
            margin-bottom: 12px;
        }
        .histogram-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
        }
        .histogram-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .histogram-head h2 {
            margin: 0;
            font-size: 18px;
        }
        .histogram-head p {
            margin: 4px 0 0;
            color: #6b7280;
            font-size: 13px;
        }
        .histogram-grid {
            display: grid;
            gap: 10px;
        }
        .histogram-row {
            display: grid;
            grid-template-columns: 150px minmax(0, 1fr) 34px;
            gap: 10px;
            align-items: center;
        }
        .histogram-label {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .histogram-track {
            height: 14px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
            position: relative;
        }
        .histogram-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #0f172a 0%, #2563eb 45%, #38bdf8 100%);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.2);
        }
        .histogram-count {
            text-align: right;
            font-weight: 800;
            color: #0f172a;
            font-size: 13px;
        }
        .histogram-foot {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        .histogram-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #dbe4f0;
            background: #f8fafc;
            color: #334155;
        }
        .histogram-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            display: inline-block;
        }
        .route-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid #dbe4f0;
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
        }
        .route-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .route-head h2 {
            margin: 0;
            font-size: 18px;
        }
        .route-head p {
            margin: 4px 0 0;
            color: #6b7280;
            font-size: 13px;
        }
        .route-chain {
            display: flex;
            align-items: stretch;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 4px;
        }
        .route-chain::-webkit-scrollbar {
            height: 8px;
        }
        .route-chain::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }
        .route-step {
            min-width: 220px;
            flex: 0 0 220px;
            background: #fff;
            border: 1px solid #dbe4f0;
            border-radius: 14px;
            padding: 12px;
            position: relative;
            box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .route-step::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -18px;
            width: 26px;
            height: 2px;
            background: linear-gradient(90deg, #60a5fa 0%, #94a3b8 50%, #60a5fa 100%);
            background-size: 200% 100%;
            animation: routePulse 2.2s linear infinite;
        }
        .route-step:last-child::after {
            display: none;
        }
        .route-step:hover,
        .route-step:focus {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(15, 23, 42, .1);
            border-color: #94a3b8;
            outline: none;
        }
        .route-step-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .route-step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            border-radius: 999px;
            color: #fff;
            font-weight: 800;
            font-size: 12px;
            box-shadow: 0 8px 16px rgba(15, 23, 42, .12);
        }
        .route-step h3 {
            margin: 0;
            font-size: 14px;
            color: #0f172a;
            line-height: 1.25;
        }
        .route-step .route-campus {
            display: inline-flex;
            margin-bottom: 8px;
            padding: 4px 8px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
        }
        .route-step .route-meta {
            display: grid;
            gap: 4px;
            font-size: 12px;
            color: #475569;
        }
        .route-step .route-meta strong {
            color: #334155;
        }
        @keyframes routePulse {
            from {
                background-position: 0 50%;
            }
            to {
                background-position: 200% 50%;
            }
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
        .timeline-shell {
            margin-bottom: 12px;
        }
        .timeline-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .timeline-toolbar h2 {
            margin: 0;
            font-size: 18px;
        }
        .timeline-note {
            color: #6b7280;
            font-size: 13px;
        }
        .timeline-main-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, .48fr);
            gap: 12px;
            align-items: start;
        }
        .timeline-track {
            position: relative;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 18px 8px 10px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
        }
        .timeline-track::-webkit-scrollbar {
            height: 10px;
        }
        .timeline-track::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }
        .timeline-events {
            display: inline-flex;
            flex-wrap: nowrap;
            gap: 18px;
            min-width: max-content;
            position: relative;
            z-index: 1;
            padding: 0 14px;
            align-items: flex-start;
            white-space: nowrap;
        }
        .timeline-events::before {
            content: '';
            position: absolute;
            left: 14px;
            right: 14px;
            top: 134px;
            height: 3px;
            background: linear-gradient(90deg, #cfe2f8, #93c5fd, #cfe2f8);
            z-index: 0;
        }
        .timeline-event {
            width: 138px;
            flex: 0 0 138px;
            position: relative;
            display: flex;
            justify-content: center;
            z-index: 1;
        }
        .timeline-node {
            display: block;
            width: 100%;
            border: 0;
            background: transparent;
            border-radius: 0;
            padding: 0;
            text-align: center;
            cursor: pointer;
            transition: transform .18s ease;
            box-shadow: none;
        }
        .timeline-node:hover,
        .timeline-node:focus {
            transform: translateY(-1px);
            outline: none;
        }
        .timeline-node.is-expanded {
            transform: translateY(-2px);
        }
        .timeline-node-head {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-bottom: 0;
        }
        .timeline-pin {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 120px;
        }
        .timeline-bubble {
            width: 46px;
            height: 46px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--node-color, #3b82f6);
            color: #fff;
            box-shadow: 0 8px 14px rgba(15, 23, 42, .18);
            font-size: 18px;
        }
        .timeline-stem {
            width: 3px;
            height: 54px;
            margin-top: 2px;
            background: color-mix(in srgb, var(--node-color, #3b82f6) 70%, #dbeafe 30%);
            border-radius: 999px;
        }
        .timeline-anchor {
            width: 10px;
            height: 10px;
            margin-top: -1px;
            border-radius: 999px;
            background: var(--node-color, #3b82f6);
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--node-color, #3b82f6) 30%, #cbd5e1 70%);
        }
        .timeline-year {
            font-size: 12px;
            color: #475569;
            margin-top: 8px;
            font-weight: 600;
        }
        .timeline-user {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
            margin-top: 4px;
            min-height: 30px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .timeline-segment {
            height: 8px;
            margin-top: 8px;
            border-radius: 2px;
            background: color-mix(in srgb, var(--node-color, #3b82f6) 78%, #ffffff 22%);
        }
        .timeline-preview {
            margin-top: 8px;
            border: 1px solid #dbe4f0;
            border-radius: 12px;
            background: #f8fbff;
            padding: 8px;
        }
        .timeline-preview-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 12px;
            color: #475569;
            margin-bottom: 8px;
        }
        .timeline-preview-grid span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 999px;
            border: 1px solid #dbe4f0;
            background: #fff;
            font-size: 11px;
            white-space: nowrap;
        }
        .timeline-preview-grid .entry-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 999px;
            border: 1px solid #dbe4f0;
            background: #fff;
            font-size: 11px;
            white-space: nowrap;
        }
        .timeline-preview-grid .entry-item strong {
            color: #334155;
        }
        .timeline-preview-btn {
            border: 0;
            background: transparent;
            color: #1d4ed8;
            border-radius: 6px;
            padding: 2px 4px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: underline;
        }
        .timeline-preview-btn:hover,
        .timeline-preview-btn:focus {
            color: #1e40af;
            background: rgba(37, 99, 235, .08);
            outline: none;
        }
        .timeline-inline-panel {
            border: 1px solid #dbe4f0;
            background: #f8fbff;
            border-radius: 12px;
            padding: 10px;
            position: sticky;
            top: 12px;
            min-height: 120px;
        }
        .timeline-inline-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .timeline-inline-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }
        .timeline-inline-subtitle {
            font-size: 12px;
            color: #64748b;
        }
        .timeline-inline-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 6px 10px;
            margin-bottom: 8px;
        }
        .timeline-inline-grid .entry-item {
            font-size: 12px;
        }
        .timeline-inline-open {
            border: 0;
            background: transparent;
            color: #1d4ed8;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            text-decoration: underline;
            padding: 2px 4px;
            border-radius: 6px;
        }
        .timeline-inline-open:hover,
        .timeline-inline-open:focus {
            background: rgba(37, 99, 235, .08);
            color: #1e40af;
            outline: none;
        }
        .timeline-badges {
            display: none;
        }
        .timeline-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid transparent;
        }
        .timeline-badge.kind-origin {
            background: #ecfdf5;
            color: #047857;
            border-color: #a7f3d0;
        }
        .timeline-badge.kind-purchase {
            background: #111827;
            color: #fff;
            border-color: #111827;
        }
        .timeline-badge.kind-transfer {
            background: #fff7ed;
            color: #c2410c;
            border-color: #fed7aa;
        }
        .timeline-badge.kind-assignment {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }
        .timeline-campus {
            font-size: 12px;
            color: #0f172a;
            background: #eff6ff;
            display: inline-flex;
            padding: 4px 8px;
            border-radius: 999px;
            margin-bottom: 8px;
        }
        .timeline-path {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 8px;
            font-size: 12px;
            color: #475569;
        }
        .timeline-path span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .timeline-meta {
            display: grid;
            gap: 5px;
            font-size: 12px;
            color: #475569;
        }
        .timeline-meta strong {
            color: #334155;
        }
        .graph-panel {
            position: sticky;
            top: 18px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
        }
        .graph-panel h2 {
            margin: 0 0 6px;
            font-size: 18px;
        }
        .graph-panel p {
            margin: 0 0 10px;
            color: #6b7280;
            font-size: 13px;
        }
        .graph-panel-grid {
            display: grid;
            gap: 10px;
        }
        .graph-panel-field {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 12px;
            background: #f8fafc;
        }
        .graph-panel-field strong {
            display: block;
            font-size: 12px;
            color: #475569;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .graph-panel-field span {
            display: block;
            font-size: 14px;
            color: #0f172a;
            line-height: 1.35;
            word-break: break-word;
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
        .hidden { display: none !important; }
        .history-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .5);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            z-index: 3000;
        }
        .history-modal-backdrop[hidden] {
            display: none !important;
        }
        .history-modal {
            width: min(920px, 100%);
            max-height: calc(100vh - 36px);
            overflow: auto;
            background: #fff;
            border: 1px solid #dbe4f0;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .24);
            padding: 14px;
        }
        .history-modal-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
        }
        .history-modal-close {
            border: 1px solid #dbe4f0;
            background: #f8fafc;
            color: #334155;
            border-radius: 999px;
            width: 34px;
            height: 34px;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
        }
        .history-modal-close:hover,
        .history-modal-close:focus {
            background: #eef2ff;
            border-color: #bfdbfe;
            outline: none;
        }
        .history-modal-sections {
            display: grid;
            gap: 8px;
        }
        .history-section {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #f8fafc;
            overflow: hidden;
        }
        .history-section > summary {
            list-style: none;
            cursor: pointer;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-weight: 700;
            color: #0f172a;
        }
        .history-section > summary::-webkit-details-marker {
            display: none;
        }
        .history-section > summary::after {
            content: '▾';
            color: #64748b;
            font-size: 12px;
        }
        .history-section[open] > summary::after {
            transform: rotate(180deg);
        }
        .history-section-body {
            padding: 0 12px 12px;
        }
        .history-section-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 8px 12px;
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
        @media (max-width: 1100px) {
            .hero-grid {
                grid-template-columns: 1fr;
            }

            .graph-panel {
                position: static;
            }

            .insights-grid {
                grid-template-columns: 1fr;
            }

            .timeline-main-grid {
                grid-template-columns: 1fr;
            }

            .timeline-inline-panel {
                position: static;
                margin-top: 8px;
            }
        }
        @media (max-width: 768px) {
            body {
                margin: 14px;
            }

            .histogram-row {
                grid-template-columns: 120px minmax(0, 1fr) 30px;
            }

            .timeline-event {
                width: 126px;
                flex-basis: 126px;
            }

            .timeline-preview-grid {
                display: flex;
                flex-wrap: wrap;
            }

            .route-step {
                min-width: 210px;
                flex-basis: 210px;
            }
        }
    </style>
</head>
<body>
<div class="wrap">
    @php
        $assignmentLog = collect($assignmentLog ?? []);
        $assignmentLogCount = $assignmentLog->count();

        $graphEvents = $assignmentLog->map(function (array $entry, int $index) use ($assignmentLogCount) {
            $campus = (string) ($entry['campus'] ?: 'Sin campus');
            $assignedUser = (string) ($entry['assigned_user'] ?: 'Sin asignar');
            $receivedBy = (string) ($entry['received_by'] ?: 'No capturado');
            $deliveryDate = (string) ($entry['assigned_at_label'] ?: $entry['at_label']);
            $description = (string) ($entry['description'] ?: '—');
            $reason = (string) (($entry['change_reason'] ?: $entry['interaction_note']) ?: 'Sin motivo capturado');
            $eventType = (string) ($entry['event_type'] ?? 'assignment');
            $kind = $eventType;

            $label = match ($eventType) {
                'purchase' => 'Compra inicial',
                'transfer' => 'Traslado',
                'repair' => 'Reparación',
                'repair_return' => 'Regreso de reparación',
                'reassignment' => 'Reasignación',
                'origin' => 'Origen',
                default => 'Asignación',
            };

            $color = match ($eventType) {
                'purchase' => '#111827',
                'transfer' => '#f59e0b',
                'repair' => '#ef4444',
                'repair_return' => '#8b5cf6',
                'reassignment' => '#3b82f6',
                'origin' => '#10b981',
                default => '#3b82f6',
            };

            if ($eventType !== 'purchase' && (!empty($entry['transfer_request_id']) || !empty($entry['transfer_from_branch']) || !empty($entry['transfer_to_branch']))) {
                $kind = 'transfer';
                $label = 'Traslado';
                $color = '#f59e0b';
            } elseif ($eventType !== 'purchase' && $index === $assignmentLogCount - 1) {
                $kind = 'origin';
                $label = 'Origen';
                $color = '#10b981';
            }

            return [
                'index' => $index,
                'kind' => $kind,
                'label' => $label,
                'color' => $color,
                'title' => $assignedUser,
                'campus' => $campus,
                'date' => $deliveryDate,
                'by' => (string) ($entry['by'] ?: 'Sistema'),
                'received_by' => $receivedBy,
                'serial_number' => (string) ($entry['serial_number'] ?: '—'),
                'description' => $description,
                'invoice_folio' => (string) ($entry['invoice_folio'] ?: '—'),
                'supplier' => (string) ($entry['supplier'] ?: '—'),
                'reason' => $reason,
                'transfer' => trim(collect([
                    !empty($entry['transfer_request_id']) ? 'Solicitud #' . $entry['transfer_request_id'] : null,
                    !empty($entry['transfer_priority']) ? 'Prioridad ' . strtoupper((string) $entry['transfer_priority']) : null,
                    !empty($entry['transfer_from_user']) || !empty($entry['transfer_to_user']) ? (($entry['transfer_from_user'] ?: 'N/A') . ' → ' . ($entry['transfer_to_user'] ?: 'N/A')) : null,
                    !empty($entry['transfer_from_branch']) || !empty($entry['transfer_to_branch']) ? (($entry['transfer_from_branch'] ?: 'N/A') . ' → ' . ($entry['transfer_to_branch'] ?: 'N/A')) : null,
                ])->filter()->join(' · ')),
            ];
        })->values();

        $campusHistogram = $graphEvents
            ->groupBy('campus')
            ->map(function ($items, string $campus) {
                return [
                    'campus' => $campus,
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $kindHistogram = $graphEvents
            ->groupBy('kind')
            ->map(function ($items, string $kind) {
                $label = match ($kind) {
                    'purchase' => 'Compra',
                    'origin' => 'Origen',
                    'transfer' => 'Traslados',
                    'repair' => 'Reparaciones',
                    'repair_return' => 'Regreso de reparación',
                    'reassignment' => 'Reasignaciones',
                    default => 'Asignaciones',
                };

                return [
                    'kind' => $kind,
                    'label' => $label,
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $routeSegments = [];
        $graphEventItems = $graphEvents->values();
        for ($i = 0; $i < $graphEventItems->count() - 1; $i++) {
            $current = $graphEventItems[$i];
            $next = $graphEventItems[$i + 1];
            $routeSegments[] = [
                'index' => $i + 1,
                'from' => $current['campus'],
                'to' => $next['campus'],
                'label' => $current['label'] . ' → ' . $next['label'],
                'user' => $next['title'],
                'date' => $next['date'],
                'kind' => $next['kind'],
                'color' => $next['color'],
                'reason' => $next['reason'],
                'serial_number' => $next['serial_number'],
                'invoice_folio' => $next['invoice_folio'],
                'received_by' => $next['received_by'],
                'by' => $next['by'],
            ];
        }

        $maxCampusCount = max(1, (int) ($campusHistogram->max('count') ?? 0));
    @endphp

    <div class="hero-grid">
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

        <div class="summary-card">
            <div class="summary-title">Resumen visual</div>
            <div class="summary-stat">
                <div>
                    <strong>Eventos</strong>
                    <span>{{ $assignmentLog->count() }}</span>
                </div>
                <div>
                    <strong>Origen</strong>
                    <span>{{ $graphEvents->first()['campus'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <strong>Estado actual</strong>
                    <span>{{ optional($asset->branch)->name ?: 'N/A' }}</span>
                </div>
                <div>
                    <strong>Responsable actual</strong>
                    <span>{{ $asset->assigned_user ?: 'Sin asignar' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="timeline-shell">
        <div class="insights-grid">
            <div class="route-card">
                <div class="route-head">
                    <div>
                        <h2>Camino de vida del equipo</h2>
                        <p>Cada bloque conecta el momento anterior con el siguiente para ver de un vistazo cómo se movió y quién lo recibió.</p>
                    </div>
                    <div class="histogram-foot">
                        <span class="histogram-pill"><span class="histogram-dot" style="background:#10b981"></span> Etapa inicial</span>
                        <span class="histogram-pill"><span class="histogram-dot" style="background:#f59e0b"></span> Movimiento</span>
                        <span class="histogram-pill"><span class="histogram-dot" style="background:#ef4444"></span> Servicio / reparación</span>
                    </div>
                </div>

                <div class="route-chain">
                    @foreach ($routeSegments as $segment)
                        <div
                            class="route-step"
                            tabindex="0"
                            role="button"
                            data-segment='@json($segment, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)'
                        >
                            <div class="route-step-title">
                                <span class="route-step-badge" style="background: {{ $segment['color'] }};">{{ $segment['index'] }}</span>
                                <h3>{{ $segment['label'] }}</h3>
                            </div>
                            <div class="route-campus">{{ $segment['from'] }} → {{ $segment['to'] }}</div>
                            <div class="route-meta">
                                <span><strong>Siguiente usuario:</strong> {{ $segment['user'] }}</span>
                                <span><strong>Momento:</strong> {{ $segment['date'] }}</span>
                                <span><strong>Hito:</strong> {{ $segment['reason'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="histogram-card">
                <div class="histogram-head">
                    <div>
                        <h2>Histograma de sedes y movimientos</h2>
                        <p>La barra más larga marca dónde se concentró más actividad y ayuda a leer la historia del equipo por etapa.</p>
                    </div>
                    <div class="histogram-foot">
                        @foreach ($kindHistogram as $kindItem)
                            <span class="histogram-pill">
                                <span class="histogram-dot" style="background: {{ $kindItem['kind'] === 'origin' ? '#10b981' : ($kindItem['kind'] === 'transfer' ? '#f59e0b' : '#2563eb') }};"></span>
                                {{ $kindItem['label'] }}: {{ $kindItem['count'] }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="histogram-grid">
                    @foreach ($campusHistogram as $campusItem)
                        @php
                            $width = (int) round(($campusItem['count'] / $maxCampusCount) * 100);
                        @endphp
                        <div class="histogram-row">
                            <div class="histogram-label">{{ $campusItem['campus'] }}</div>
                            <div class="histogram-track">
                                <div class="histogram-fill" style="width: {{ $width }}%;"></div>
                            </div>
                            <div class="histogram-count">{{ $campusItem['count'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="timeline-toolbar">
            <div>
                <h2>Línea de historia del equipo</h2>
                <div class="timeline-note">Haz clic en un punto para ver su resumen y usa el enlace para abrir el modal completo.</div>
            </div>
            <div class="timeline-note">Origen: Andares · Paso por Tepic · Estado actual: Corporativo</div>
        </div>

        <div class="timeline-main-grid">
            <div class="timeline-track">
                <div class="timeline-events">
                    @foreach ($graphEvents as $event)
                        @php
                            $eventIcon = match ($event['kind']) {
                                'purchase' => '🛒',
                                'transfer' => '↔',
                                'repair' => '🛠',
                                'repair_return' => '✅',
                                'reassignment' => '👤',
                                default => '●',
                            };

                            $eventYear = preg_match('/(\d{4})$/', (string) $event['date'], $yearMatch)
                                ? $yearMatch[1]
                                : (string) $event['date'];
                        @endphp
                        <div class="timeline-event" style="--node-color: {{ $event['color'] }};">
                            <div
                                class="timeline-node"
                                role="button"
                                tabindex="0"
                                data-event='@json($event, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)'
                            >
                                <div class="timeline-node-head">
                                    <div class="timeline-pin" aria-hidden="true">
                                        <div class="timeline-bubble">{{ $eventIcon }}</div>
                                        <div class="timeline-stem"></div>
                                        <div class="timeline-anchor"></div>
                                    </div>
                                    <div class="timeline-year">{{ $eventYear }}</div>
                                </div>
                                <div class="timeline-user">{{ $event['label'] }}</div>
                                <div class="timeline-segment" aria-hidden="true"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="timeline-inline-panel hidden" id="timeline-inline-panel" aria-live="polite">
                <div class="timeline-inline-panel-head">
                    <div>
                        <div class="timeline-inline-title" id="timeline-inline-title">Detalle rápido</div>
                        <div class="timeline-inline-subtitle" id="timeline-inline-subtitle">Selecciona un evento del carril.</div>
                    </div>
                    <button type="button" class="timeline-inline-open" id="timeline-inline-open">Ver detalle completo</button>
                </div>
                <div class="timeline-inline-grid" id="timeline-inline-grid"></div>
            </div>
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

<div class="history-modal-backdrop" id="history-modal-backdrop" hidden>
    <div class="history-modal" role="dialog" aria-modal="true" aria-labelledby="history-modal-title" aria-describedby="history-modal-subtitle">
        <div class="history-modal-head">
            <div>
                <div class="entry-title" id="history-modal-title">Detalle del evento</div>
                <div class="muted" id="history-modal-subtitle">Selecciona un nodo para ver la información completa.</div>
            </div>
            <button type="button" class="history-modal-close" id="history-modal-close" aria-label="Cerrar detalle">×</button>
        </div>
        <div class="muted nowrap" id="history-modal-date" style="margin-bottom:8px;"></div>
        <div class="history-modal-sections" id="history-modal-sections"></div>
    </div>
</div>

<script>
    (() => {
        const modalBackdrop = document.getElementById('history-modal-backdrop');
        const detailTitle = document.getElementById('history-modal-title');
        const detailSubtitle = document.getElementById('history-modal-subtitle');
        const detailDate = document.getElementById('history-modal-date');
        const detailSections = document.getElementById('history-modal-sections');
        const closeButton = document.getElementById('history-modal-close');
        const timelineInlinePanel = document.getElementById('timeline-inline-panel');
        const timelineInlineTitle = document.getElementById('timeline-inline-title');
        const timelineInlineSubtitle = document.getElementById('timeline-inline-subtitle');
        const timelineInlineGrid = document.getElementById('timeline-inline-grid');
        const timelineInlineOpen = document.getElementById('timeline-inline-open');
        let selectedTimelineEvent = null;

        const sectionMap = [
            {
                title: 'Usuario',
                open: true,
                fields: [
                    ['Usuario', 'title'],
                    ['Capturó', 'by'],
                    ['Recibe', 'received_by'],
                ],
            },
            {
                title: 'Movimiento',
                open: true,
                fields: [
                    ['Fecha', 'date'],
                    ['Evento', 'label'],
                    ['Campus', 'campus'],
                    ['Motivo', 'reason'],
                    ['Traspaso', 'transfer'],
                ],
            },
            {
                title: 'Activo',
                open: false,
                fields: [
                    ['Serie', 'serial_number'],
                    ['Descripción', 'description'],
                    ['Folio factura', 'invoice_folio'],
                    ['Proveedor', 'supplier'],
                ],
            },
        ];

        const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        }[char]));

        const openModal = () => {
            modalBackdrop.hidden = false;
            document.body.style.overflow = 'hidden';
        };

        const closeModal = () => {
            modalBackdrop.hidden = true;
            document.body.style.overflow = '';
        };

        const renderDetail = (payload, title, subtitle) => {
            openModal();
            detailTitle.textContent = title || 'Detalle del evento';
            detailSubtitle.textContent = subtitle || 'Selecciona un nodo para ver la información completa.';
            detailDate.textContent = payload.date || '';

            detailSections.innerHTML = sectionMap.map((section) => {
                const sectionContent = section.fields.map(([label, key]) => {
                    const value = payload[key] || '—';
                    return `<div class="entry-item"><strong>${escapeHtml(label)}:</strong> ${escapeHtml(value)}</div>`;
                }).join('');

                return `<details class="history-section" ${section.open ? 'open' : ''}><summary>${escapeHtml(section.title)}</summary><div class="history-section-body"><div class="history-section-grid">${sectionContent}</div></div></details>`;
            }).join('');
        };

        const collapseTimelineSelection = () => {
            document.querySelectorAll('.timeline-node').forEach((node) => {
                node.classList.remove('is-expanded');
            });
        };

        const renderInlineTimeline = (event) => {
            timelineInlinePanel.classList.remove('hidden');
            timelineInlineTitle.textContent = `${event.label || 'Evento'} · ${event.date || ''}`;
            timelineInlineSubtitle.textContent = `${event.campus || 'Sin campus'} · ${event.title || 'Sin usuario'}`;
            timelineInlineGrid.innerHTML = [
                ['Usuario', event.title],
                ['Campus', event.campus],
                ['Capturó', event.by],
                ['Recibe', event.received_by],
                ['Motivo', event.reason],
                ['Folio', event.invoice_folio],
            ].map(([label, value]) => `<div class="entry-item"><strong>${escapeHtml(label)}:</strong> ${escapeHtml(value || '—')}</div>`).join('');
        };

        document.querySelectorAll('.timeline-node').forEach((button) => {
            button.addEventListener('click', () => {
                const event = JSON.parse(button.dataset.event || '{}');
                selectedTimelineEvent = event;
                collapseTimelineSelection();
                button.classList.add('is-expanded');
                renderInlineTimeline(event);
            });

            button.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    button.click();
                }
            });
        });

        timelineInlineOpen.addEventListener('click', () => {
            if (!selectedTimelineEvent) return;
            renderDetail(
                selectedTimelineEvent,
                selectedTimelineEvent.title || 'Detalle del evento',
                `${selectedTimelineEvent.kind || 'evento'} · ${selectedTimelineEvent.campus || 'Sin campus'}`
            );
        });

        document.querySelectorAll('.route-step').forEach((step) => {
            const openStep = () => {
                const segment = JSON.parse(step.dataset.segment || '{}');
                renderDetail(
                    {
                        date: segment.date || '',
                        label: segment.label || 'Etapa del camino',
                        campus: `${segment.from || 'N/A'} → ${segment.to || 'N/A'}`,
                        title: segment.user || 'Sin usuario',
                        by: segment.by || 'Sistema',
                        received_by: segment.received_by || 'N/A',
                        serial_number: segment.serial_number || '—',
                        invoice_folio: segment.invoice_folio || '—',
                        reason: segment.reason || 'Sin motivo capturado',
                        transfer: segment.kind === 'transfer' ? 'Movimiento intersede' : (segment.kind === 'repair' || segment.kind === 'repair_return' ? 'Servicio técnico' : 'Asignación / uso'),
                        supplier: segment.kind === 'repair' || segment.kind === 'repair_return' ? 'Mantenimiento interno' : '—',
                        description: segment.label || '—',
                    },
                    `${segment.label || 'Etapa'} · ${segment.from || 'N/A'} → ${segment.to || 'N/A'}`,
                    `Camino ${segment.index || ''} · ${segment.kind || 'evento'}`
                );
            };

            step.addEventListener('click', openStep);
            step.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openStep();
                }
            });
        });

        closeButton.addEventListener('click', closeModal);
        modalBackdrop.addEventListener('click', (event) => {
            if (event.target === modalBackdrop) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modalBackdrop.hidden) {
                closeModal();
            }
        });

        closeModal();
    })();
</script>
</body>
</html>
