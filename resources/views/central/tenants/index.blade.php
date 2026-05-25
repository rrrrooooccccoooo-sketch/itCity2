@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Administración de Tenants</h2>
    </div>

    @if (session('status'))
        <div data-swal-flash data-swal-icon="success" data-swal-title="Operación completada" data-swal-text="{{ session('status') }}" data-swal-toast="1" data-swal-position="top-end" data-swal-timer="2600"></div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-semibold">Alta de cliente</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.tenants.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Empresa</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dominio tenant</label>
                            <input type="text" name="domain" class="form-control" placeholder="acme.localhost" value="{{ old('domain') }}" required>
                            <small class="text-muted">Agrega este dominio a tu hosts local para pruebas.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Logo URL (opcional)</label>
                            <input type="url" name="logo_url" class="form-control" value="{{ old('logo_url') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email de facturación (opcional)</label>
                            <input type="email" name="billing_email" class="form-control" value="{{ old('billing_email') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Plan</label>
                            <select name="plan" class="form-select" required>
                                <option value="starter" @selected(old('plan') === 'starter')>Starter</option>
                                <option value="business" @selected(old('plan') === 'business')>Business</option>
                                <option value="enterprise" @selected(old('plan') === 'enterprise')>Enterprise</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Crear tenant</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-semibold">Tenants registrados</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
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
                                @php $domain = optional($tenant->domains->first())->domain; @endphp
                                <tr>
                                    <td>{{ $tenant->company_name ?? data_get($tenant, 'data.company_name', 'N/A') }}</td>
                                    <td>{{ strtoupper($tenant->plan ?? data_get($tenant, 'data.plan', 'starter')) }}</td>
                                    <td>{{ $domain ?? '-' }}</td>
                                    <td>
                                        @php
                                            $subscription = $tenant->subscription('default');
                                        @endphp

                                        @if ($tenant->subscribed('default') && $subscription && $subscription->onGracePeriod())
                                            <span class="badge bg-secondary">Cancelada (gracia)</span>
                                        @elseif ($tenant->subscribed('default') && $tenant->is_active)
                                            <span class="badge bg-success">Activa</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pendiente de pago</span>
                                        @endif
                                    </td>
                                    <td class="d-flex gap-2">
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
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Aún no hay tenants registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
