<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ITCity') | ITCity</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --sb-w: 238px;
            --tb-h: 58px;
            --navy: #0f172a;
            --navy2: #1e293b;
            --accent: #2563eb;
            --bg: #f1f5f9;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--bg);
            margin: 0;
            font-family: inherit;
        }

        /* ── Sidebar ───────────────────────────── */
        #ic-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sb-w);
            height: 100vh;
            background: var(--navy);
            display: flex;
            flex-direction: column;
            z-index: 200;
            overflow-y: auto;
        }

        .sb-logo {
            padding: 18px 20px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
        }

        .sb-logo a {
            color: #fff;
            text-decoration: none;
            font-size: 1.08rem;
            font-weight: 700;
            letter-spacing: .02em;
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .sb-logo .sb-dot {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }

        .sb-group-title {
            padding: 18px 20px 5px;
            color: #475569;
            font-size: .67rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .sb-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            color: #94a3b8;
            text-decoration: none;
            font-size: .875rem;
            transition: background .14s, color .14s;
            border-left: 3px solid transparent;
        }

        .sb-link:hover {
            background: rgba(255, 255, 255, .05);
            color: #e2e8f0;
        }

        .sb-link.active {
            background: rgba(37, 99, 235, .13);
            color: #fff;
            border-left-color: var(--accent);
        }

        .sb-link svg { flex-shrink: 0; opacity: .65; }
        .sb-link.active svg { opacity: 1; }

        .sb-link .sb-sub {
            font-size: .75rem;
            color: #64748b;
            margin-left: auto;
        }

        /* ── Main ──────────────────────────────── */
        #ic-main {
            margin-left: var(--sb-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transform-origin: var(--pz-origin-x, 50%) var(--pz-origin-y, 50%);
            transition: transform .42s cubic-bezier(.22,.61,.36,1), opacity .34s ease, filter .34s ease;
        }

        body.prezi-enter #ic-main {
            animation: preziEnter .42s cubic-bezier(.22,.61,.36,1);
        }

        body.prezi-nav #ic-main {
            transform: translate3d(var(--pz-shift-x, 0px), var(--pz-shift-y, 0px), 0) scale(var(--pz-scale, 1.12));
            opacity: .2;
            filter: blur(3px);
        }

        #preziOverlay {
            position: fixed;
            inset: 0;
            z-index: 9998;
            pointer-events: none;
            opacity: 0;
            transition: opacity .3s ease;
            background: radial-gradient(circle at var(--pz-origin-x, 50%) var(--pz-origin-y, 50%), rgba(59,130,246,.22), rgba(15,23,42,.94));
        }

        body.prezi-nav #preziOverlay {
            opacity: 1;
        }

        @keyframes preziEnter {
            from {
                transform: scale(1.06);
                opacity: .2;
                filter: blur(5px);
            }
            to {
                transform: scale(1);
                opacity: 1;
                filter: blur(0);
            }
        }

        /* ── Topbar ────────────────────────────── */
        #ic-topbar {
            height: var(--tb-h);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .tb-title {
            flex: 1;
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
        }

        .tb-breadcrumb {
            font-size: .8rem;
            color: #94a3b8;
        }

        .tb-breadcrumb a { color: #64748b; text-decoration: none; }
        .tb-breadcrumb a:hover { color: #2563eb; }
        .tb-breadcrumb span { margin: 0 5px; }

        /* ── Content ───────────────────────────── */
        #ic-content {
            flex: 1;
            padding: 28px 26px;
        }

        /* ── Status badges ─────────────────────── */
        .sb-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .sb-badge::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
            display: inline-block;
        }

        .sb-badge.active   { background: #dcfce7; color: #15803d; }
        .sb-badge.warning  { background: #fef9c3; color: #92400e; }
        .sb-badge.error    { background: #fee2e2; color: #b91c1c; }
        .sb-badge.inactive { background: #f1f5f9; color: #64748b; }
        .sb-badge.default  { background: #e0e7ff; color: #3730a3; }

        /* ── Cards ─────────────────────────────── */
        .ic-card {
            background: #fff !important;
            border: 0 !important;
            border-radius: 14px !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .06), 0 4px 18px rgba(15, 23, 42, .05) !important;
        }

        .ic-card > .card-header {
            background: #fff !important;
            border-bottom: 1px solid #f1f5f9 !important;
            border-radius: 14px 14px 0 0 !important;
            padding: 14px 20px !important;
            font-weight: 700 !important;
            font-size: .88rem;
            color: #0f172a;
            letter-spacing: .01em;
        }

        .ic-card.ic-card--accent > .card-header {
            background: linear-gradient(90deg, #2563eb, #1d4ed8) !important;
            color: #fff !important;
            border-bottom: 0 !important;
        }

        /* ── Stat cards ────────────────────────── */
        .ic-stat {
            background: #fff;
            border-radius: 14px;
            padding: 22px 22px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .06), 0 4px 18px rgba(15, 23, 42, .05);
            display: flex;
            align-items: center;
            gap: 18px;
            height: 100%;
        }

        .ic-stat .st-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .ic-stat .st-label {
            font-size: .7rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 3px;
        }

        .ic-stat .st-value {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .ic-stat .st-sub {
            font-size: .75rem;
            color: #94a3b8;
            margin-top: 3px;
        }

        /* ── Metrics ───────────────────────────── */
        .metric-row { margin-bottom: 14px; }

        .metric-row .metric-head {
            display: flex;
            justify-content: space-between;
            font-size: .79rem;
            color: #64748b;
            margin-bottom: 4px;
        }

        .metric-row .metric-head strong { color: #0f172a; }

        .metric-row .progress {
            height: 7px;
            border-radius: 999px;
            background: #f1f5f9;
        }

        /* ── Tables ────────────────────────────── */
        .ic-table th {
            font-size: .73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94a3b8;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            padding: 10px 16px;
        }

        .ic-table td {
            font-size: .875rem;
            vertical-align: middle;
            padding: 10px 16px;
        }

        /* ── Misc ──────────────────────────────── */
        .code-box {
            font-family: Consolas, monospace;
            font-size: .84rem;
            white-space: pre-wrap;
        }

        .mini-table td,
        .mini-table th { vertical-align: middle; font-size: .9rem; }

        .table .badge,
        .mini-table .badge,
        .dataTable .badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .28rem .58rem;
            border-radius: 999px;
            font-size: .74rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: .01em;
            border: 1px solid transparent;
            text-shadow: none;
        }

        .table .badge.bg-primary,
        .mini-table .badge.bg-primary,
        .dataTable .badge.bg-primary,
        .table .badge.text-bg-primary,
        .mini-table .badge.text-bg-primary,
        .dataTable .badge.text-bg-primary {
            background: #dbeafe !important;
            color: #1e3a8a !important;
            border-color: #93c5fd;
        }

        .table .badge.bg-secondary,
        .mini-table .badge.bg-secondary,
        .dataTable .badge.bg-secondary,
        .table .badge.text-bg-secondary,
        .mini-table .badge.text-bg-secondary,
        .dataTable .badge.text-bg-secondary {
            background: #e2e8f0 !important;
            color: #334155 !important;
            border-color: #cbd5e1;
        }

        .table .badge.bg-success,
        .mini-table .badge.bg-success,
        .dataTable .badge.bg-success,
        .table .badge.text-bg-success,
        .mini-table .badge.text-bg-success,
        .dataTable .badge.text-bg-success {
            background: #dcfce7 !important;
            color: #166534 !important;
            border-color: #86efac;
        }

        .table .badge.bg-danger,
        .mini-table .badge.bg-danger,
        .dataTable .badge.bg-danger,
        .table .badge.text-bg-danger,
        .mini-table .badge.text-bg-danger,
        .dataTable .badge.text-bg-danger {
            background: #fee2e2 !important;
            color: #991b1b !important;
            border-color: #fca5a5;
        }

        .table .badge.bg-warning,
        .mini-table .badge.bg-warning,
        .dataTable .badge.bg-warning,
        .table .badge.text-bg-warning,
        .mini-table .badge.text-bg-warning,
        .dataTable .badge.text-bg-warning {
            background: #fef3c7 !important;
            color: #92400e !important;
            border-color: #fcd34d;
        }

        .table .badge.bg-info,
        .mini-table .badge.bg-info,
        .dataTable .badge.bg-info,
        .table .badge.text-bg-info,
        .mini-table .badge.text-bg-info,
        .dataTable .badge.text-bg-info {
            background: #cffafe !important;
            color: #155e75 !important;
            border-color: #67e8f9;
        }

        .table .badge.bg-light,
        .mini-table .badge.bg-light,
        .dataTable .badge.bg-light,
        .table .badge.text-bg-light,
        .mini-table .badge.text-bg-light,
        .dataTable .badge.text-bg-light {
            background: #f8fafc !important;
            color: #334155 !important;
            border-color: #cbd5e1;
        }

        .table .badge.bg-dark,
        .mini-table .badge.bg-dark,
        .dataTable .badge.bg-dark,
        .table .badge.text-bg-dark,
        .mini-table .badge.text-bg-dark,
        .dataTable .badge.text-bg-dark {
            background: #e2e8f0 !important;
            color: #0f172a !important;
            border-color: #94a3b8;
        }

        .section-card {
            border: 0;
            border-radius: 14px !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .06), 0 4px 18px rgba(15, 23, 42, .05) !important;
        }

        .section-card > .card-header {
            background: #fff !important;
            border-bottom: 1px solid #f1f5f9 !important;
            border-radius: 14px 14px 0 0 !important;
            padding: 14px 20px !important;
            font-weight: 700 !important;
            font-size: .88rem;
            color: #0f172a;
        }

        .sticky-form { position: sticky; top: calc(var(--tb-h) + 16px); }

        @media (max-width: 768px) {
            #ic-sidebar { transform: translateX(-100%); }
            #ic-main    { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- ── Sidebar ──────────────────────────────── --}}
<aside id="ic-sidebar">
    <div class="sb-logo">
        <a href="{{ url('/') }}">
            <span class="sb-dot">🏙</span>
            <span>ITCity</span>
        </a>
    </div>

    @php
        $path = request()->path();
        $navCity    = ($path === '/'  || $path === '') ? 'active' : '';
        $navBranch  = (str_starts_with($path, 'sede/') && !str_ends_with($path, '/red')) ? 'active' : '';
        $navAdmin   = str_starts_with($path, 'admin') ? 'active' : '';
        $navTopo    = ($path === 'red') ? 'active' : '';
    @endphp

    <div class="sb-group-title">Portal</div>

    <a href="{{ url('/') }}" class="sb-link {{ $navCity }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
        </svg>
        Ciudad
    </a>

    <a href="{{ url('/red') }}" class="sb-link {{ $navTopo }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
            <path d="M4.5 2A2.5 2.5 0 0 0 2 4.5v7A2.5 2.5 0 0 0 4.5 14h7a2.5 2.5 0 0 0 2.5-2.5v-7A2.5 2.5 0 0 0 11.5 2h-7zM8 5a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm-3 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm6 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2zM5 10h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1z"/>
        </svg>
        Topología Global
    </a>

    @isset($branch)
    <div class="sb-group-title">Sede</div>
    <a href="{{ url('/sede/' . $branch->id) }}" class="sb-link {{ $navBranch }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
            <path d="M3 0h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V1a1 1 0 0 1 1-1zm0 1v14h10V1H3z"/>
            <path d="M5 4a1 1 0 0 0 0 2h1a1 1 0 0 0 0-2H5zm5 0a1 1 0 0 0 0 2h1a1 1 0 0 0 0-2h-1zm-5 4a1 1 0 0 0 0 2h1a1 1 0 0 0 0-2H5zm5 0a1 1 0 0 0 0 2h1a1 1 0 0 0 0-2h-1z"/>
        </svg>
        {{ Str::limit($branch->name, 22) }}
    </a>
    @endisset

    @isset($node)
    @php $nb = $node->branch; @endphp
    @if ($nb)
    <div class="sb-group-title">Sede</div>
    <a href="{{ url('/sede/' . $nb->id) }}" class="sb-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
            <path d="M3 0h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V1a1 1 0 0 1 1-1zm0 1v14h10V1H3z"/>
        </svg>
        {{ Str::limit($nb->name, 22) }}
    </a>
    <div class="sb-group-title">Nodo</div>
    <a href="{{ url('/nodos/' . $node->id) }}" class="sb-link active">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
            <path d="M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5zm0 1h6a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1z"/>
        </svg>
        {{ Str::limit($node->name, 22) }}
    </a>
    @endif
    @endisset

    @php
        $requestedAdminBranchId = max(0, (int) request()->integer('branch_id', 0));
        $portalContextBranchId = max(0, (int) session('tenant_portal_context_branch_id', 0));
        $sessionAdminBranchId = max(0, (int) session('tenant_admin_context_branch_id', 0));
        $adminBranchContextId = null;
        if ($requestedAdminBranchId > 0) {
            $adminBranchContextId = $requestedAdminBranchId;
        } elseif (isset($currentContextBranchId) && (int) $currentContextBranchId > 0) {
            $adminBranchContextId = (int) $currentContextBranchId;
        } elseif (isset($branch)) {
            $adminBranchContextId = $branch->id;
        } elseif (isset($node)) {
            $adminBranchContextId = $node->branch_id ?? null;
        } elseif ($sessionAdminBranchId > 0) {
            $adminBranchContextId = $sessionAdminBranchId;
        } elseif ($portalContextBranchId > 0) {
            $adminBranchContextId = $portalContextBranchId;
        }

        $adminPanelUrl = $adminBranchContextId ? url('/admin?branch_id=' . $adminBranchContextId) : url('/admin');
        $adminUsersUrl = $adminBranchContextId ? url('/admin/users?branch_id=' . $adminBranchContextId) : url('/admin/users');
    @endphp

    <div class="sb-group-title">Administración</div>
    <a href="{{ $adminPanelUrl }}" class="sb-link {{ $navAdmin }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
            <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/>
        </svg>
        Panel Admin
    </a>
    @auth
    @if (auth()->user()->isAdmin())
    <a href="{{ $adminUsersUrl }}" class="sb-link {{ str_starts_with($path, 'admin/users') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
        </svg>
        Usuarios
    </a>
    @endif
    @endauth

    <div style="flex:1"></div>
    <div style="padding:14px 20px;border-top:1px solid rgba(255,255,255,.07)">
        <div style="font-size:.7rem;color:#475569;font-weight:600;letter-spacing:.04em">ITCITY PLATFORM</div>
        <div style="font-size:.68rem;color:#334155;">Tenant Management v1.0</div>
    </div>
</aside>

{{-- ── Main ──────────────────────────────────── --}}
<div id="ic-main">
    <div id="ic-topbar">
        <div class="tb-title">@yield('page_title', 'ITCity')</div>
        <div class="d-flex gap-2 align-items-center">
            @yield('topbar_actions')
        </div>
    </div>
    <div id="ic-content">
        @yield('content')
    </div>
</div>

<div id="preziOverlay" aria-hidden="true"></div>

<script>
    (function () {
        const ADMIN_CONTEXT_STORAGE_KEY = 'tenant_admin_context_branch_id';
        const readPositiveInt = (value) => {
            const normalized = String(value ?? '').trim();
            if (!/^\d+$/.test(normalized)) return null;
            const parsed = Number.parseInt(normalized, 10);
            return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
        };

        const currentUrlForContext = new URL(window.location.href);
        const queryContextBranchId = readPositiveInt(currentUrlForContext.searchParams.get('branch_id'));
        const pathMatch = currentUrlForContext.pathname.match(/^\/sede\/(\d+)$/);
        const pathContextBranchId = readPositiveInt(pathMatch ? pathMatch[1] : null);
        const linkContextBranchId = (() => {
            const seededLink = document.querySelector('a[href*="/admin?branch_id="]');
            if (!seededLink) return null;

            try {
                const seededUrl = new URL(seededLink.href, window.location.origin);
                return readPositiveInt(seededUrl.searchParams.get('branch_id'));
            } catch (error) {
                return null;
            }
        })();

        const resolvedContextBranchId = queryContextBranchId ?? pathContextBranchId ?? linkContextBranchId;
        if (resolvedContextBranchId !== null) {
            sessionStorage.setItem(ADMIN_CONTEXT_STORAGE_KEY, String(resolvedContextBranchId));
        }

        const currentUrl = new URL(window.location.href);
        const isDirectFloorPlanRoute = currentUrl.pathname.includes('/admin/floor-plans/');
        if (currentUrl.searchParams.has('floor_plan') || isDirectFloorPlanRoute) {
            return;
        }

        const overlayEl = document.getElementById('preziOverlay');
        const mainEl = document.getElementById('ic-main');
        const beginWhiteTransition = () => {
            document.body.classList.remove('prezi-nav', 'prezi-enter');
            document.body.style.background = '#fff';
            if (mainEl) {
                mainEl.style.transition = 'opacity .08s ease';
                mainEl.style.opacity = '0';
                mainEl.style.filter = 'none';
                mainEl.style.transform = 'none';
            }
            if (overlayEl) {
                overlayEl.style.transition = 'opacity .08s ease';
                overlayEl.style.background = '#fff';
                overlayEl.style.opacity = '1';
            }
        };

        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const clearPreziState = () => {
            document.body.classList.remove('prezi-nav', 'prezi-enter');
            const root = document.documentElement;
            root.style.removeProperty('--pz-origin-x');
            root.style.removeProperty('--pz-origin-y');
            root.style.removeProperty('--pz-shift-x');
            root.style.removeProperty('--pz-shift-y');
            root.style.removeProperty('--pz-scale');
        };

        clearPreziState();
        window.addEventListener('pageshow', clearPreziState);

        requestAnimationFrame(function () {
            document.body.classList.add('prezi-enter');
            setTimeout(function () {
                document.body.classList.remove('prezi-enter');
            }, 450);
        });

        const isInternalNavigable = (link) => {
            if (!link || !link.href) return false;
            if (link.target === '_blank' || link.hasAttribute('download')) return false;
            if (link.getAttribute('href').startsWith('#')) return false;
            if (link.getAttribute('href').startsWith('mailto:') || link.getAttribute('href').startsWith('tel:')) return false;
            const url = new URL(link.href, window.location.origin);
            if (url.origin !== window.location.origin) return false;

            const current = new URL(window.location.href);
            const samePathAndQuery = url.pathname === current.pathname && url.search === current.search;
            if (samePathAndQuery && url.hash !== current.hash) {
                return false;
            }

            return true;
        };

        document.addEventListener('click', function (event) {
            if (event.defaultPrevented || event.button !== 0) return;
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

            const link = event.target.closest('a[href]');
            if (!isInternalNavigable(link)) return;

            const url = new URL(link.href, window.location.origin);
            if (url.href === window.location.href) return;

            const isFloorPlanTarget = url.searchParams.has('floor_plan') || url.pathname.includes('/admin/floor-plans/');
            if (isFloorPlanTarget) {
                event.preventDefault();
                beginWhiteTransition();
                setTimeout(function () {
                    window.location.href = url.href;
                }, 20);
                return;
            }

            event.preventDefault();

            const rect = link.getBoundingClientRect();
            const centerX = rect.left + (rect.width / 2);
            const centerY = rect.top + (rect.height / 2);

            const root = document.documentElement;
            root.style.setProperty('--pz-origin-x', `${centerX}px`);
            root.style.setProperty('--pz-origin-y', `${centerY}px`);

            const dx = (window.innerWidth / 2 - centerX) * 0.08;
            const dy = (window.innerHeight / 2 - centerY) * 0.08;
            root.style.setProperty('--pz-shift-x', `${dx}px`);
            root.style.setProperty('--pz-shift-y', `${dy}px`);
            root.style.setProperty('--pz-scale', '1.13');

            document.body.classList.add('prezi-nav');
            setTimeout(function () {
                window.location.href = url.href;
            }, 320);
        });
    })();
</script>

@stack('scripts')
</body>
</html>
