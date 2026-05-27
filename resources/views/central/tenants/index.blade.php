@extends('layouts.app')

@section('content')
@php
    $tenantCount = $tenants->count();
    $activeCount = $tenants->filter(fn ($tenant) => $tenant->subscribed('default') && $tenant->is_active)->count();
    $pendingCount = max(0, $tenantCount - $activeCount);
@endphp

<div class="tenant-admin-shell container-fluid px-3 px-xl-4">
    @if (session('status'))
        <div data-swal-flash data-swal-icon="success" data-swal-title="Operación completada" data-swal-text="{{ session('status') }}" data-swal-toast="1" data-swal-position="top-end" data-swal-timer="2600"></div>
    @endif

    <section class="tenant-hero mb-4">
        <div>
            <p class="tenant-hero-kicker mb-2">Control Central Multi-tenant</p>
            <h1 class="tenant-hero-title mb-2">Administración de Tenants</h1>
            <p class="tenant-hero-subtitle mb-0">Crea, activa y opera clientes desde una sola consola con visibilidad clara de su estado comercial.</p>
        </div>
        <div class="tenant-hero-stats">
            <div class="tenant-stat-card">
                <span class="tenant-stat-label">Total</span>
                <strong class="tenant-stat-value">{{ $tenantCount }}</strong>
            </div>
            <div class="tenant-stat-card success">
                <span class="tenant-stat-label">Activos</span>
                <strong class="tenant-stat-value">{{ $activeCount }}</strong>
            </div>
            <div class="tenant-stat-card warning">
                <span class="tenant-stat-label">Pendientes</span>
                <strong class="tenant-stat-value">{{ $pendingCount }}</strong>
            </div>
        </div>
    </section>

    <div class="row g-4 align-items-start">
        <div class="col-lg-5 col-xxl-4">
            <div class="tenant-panel h-100">
                <div class="tenant-panel-head">
                    <h2 class="tenant-panel-title">Alta de cliente</h2>
                    <p class="tenant-panel-text">Define identidad y dominio inicial para provisionar el tenant.</p>
                </div>

                <form method="POST" action="{{ route('admin.tenants.store') }}" id="tenant-create-form" class="tenant-form">
                    @csrf
                    <div>
                        <label class="form-label tenant-label">Empresa</label>
                        <input type="text" name="company_name" class="form-control tenant-input" value="{{ old('company_name') }}" required>
                    </div>
                    <div>
                        <label class="form-label tenant-label">Dominio tenant</label>
                        <input type="text" name="domain" class="form-control tenant-input" placeholder="acme.localhost" value="{{ old('domain') }}" required>
                        <small class="tenant-help">Agrega este dominio al archivo hosts para pruebas locales.</small>
                    </div>
                    <div>
                        <label class="form-label tenant-label">Nombre admin inicial</label>
                        <input type="text" name="admin_name" class="form-control tenant-input" value="{{ old('admin_name') }}" required>
                    </div>
                    <div>
                        <label class="form-label tenant-label">Email admin inicial</label>
                        <input type="email" name="admin_email" class="form-control tenant-input" value="{{ old('admin_email') }}" required>
                    </div>
                    <div>
                        <label class="form-label tenant-label">Password temporal admin</label>
                        <input type="password" name="admin_password" class="form-control tenant-input" minlength="10" required autocomplete="new-password">
                        <small class="tenant-help">Comparte esta contraseña temporal al responsable del tenant para su primer acceso.</small>
                    </div>
                    <div>
                        <label class="form-label tenant-label">Logo URL (opcional)</label>
                        <input type="url" name="logo_url" class="form-control tenant-input" value="{{ old('logo_url') }}">
                    </div>
                    <div>
                        <label class="form-label tenant-label">Email de facturación (opcional)</label>
                        <input type="email" name="billing_email" class="form-control tenant-input" value="{{ old('billing_email') }}">
                    </div>
                    <div>
                        <label class="form-label tenant-label">Plan</label>
                        <select name="plan" class="form-select tenant-input" required>
                            <option value="starter" @selected(old('plan') === 'starter')>Starter</option>
                            <option value="business" @selected(old('plan') === 'business')>Business</option>
                            <option value="enterprise" @selected(old('plan') === 'enterprise')>Enterprise</option>
                        </select>
                    </div>
                    <button type="submit" class="btn tenant-submit-btn" id="tenant-create-submit">Crear tenant</button>
                </form>
            </div>
        </div>

        <div class="col-lg-7 col-xxl-8">
            <div class="tenant-panel">
                <div class="tenant-panel-head d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h2 class="tenant-panel-title">Tenants registrados</h2>
                        <p class="tenant-panel-text mb-0">Estatus de suscripción y acceso directo por cliente.</p>
                    </div>
                </div>

                <div class="table-responsive tenant-table-wrap">
                    <table class="table tenant-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Plan</th>
                                <th>Dominio</th>
                                <th>Suscripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tenants as $tenant)
                                @php
                                    $domain = optional($tenant->domains->first())->domain;
                                    $subscription = $tenant->subscription('default');

                                    if ($tenant->subscribed('default') && $subscription && $subscription->onGracePeriod()) {
                                        $statusClass = 'soft-muted';
                                        $statusLabel = 'Cancelada (gracia)';
                                    } elseif ($tenant->subscribed('default') && $tenant->is_active) {
                                        $statusClass = 'soft-success';
                                        $statusLabel = 'Activa';
                                    } else {
                                        $statusClass = 'soft-warning';
                                        $statusLabel = 'Pendiente de pago';
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="tenant-company">{{ $tenant->company_name ?? data_get($tenant, 'data.company_name', 'N/A') }}</div>
                                        <div class="tenant-company-id">ID {{ $tenant->id }}</div>
                                    </td>
                                    <td>
                                        <span class="tenant-pill">{{ strtoupper($tenant->plan ?? data_get($tenant, 'data.plan', 'starter')) }}</span>
                                    </td>
                                    <td>
                                        <span class="tenant-domain">{{ $domain ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="tenant-state {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td>
                                        <div class="tenant-actions">
                                            @if ($domain)
                                                <a href="http://{{ $domain }}" target="_blank" class="btn btn-sm btn-outline-primary">Abrir portal</a>
                                            @endif

                                            @if (!($tenant->subscribed('default') && $tenant->is_active))
                                                <form method="POST" action="{{ route('admin.tenants.billing.checkout', $tenant) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">Pagar suscripción</button>
                                                </form>
                                            @elseif ($subscription && !$subscription->onGracePeriod())
                                                <form method="POST" action="{{ route('admin.tenants.billing.cancel', $tenant) }}" data-confirm="¿Cancelar suscripción?" data-confirm-title="Cancelar suscripción" data-confirm-icon="warning" data-confirm-button-text="Sí, cancelar">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar</button>
                                                </form>
                                            @elseif ($subscription && $subscription->onGracePeriod())
                                                <form method="POST" action="{{ route('admin.tenants.billing.resume', $tenant) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Reanudar</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">Aún no hay tenants registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.bunny.net/css?family=space-grotesk:500,600,700|dm-sans:400,500,700');

    .tenant-admin-shell {
        --tenant-primary: #0ea5e9;
        --tenant-secondary: #0f172a;
        --tenant-accent: #14b8a6;
        --tenant-bg: #eef4fb;
        --tenant-border: #d9e3ef;
        --tenant-text: #1e293b;
        font-family: 'DM Sans', sans-serif;
        color: var(--tenant-text);
    }

    .tenant-hero {
        background:
            radial-gradient(circle at 90% 0%, rgba(20, 184, 166, .18), transparent 42%),
            radial-gradient(circle at 0% 100%, rgba(14, 165, 233, .22), transparent 48%),
            linear-gradient(140deg, #082f49 0%, #0f172a 44%, #1e293b 100%);
        border-radius: 24px;
        padding: 28px;
        color: #fff;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 20px;
        border: 1px solid rgba(255, 255, 255, .14);
        box-shadow: 0 22px 45px rgba(2, 8, 23, .24);
    }

    .tenant-hero-kicker {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .16em;
        color: #93c5fd;
        font-weight: 700;
    }

    .tenant-hero-title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: clamp(1.65rem, 2.2vw, 2.2rem);
        font-weight: 700;
        letter-spacing: -.01em;
    }

    .tenant-hero-subtitle {
        color: #cbd5e1;
        max-width: 700px;
    }

    .tenant-hero-stats {
        display: flex;
        gap: 10px;
        align-items: stretch;
    }

    .tenant-stat-card {
        min-width: 112px;
        border-radius: 14px;
        padding: 12px 14px;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .16);
        backdrop-filter: blur(5px);
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .tenant-stat-card.success {
        background: rgba(16, 185, 129, .16);
    }

    .tenant-stat-card.warning {
        background: rgba(251, 191, 36, .18);
    }

    .tenant-stat-label {
        font-size: .72rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #e2e8f0;
    }

    .tenant-stat-value {
        font-size: 1.55rem;
        line-height: 1;
        font-family: 'Space Grotesk', sans-serif;
    }

    .tenant-panel {
        background: #fff;
        border: 1px solid var(--tenant-border);
        border-radius: 20px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .tenant-panel-head {
        padding: 18px 20px 14px;
        border-bottom: 1px solid #edf2f7;
        background: linear-gradient(180deg, #fff 0%, #f9fbfe 100%);
    }

    .tenant-panel-title {
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 700;
        font-size: 1.08rem;
        margin: 0;
        color: #0f172a;
    }

    .tenant-panel-text {
        margin: 4px 0 0;
        color: #64748b;
        font-size: .9rem;
    }

    .tenant-form {
        padding: 18px 20px 20px;
        display: grid;
        gap: 14px;
    }

    .tenant-label {
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #334155;
        margin-bottom: 6px;
    }

    .tenant-input {
        border-radius: 12px;
        border: 1px solid #d4deeb;
        padding: .64rem .78rem;
    }

    .tenant-input:focus {
        border-color: #38bdf8;
        box-shadow: 0 0 0 .2rem rgba(56, 189, 248, .18);
    }

    .tenant-help {
        display: block;
        margin-top: 5px;
        color: #64748b;
        font-size: .79rem;
    }

    .tenant-submit-btn {
        width: 100%;
        border: 0;
        border-radius: 12px;
        padding: .74rem 1rem;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(120deg, #0284c7 0%, #0ea5e9 48%, #14b8a6 100%);
        box-shadow: 0 12px 26px rgba(14, 165, 233, .34);
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .tenant-submit-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 30px rgba(14, 165, 233, .4);
        color: #fff;
    }

    .tenant-submit-btn:disabled {
        opacity: .76;
        cursor: not-allowed;
    }

    .tenant-table-wrap {
        padding: 2px 0;
    }

    .tenant-table thead th {
        font-size: .73rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        border-bottom-color: #e5edf7;
        white-space: nowrap;
    }

    .tenant-table tbody tr {
        transition: background-color .14s ease;
    }

    .tenant-table tbody tr:hover {
        background: #f8fbff;
    }

    .tenant-table td {
        border-color: #edf2f7;
        vertical-align: middle;
    }

    .tenant-company {
        font-weight: 700;
        color: #0f172a;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .tenant-company-id {
        font-size: .74rem;
        color: #64748b;
        max-width: 250px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .tenant-domain {
        font-family: Consolas, monospace;
        font-size: .82rem;
        color: #0f172a;
        word-break: break-word;
    }

    .tenant-pill {
        display: inline-flex;
        border-radius: 999px;
        padding: .25rem .58rem;
        font-size: .7rem;
        font-weight: 700;
        background: #e0f2fe;
        color: #075985;
        border: 1px solid #bae6fd;
    }

    .tenant-state {
        display: inline-flex;
        border-radius: 999px;
        padding: .3rem .62rem;
        font-size: .72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .tenant-state.soft-success {
        background: #dcfce7;
        color: #166534;
    }

    .tenant-state.soft-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .tenant-state.soft-muted {
        background: #e2e8f0;
        color: #334155;
    }

    .tenant-actions {
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
    }

    .tenant-actions form {
        margin: 0;
    }

    @media (max-width: 992px) {
        .tenant-hero {
            grid-template-columns: 1fr;
            padding: 20px;
        }

        .tenant-hero-stats {
            flex-wrap: wrap;
        }

        .tenant-stat-card {
            min-width: 92px;
        }
    }
</style>

<script>
    (() => {
        const createForm = document.getElementById('tenant-create-form');
        const createSubmit = document.getElementById('tenant-create-submit');

        if (!createForm || !createSubmit) {
            return;
        }

        createForm.addEventListener('submit', (event) => {
            if (createForm.dataset.submitted === '1') {
                event.preventDefault();
                return;
            }

            createForm.dataset.submitted = '1';
            createSubmit.disabled = true;
            createSubmit.textContent = 'Creando...';
        });
    })();
</script>
@endsection
