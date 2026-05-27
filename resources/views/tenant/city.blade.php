<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenantName }} | ITCity</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --brand-primary: {{ $tenantColors['primary'] ?? '#2563EB' }};
            --brand-secondary: {{ $tenantColors['secondary'] ?? '#0F172A' }};
            --brand-accent: {{ $tenantColors['accent'] ?? '#38BDF8' }};
            --brand-bg: {{ $tenantColors['background'] ?? '#F1F5F9' }};
            --brand-text: {{ $tenantColors['text'] ?? '#111827' }};
        }

        * { box-sizing: border-box; }

        body {
            background: linear-gradient(180deg, color-mix(in srgb, var(--brand-bg) 88%, #ffffff 12%) 0%, var(--brand-bg) 100%);
            margin: 0;
            font-family: inherit;
            min-height: 100vh;
            color: var(--brand-text);
            transition: transform .38s ease, opacity .3s ease, filter .3s ease;
        }

        body.prezi-enter {
            animation: cityPreziEnter .42s cubic-bezier(.22,.61,.36,1);
        }

        body.prezi-nav {
            transform-origin: var(--city-pz-origin-x, 50%) var(--city-pz-origin-y, 50%);
            transform: translate3d(var(--city-pz-shift-x, 0px), var(--city-pz-shift-y, 0px), 0) scale(var(--city-pz-scale, 1.08));
            opacity: .22;
            filter: blur(3px);
        }

        .city-topbar {
            position: sticky;
            top: 0;
            z-index: 1400;
            background: #fff;
            border-bottom: 1px solid #dbe4f0;
            padding: 6px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .city-topbar-left {
            font-size: .84rem;
            font-weight: 700;
            color: var(--brand-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .city-topbar-right {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
            justify-content: flex-end;
            min-width: 0;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .city-topbar-right::-webkit-scrollbar {
            display: none;
        }

        .city-user-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 9px;
            border: 1px solid #dbe4f0;
            border-radius: 999px;
            background: #f8fbff;
            font-size: .72rem;
            color: #334155;
            white-space: nowrap;
        }

        .city-nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            font-size: .72rem;
            line-height: 1;
            padding: .3rem .55rem;
            white-space: nowrap;
        }

        .city-nav-warning {
            color: #111827 !important;
        }

        .city-nav-warning:hover,
        .city-nav-warning:focus {
            color: #111827 !important;
        }

        .city-nav-label {
            display: inline;
        }

        .city-user-name {
            display: inline;
        }

        @keyframes cityPreziEnter {
            from {
                transform: scale(1.06);
                opacity: .25;
                filter: blur(5px);
            }
            to {
                transform: scale(1);
                opacity: 1;
                filter: blur(0);
            }
        }

        /* ── Hero Section ──────────────────────── */
        .hero {
            background: linear-gradient(135deg, var(--brand-secondary) 0%, var(--brand-primary) 100%);
            color: #fff;
            padding: 40px 24px;
            text-align: center;
            position: relative;
        }

        .hero-content { position: relative; z-index: 1; }

        .hero .h-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .hero .h-logo-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: color-mix(in srgb, var(--brand-accent) 24%, transparent);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            border: 1px solid color-mix(in srgb, var(--brand-accent) 42%, transparent);
        }

        .hero h1 {
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 6px;
            letter-spacing: -.01em;
        }

        .hero .h-sub {
            font-size: .9rem;
            color: #cbd5e1;
            margin: 0 0 16px;
        }

        .hero .h-btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ── Stats Row ─────────────────────────── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            padding: 0 24px;
            margin: -20px 0 32px;
            position: relative;
            z-index: 10;
            max-width: 1280px;
            margin-left: auto;
            margin-right: auto;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 12px rgba(15, 23, 42, .06);
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .stat-card .icon { font-size: 1.6rem; margin-bottom: 6px; }
        .stat-card .label { font-size: .7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 1px; }
        .stat-card .value { font-size: 1.8rem; font-weight: 800; color: #0f172a; line-height: 1; }

        /* ── Section Container ─────────────────── */
        .section-container {
            max-width: 1360px;
            margin: 40px auto 60px;
            padding: 0 24px;
        }

        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .section-head h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .section-head .actions {
            display: flex;
            gap: 8px;
        }

        /* ── Buildings Grid ────────────────────── */
        .buildings-grid {
            perspective: 1200px;
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 32px 28px;
        }

        @media (min-width: 640px) {
            .buildings-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 900px) {
            .buildings-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (min-width: 1140px) {
            .buildings-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (min-width: 1360px) {
            .buildings-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }

        .building-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
            transition: transform .45s ease, opacity .45s ease, filter .45s ease;
        }

        .building-wrapper.is-faded {
            opacity: .2;
            filter: blur(2px);
            transform: scale(.95);
        }

        .building-wrapper.is-entering {
            transform: scale(1.12) translateY(-6px);
        }

        .building-3d {
            width: 100%;
            max-width: 200px;
            height: 240px;
            position: relative;
            cursor: pointer;
            margin-bottom: 16px;
            transition: transform .5s cubic-bezier(.34, 1.56, .64, 1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .building-3d:hover {
            transform: rotateX(4deg) rotateY(-10deg) scale(1.05);
        }

        .building-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 14px 20px rgba(15, 23, 42, .25));
        }

        /* ── Info Card ─────────────────────────── */
        .building-info {
            width: 100%;
            background: #fff;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 12px rgba(15, 23, 42, .06);
            border: 1px solid #e2e8f0;
            text-align: center;
            transition: all .3s;
        }

        .building-wrapper:hover .building-info {
            box-shadow: 0 6px 20px rgba(15, 23, 42, .12);
            border-color: #2563eb;
        }

        #cityTransitionLayer {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 50% 45%, rgba(37, 99, 235, .35), rgba(15, 23, 42, .94));
            color: #fff;
            backdrop-filter: blur(6px);
        }

        #cityTransitionLayer.active {
            display: flex;
            animation: fadeInLayer .28s ease;
        }

        #cityPreziMask {
            position: fixed;
            inset: 0;
            z-index: 9997;
            pointer-events: none;
            opacity: 0;
            transition: opacity .28s ease;
            background: radial-gradient(circle at var(--city-pz-origin-x, 50%) var(--city-pz-origin-y, 50%), rgba(59,130,246,.26), rgba(15,23,42,.92));
        }

        body.prezi-nav #cityPreziMask {
            opacity: 1;
        }

        .transition-card {
            min-width: 300px;
            max-width: 84vw;
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 14px;
            padding: 16px 18px;
            background: rgba(15, 23, 42, .55);
            box-shadow: 0 14px 34px rgba(2, 6, 23, .45);
            transform: scale(.95);
            animation: zoomLayer .35s ease forwards;
        }

        .transition-title {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #93c5fd;
            margin-bottom: 4px;
        }

        .transition-branch {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .transition-sub {
            margin: 0;
            font-size: .85rem;
            color: #cbd5e1;
        }

        @keyframes fadeInLayer {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes zoomLayer {
            from { transform: scale(.9); opacity: .6; }
            to { transform: scale(1); opacity: 1; }
        }

        .bi-title {
            font-weight: 700;
            font-size: .95rem;
            color: #0f172a;
            margin: 0 0 3px;
        }

        .bi-location {
            font-size: .7rem;
            color: #94a3b8;
            margin: 0 0 10px;
            min-height: 16px;
        }

        .bi-stats {
            display: flex;
            justify-content: space-around;
            padding-top: 10px;
            border-top: 1px solid #f1f5f9;
        }

        .bi-stat {
            text-align: center;
        }

        .bi-stat-label {
            font-size: .65rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .bi-stat-value {
            font-size: 1.1rem;
            font-weight: 800;
            color: #2563eb;
            margin-top: 2px;
        }

        .bi-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .7rem;
            color: #059669;
            font-weight: 600;
            margin-top: 8px;
        }

        .bi-status::before {
            content: '●';
            font-size: .6rem;
            color: #10b981;
        }

        /* ── Empty State ──────────────────────── */
        .empty-state {
            text-align: center;
            padding: 100px 40px;
        }

        .empty-state .icon {
            font-size: 4rem;
            margin-bottom: 16px;
            opacity: .3;
        }

        .empty-state h3 {
            font-size: 1.3rem;
            color: #64748b;
            margin: 0 0 8px;
        }

        .empty-state p {
            color: #94a3b8;
            margin: 0 0 24px;
        }

        @media (max-width: 768px) {
            .building-3d { max-width: 160px; height: 180px; }
            .hero h1 { font-size: 1.5rem; }
        }

        @media (max-width: 576px) {
            .city-topbar {
                padding: 5px 10px;
                gap: 6px;
            }

            .city-topbar-left {
                font-size: .78rem;
            }

            .city-user-name,
            .city-nav-label {
                display: none;
            }

            .city-user-pill {
                padding: 4px 8px;
                gap: 0;
            }

            .city-nav-btn {
                padding: .28rem .42rem;
                min-width: 2.1rem;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
@php
    $cityMainUrl = url('/');
@endphp

<div class="city-topbar">
    <div class="city-topbar-left">{{ $tenantName }}</div>
    <div class="city-topbar-right">
        @auth
        <span class="city-user-pill" title="Usuario logueado">
            <i class="bi bi-person-circle" aria-hidden="true"></i>
            <span class="city-user-name">{{ auth()->user()->name }}</span>
        </span>
        @endauth
        <a href="/admin#crud-branches" class="btn btn-sm btn-outline-warning city-nav-btn city-nav-warning">
            <i class="bi bi-gear-fill" aria-hidden="true"></i>
            <span class="city-nav-label">Administrar</span>
        </a>
        @auth
        @if (auth()->user()->hasTenantPermission('tenant.admin'))
        <a href="/admin/branding" class="btn btn-sm btn-outline-primary city-nav-btn">
            <i class="bi bi-palette-fill" aria-hidden="true"></i>
            <span class="city-nav-label">Branding</span>
        </a>
        @endif
        @endauth
        <a href="#campus" class="btn btn-sm btn-outline-info city-nav-btn">
            <i class="bi bi-compass" aria-hidden="true"></i>
            <span class="city-nav-label">Explorar</span>
        </a>
        @auth
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger city-nav-btn">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                <span class="city-nav-label">Cerrar sesión</span>
            </button>
        </form>
        @endauth
    </div>
</div>

<!-- ── Hero Section ──────────────────────────────── -->
<div class="hero">
    <div class="hero-content">
        <div class="h-logo">
            <div class="h-logo-icon">🏙</div>
            <div style="text-align: left">
                <div style="font-size: .8rem; color: #94a3b8">GESTIÓN DE INFRAESTRUCTURA</div>
                <div style="font-size: 1.05rem; font-weight: 700">{{ $tenantName }}</div>
            </div>
            @if (!empty($tenantLogo))
                <img src="{{ $tenantLogo }}" alt="Logo" style="width: 36px; height: 36px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(255,255,255,.2); margin-left: auto;">
            @endif
        </div>
        <h1>{{ $tenantName }}</h1>
        <p class="h-sub">Campus interconectado · Monitoreo centralizado · Control integral</p>
    </div>
</div>

<!-- ── Stats Overview ────────────────────────────── -->
<div class="stats-row">
    <div class="stat-card">
        <div class="icon">🏢</div>
        <div class="label">Sedes</div>
        <div class="value">{{ $branches->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="icon">🖥️</div>
        <div class="label">Nodos</div>
        <div class="value">@php echo $branches->sum(fn($b) => $b->nodes_count); @endphp</div>
    </div>
    <div class="stat-card">
        <div class="icon">📡</div>
        <div class="label">Activos</div>
        <div class="value">@php echo $branches->sum(fn($b) => $b->nodes_count); @endphp</div>
    </div>
    <div class="stat-card">
        <div class="icon">✅</div>
        <div class="label">Salud</div>
        <div class="value">99%</div>
    </div>
</div>

<!-- ── Campus Buildings ──────────────────────────── -->
<div class="section-container">
    <div id="campus" class="section-head">
        <h2>Campus de sucursales</h2>
        <div class="actions">
            <a href="/admin#crud-branches" class="btn btn-sm btn-primary">+ Nueva sede</a>
        </div>
    </div>

    @if ($branches->isEmpty())
        <div class="empty-state">
            <div class="icon">📍</div>
            <h3>Sin sedes registradas</h3>
            <p>Crea tu primera sucursal desde el panel de administración</p>
            <a href="/admin#crud-branches" class="btn btn-primary">Ir a Administración</a>
        </div>
    @else
        <div class="buildings-grid">
            @foreach ($branches as $branch)
                <a
                    href="{{ url('/sede/' . $branch->id) }}?drill=1"
                    class="text-decoration-none text-dark js-branch-entry"
                    data-branch-name="{{ $branch->name }}"
                    style="height: 100%">
                    <div class="building-wrapper">
                        <div class="building-3d">
                            <img src="{{ asset('images/building-modern.svg') }}" alt="Edificio" class="building-image">
                        </div>

                        <div class="building-info">
                            <h3 class="bi-title">{{ Str::limit($branch->name, 25) }}</h3>
                            <div class="bi-location">
                                @if ($branch->city || $branch->state)
                                    {{ $branch->city ?? '' }}{{ ($branch->city && $branch->state) ? ', ' : '' }}{{ $branch->state ?? '' }}
                                @else
                                    <span style="opacity: .5">Ubicación</span>
                                @endif
                            </div>

                            <div class="bi-stats">
                                <div class="bi-stat">
                                    <div class="bi-stat-label">Nodos</div>
                                    <div class="bi-stat-value">{{ $branch->nodes_count }}</div>
                                </div>
                                <div class="bi-stat">
                                    <div class="bi-stat-label">Estado</div>
                                    <div style="font-size: 1rem; margin-top: 2px">🟢</div>
                                </div>
                            </div>

                            <div class="bi-status">Online</div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

<div id="cityTransitionLayer" aria-hidden="true">
    <div class="transition-card">
        <div class="transition-title">Drill-down visual</div>
        <h3 id="transitionBranchName" class="transition-branch">Sucursal</h3>
        <p class="transition-sub">Cargando niveles de infraestructura…</p>
    </div>
</div>
<div id="cityPreziMask" aria-hidden="true"></div>

<script>
    window.itcityCityGoBackWithFallback = function (fallbackUrl) {
        if (window.history.length > 1) {
            window.history.back();
            return;
        }

        if (fallbackUrl) {
            window.location.href = fallbackUrl;
        }
    };

    if (!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
        requestAnimationFrame(() => {
            document.body.classList.add('prezi-enter');
            setTimeout(() => document.body.classList.remove('prezi-enter'), 450);
        });
    }

    document.querySelectorAll('a[href="#campus"]').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            document.getElementById('campus').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    const transitionLayer = document.getElementById('cityTransitionLayer');
    const transitionBranchName = document.getElementById('transitionBranchName');
    const branchLinks = document.querySelectorAll('.js-branch-entry');

    const resetCityTransitionState = () => {
        document.body.classList.remove('prezi-nav', 'prezi-enter');

        const root = document.documentElement;
        root.style.removeProperty('--city-pz-origin-x');
        root.style.removeProperty('--city-pz-origin-y');
        root.style.removeProperty('--city-pz-shift-x');
        root.style.removeProperty('--city-pz-shift-y');
        root.style.removeProperty('--city-pz-scale');

        if (transitionLayer) {
            transitionLayer.classList.remove('active');
        }

        document.querySelectorAll('.building-wrapper').forEach((wrapper) => {
            wrapper.classList.remove('is-entering', 'is-faded');
        });
    };

    // Ensure the city page returns to a clean visual state after browser Back/Forward restores.
    resetCityTransitionState();
    window.addEventListener('pageshow', resetCityTransitionState);

    branchLinks.forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();

            const wrapper = link.querySelector('.building-wrapper');
            const branchName = link.getAttribute('data-branch-name') || 'Sucursal';
            transitionBranchName.textContent = branchName;

            branchLinks.forEach((other) => {
                const otherWrapper = other.querySelector('.building-wrapper');
                if (!otherWrapper) return;
                if (other === link) {
                    otherWrapper.classList.add('is-entering');
                } else {
                    otherWrapper.classList.add('is-faded');
                }
            });

            setTimeout(() => transitionLayer.classList.add('active'), 180);
            setTimeout(() => { window.location.href = link.href; }, 620);
        });
    });

    const isReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const isInternalNavigable = (link) => {
        if (!link || !link.href) return false;
        if (link.target === '_blank' || link.hasAttribute('download')) return false;
        const href = link.getAttribute('href') || '';
        if (href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return false;
        return new URL(link.href, window.location.origin).origin === window.location.origin;
    };

    document.addEventListener('click', (event) => {
        if (isReduced) return;
        if (event.defaultPrevented || event.button !== 0) return;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const link = event.target.closest('a[href]');
        if (!isInternalNavigable(link)) return;
        if (link.classList.contains('js-branch-entry')) return;

        const url = new URL(link.href, window.location.origin);
        if (url.href === window.location.href) return;

        event.preventDefault();

        const rect = link.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        const root = document.documentElement;
        root.style.setProperty('--city-pz-origin-x', `${centerX}px`);
        root.style.setProperty('--city-pz-origin-y', `${centerY}px`);
        root.style.setProperty('--city-pz-shift-x', `${(window.innerWidth / 2 - centerX) * 0.08}px`);
        root.style.setProperty('--city-pz-shift-y', `${(window.innerHeight / 2 - centerY) * 0.08}px`);
        root.style.setProperty('--city-pz-scale', '1.11');

        document.body.classList.add('prezi-nav');
        setTimeout(() => { window.location.href = url.href; }, 300);
    });
</script>

</body>
</html>
