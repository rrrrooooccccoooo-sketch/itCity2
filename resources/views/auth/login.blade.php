<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Acceso · ITCity</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-1: #0f172a;
            --bg-2: #1e1b4b;
            --bg-3: #0f766e;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --accent: #38bdf8;
            --accent-2: #6366f1;
            --panel-border: rgba(255,255,255,.2);
            --panel-bg: rgba(255,255,255,.1);
        }

        html, body {
            min-height: 100%;
            font-family: 'Nunito', system-ui, -apple-system, sans-serif;
            color: var(--text);
            background: radial-gradient(circle at 14% 20%, rgba(56,189,248,.24) 0%, transparent 35%),
                        radial-gradient(circle at 85% 80%, rgba(99,102,241,.22) 0%, transparent 40%),
                        linear-gradient(145deg, var(--bg-1) 0%, var(--bg-2) 50%, var(--bg-3) 100%);
            overflow-x: hidden;
        }

        .bg-glow {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(14px);
            opacity: .45;
            animation: drift 14s ease-in-out infinite;
        }

        .bg-orb.one {
            width: 380px;
            height: 380px;
            left: -90px;
            top: -90px;
            background: rgba(56, 189, 248, .33);
        }

        .bg-orb.two {
            width: 320px;
            height: 320px;
            right: -70px;
            top: 18%;
            background: rgba(129, 140, 248, .34);
            animation-delay: 2.5s;
        }

        .bg-orb.three {
            width: 300px;
            height: 300px;
            left: 22%;
            bottom: -120px;
            background: rgba(45, 212, 191, .28);
            animation-delay: 5s;
        }

        @keyframes drift {
            0%, 100% { transform: translateY(0) translateX(0) scale(1); }
            50% { transform: translateY(-20px) translateX(10px) scale(1.04); }
        }

        .layout {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem;
        }

        .style-switcher {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 20;
            display: flex;
            gap: .4rem;
            padding: .35rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, .45);
            border: 1px solid rgba(255,255,255,.2);
            backdrop-filter: blur(8px);
        }

        .style-switcher a {
            text-decoration: none;
            font-size: .72rem;
            font-weight: 700;
            color: #cbd5e1;
            padding: .35rem .6rem;
            border-radius: 999px;
            border: 1px solid transparent;
            transition: all .16s ease;
        }

        .style-switcher a.active {
            color: #082f49;
            background: linear-gradient(120deg, #67e8f9 0%, #a5b4fc 100%);
        }

        .style-switcher a:not(.active):hover {
            color: #fff;
            border-color: rgba(255,255,255,.26);
        }

        .glass-shell {
            width: min(1100px, 100%);
            min-height: 620px;
            border-radius: 28px;
            border: 1px solid var(--panel-border);
            background: rgba(15, 23, 42, .42);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            display: grid;
            grid-template-columns: 1.08fr .92fr;
            overflow: hidden;
            box-shadow: 0 28px 80px rgba(2, 6, 23, .5);
        }

        .hero {
            position: relative;
            padding: 3rem 3.25rem;
            border-right: 1px solid rgba(255,255,255,.12);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hero-topology {
            position: absolute;
            inset: 0;
            z-index: 0;
            opacity: .45;
            pointer-events: none;
        }

        .hero-topology svg { width: 100%; height: 100%; }

        .top-line {
            fill: none;
            stroke: rgba(186, 230, 253, .35);
            stroke-width: 1.5;
            stroke-dasharray: 6 10;
            animation: flow 10s linear infinite;
        }

        .top-line.alt {
            stroke: rgba(165, 180, 252, .35);
            animation-duration: 14s;
            animation-direction: reverse;
        }

        .top-node {
            fill: #38bdf8;
            stroke: rgba(255,255,255,.55);
            stroke-width: 1.2;
            animation: pulse 3.6s ease-in-out infinite;
            transform-origin: center;
            transform-box: fill-box;
        }

        .top-node.alt { fill: #818cf8; animation-duration: 4.8s; }

        @keyframes flow {
            from { stroke-dashoffset: 0; }
            to { stroke-dashoffset: -180; }
        }

        @keyframes pulse {
            0%,100% { transform: scale(1); opacity: .9; }
            50% { transform: scale(1.16); opacity: 1; }
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: .75rem;
            padding: .35rem .7rem;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            margin-bottom: 1.6rem;
            background: rgba(15,23,42,.34);
        }

        .logo-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #22d3ee;
            box-shadow: 0 0 12px rgba(34,211,238,.75);
        }

        .hero h1 {
            font-size: clamp(2rem, 3.5vw, 3rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -.03em;
            max-width: 14ch;
            margin-bottom: 1rem;
        }

        .hero h1 span {
            background: linear-gradient(90deg, #67e8f9 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            color: #cbd5e1;
            max-width: 46ch;
            line-height: 1.7;
            font-size: .95rem;
        }

        .hero-cards {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }

        .hero-card {
            border: 1px solid rgba(255,255,255,.16);
            background: rgba(15, 23, 42, .44);
            border-radius: 14px;
            padding: .9rem;
            min-height: 90px;
        }

        .hero-card small {
            display: block;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 700;
            font-size: .64rem;
            margin-bottom: .35rem;
        }

        .hero-card strong {
            font-size: 1.2rem;
            font-weight: 800;
            color: #f8fafc;
            display: block;
        }

        .hero-card span {
            font-size: .73rem;
            color: #cbd5e1;
        }

        .form-pane {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.2rem 1.6rem;
        }

        .form-card {
            width: min(430px, 100%);
            border-radius: 20px;
            background: var(--panel-bg);
            border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(10px);
            padding: 1.6rem 1.4rem;
            box-shadow: 0 16px 42px rgba(2, 6, 23, .35);
        }

        .form-card h2 {
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -.02em;
            margin-bottom: .2rem;
        }

        .form-card p {
            color: #cbd5e1;
            font-size: .82rem;
            margin-bottom: 1.35rem;
        }

        .alert {
            border-radius: 12px;
            border: 1px solid rgba(248, 113, 113, .45);
            background: rgba(127, 29, 29, .35);
            padding: .65rem .75rem;
            margin-bottom: 1rem;
            font-size: .8rem;
            color: #fecaca;
        }

        .field {
            margin-bottom: .95rem;
        }

        .field label {
            display: block;
            margin-bottom: .35rem;
            font-size: .77rem;
            font-weight: 700;
            letter-spacing: .02em;
            color: #e2e8f0;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        .input {
            width: 100%;
            padding: .72rem .85rem .72rem 2.35rem;
            border-radius: 11px;
            border: 1px solid rgba(255,255,255,.22);
            background: rgba(15,23,42,.42);
            color: #e2e8f0;
            outline: none;
            font-size: .9rem;
            transition: border-color .18s, box-shadow .18s;
        }

        .input::placeholder { color: #94a3b8; }

        .input:focus {
            border-color: rgba(103,232,249,.85);
            box-shadow: 0 0 0 3px rgba(56,189,248,.2);
        }

        .input.is-invalid {
            border-color: rgba(248,113,113,.85);
        }

        .invalid-feedback {
            margin-top: .3rem;
            font-size: .72rem;
            color: #fecaca;
            font-weight: 700;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: .25rem 0 1rem;
            font-size: .78rem;
        }

        .meta label {
            display: inline-flex;
            gap: .4rem;
            align-items: center;
            color: #cbd5e1;
            cursor: pointer;
        }

        .meta input[type="checkbox"] {
            accent-color: #38bdf8;
            width: 15px;
            height: 15px;
        }

        .meta a {
            color: #7dd3fc;
            text-decoration: none;
            font-weight: 700;
        }

        .meta a:hover { text-decoration: underline; }

        .btn {
            width: 100%;
            border: 0;
            border-radius: 11px;
            padding: .78rem .9rem;
            color: #082f49;
            background: linear-gradient(120deg, #67e8f9 0%, #a5b4fc 100%);
            font-weight: 800;
            letter-spacing: .02em;
            cursor: pointer;
            transition: transform .15s, box-shadow .15s;
            box-shadow: 0 10px 24px rgba(56,189,248,.24);
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(56,189,248,.3);
        }

        .foot {
            margin-top: 1rem;
            text-align: center;
            color: #94a3b8;
            font-size: .72rem;
        }

        body.variant-command {
            background: radial-gradient(circle at 20% 10%, rgba(34,211,238,.18) 0%, transparent 34%),
                        radial-gradient(circle at 86% 70%, rgba(59,130,246,.2) 0%, transparent 38%),
                        linear-gradient(145deg, #020617 0%, #0f172a 50%, #111827 100%);
        }

        body.variant-command .glass-shell {
            background: rgba(2, 6, 23, .62);
            border-color: rgba(56,189,248,.25);
        }

        body.variant-command .hero-card,
        body.variant-command .form-card {
            border-color: rgba(56,189,248,.25);
            background: rgba(2, 6, 23, .56);
        }

        body.variant-command .btn {
            color: #ecfeff;
            background: linear-gradient(120deg, #0ea5e9 0%, #2563eb 100%);
        }

        body.variant-minimal {
            color: #0f172a;
            background: linear-gradient(145deg, #f8fafc 0%, #eef2ff 52%, #ecfeff 100%);
        }

        body.variant-minimal .bg-glow,
        body.variant-minimal .hero-topology {
            display: none;
        }

        body.variant-minimal .style-switcher {
            background: rgba(255,255,255,.82);
            border-color: #cbd5e1;
        }

        body.variant-minimal .style-switcher a {
            color: #334155;
        }

        body.variant-minimal .glass-shell {
            background: rgba(255,255,255,.86);
            border-color: #dbeafe;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .12);
        }

        body.variant-minimal .hero {
            border-right-color: #dbeafe;
        }

        body.variant-minimal .logo {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1e3a8a;
        }

        body.variant-minimal .logo-dot {
            background: #2563eb;
            box-shadow: none;
        }

        body.variant-minimal .hero h1,
        body.variant-minimal .hero-card strong,
        body.variant-minimal .form-card h2 {
            color: #0f172a;
        }

        body.variant-minimal .hero h1 span {
            background: linear-gradient(90deg, #1d4ed8 0%, #0f766e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        body.variant-minimal .hero p,
        body.variant-minimal .hero-card span,
        body.variant-minimal .hero-card small,
        body.variant-minimal .form-card p,
        body.variant-minimal .foot,
        body.variant-minimal .meta label,
        body.variant-minimal .field label {
            color: #475569;
        }

        body.variant-minimal .hero-card,
        body.variant-minimal .form-card {
            background: rgba(255,255,255,.88);
            border-color: #e2e8f0;
            box-shadow: none;
        }

        body.variant-minimal .input {
            color: #0f172a;
            background: #fff;
            border-color: #cbd5e1;
        }

        body.variant-minimal .input::placeholder,
        body.variant-minimal .input-wrap svg {
            color: #94a3b8;
        }

        body.variant-minimal .btn {
            color: #fff;
            background: linear-gradient(120deg, #2563eb 0%, #0ea5e9 100%);
            box-shadow: 0 10px 22px rgba(37,99,235,.24);
        }

        body.variant-minimal .meta a {
            color: #2563eb;
        }

        @media (max-width: 980px) {
            .glass-shell {
                grid-template-columns: 1fr;
            }

            .hero {
                order: 2;
                padding: 1.4rem 1.4rem 1.6rem;
                min-height: auto;
            }

            .hero-cards {
                grid-template-columns: 1fr 1fr;
            }

            .form-pane {
                order: 1;
                padding: 1.2rem 1rem .2rem;
            }

            .hero-content {
                margin-top: .5rem;
            }
        }

        @media (max-width: 620px) {
            .layout { padding: 1rem; }
            .glass-shell { border-radius: 20px; }
            .hero-cards { grid-template-columns: 1fr; }
            .hero h1 { font-size: 1.8rem; }
            .form-card { padding: 1.2rem 1rem; }
        }
    </style>
</head>
@php
    $style = request()->query('style', 'glass');
    $allowedStyles = ['glass', 'command', 'minimal'];
    $style = in_array($style, $allowedStyles, true) ? $style : 'glass';
@endphp
<body class="variant-{{ $style }}">
    <div class="bg-glow" aria-hidden="true">
        <div class="bg-orb one"></div>
        <div class="bg-orb two"></div>
        <div class="bg-orb three"></div>
    </div>

    <nav class="style-switcher" aria-label="Selector de estilo">
        <a href="{{ route('login', ['style' => 'glass'], false) }}" class="{{ $style === 'glass' ? 'active' : '' }}">Glass</a>
        <a href="{{ route('login', ['style' => 'command'], false) }}" class="{{ $style === 'command' ? 'active' : '' }}">Command</a>
        <a href="{{ route('login', ['style' => 'minimal'], false) }}" class="{{ $style === 'minimal' ? 'active' : '' }}">Minimal</a>
    </nav>

    <main class="layout">
        <section class="glass-shell">
            <aside class="hero">
                <div class="hero-topology" aria-hidden="true">
                    <svg viewBox="0 0 760 680" preserveAspectRatio="xMidYMid slice">
                        <path class="top-line" d="M60 120 L210 90 L340 150 L480 110 L620 165"/>
                        <path class="top-line alt" d="M90 250 L240 205 L375 270 L520 220 L665 280"/>
                        <path class="top-line" d="M80 400 L230 345 L360 420 L505 370 L640 430"/>
                        <path class="top-line alt" d="M110 560 L260 500 L390 560 L525 515 L670 575"/>
                        <path class="top-line" d="M210 90 L240 205 L230 345 L260 500"/>
                        <path class="top-line alt" d="M340 150 L375 270 L360 420 L390 560"/>
                        <path class="top-line" d="M480 110 L520 220 L505 370 L525 515"/>
                        <circle class="top-node" cx="210" cy="90" r="6"/>
                        <circle class="top-node alt" cx="340" cy="150" r="5"/>
                        <circle class="top-node" cx="480" cy="110" r="6"/>
                        <circle class="top-node alt" cx="620" cy="165" r="5"/>
                        <circle class="top-node" cx="240" cy="205" r="5"/>
                        <circle class="top-node" cx="375" cy="270" r="6"/>
                        <circle class="top-node alt" cx="520" cy="220" r="5"/>
                        <circle class="top-node" cx="230" cy="345" r="5"/>
                        <circle class="top-node alt" cx="360" cy="420" r="6"/>
                        <circle class="top-node" cx="505" cy="370" r="5"/>
                        <circle class="top-node alt" cx="260" cy="500" r="5"/>
                        <circle class="top-node" cx="390" cy="560" r="6"/>
                        <circle class="top-node alt" cx="525" cy="515" r="5"/>
                    </svg>
                </div>

                <div class="hero-content">
                    <div class="logo">
                        <span class="logo-dot"></span>
                        ITCity Platform
                    </div>
                    <h1>Una experiencia de acceso <span>moderna y elegante</span></h1>
                    <p>Accede al panel de administración y monitoreo con un diseño premium, visual limpio y enfoque en claridad.</p>
                </div>

                <div class="hero-cards">
                    <article class="hero-card">
                        <small>Estado</small>
                        <strong>Online</strong>
                        <span>Monitoreo activo</span>
                    </article>
                    <article class="hero-card">
                        <small>Topología</small>
                        <strong>Global</strong>
                        <span>Conectividad multi-sede</span>
                    </article>
                    <article class="hero-card">
                        <small>Control</small>
                        <strong>RBAC</strong>
                        <span>Roles y permisos</span>
                    </article>
                </div>
            </aside>

            <div class="form-pane">
                <div class="form-card">
                    <h2>Iniciar sesión</h2>
                    <p>Ingresa tus credenciales para continuar.</p>

                    @if ($errors->any())
                        <div class="alert">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login', [], false) }}">
                        @csrf

                        <div class="field">
                            <label for="email">Correo electrónico</label>
                            <div class="input-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Z"/>
                                </svg>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus required class="input @error('email') is-invalid @enderror" placeholder="tu@empresa.com">
                            </div>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="password">Contraseña</label>
                            <div class="input-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                                </svg>
                                <input id="password" type="password" name="password" autocomplete="current-password" required class="input @error('password') is-invalid @enderror" placeholder="••••••••">
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="meta">
                            <label>
                                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                Recordarme
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                            @endif
                        </div>

                        <button type="submit" class="btn">Entrar al panel</button>
                    </form>

                    <div class="foot">ITCity · Tenant Access</div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
