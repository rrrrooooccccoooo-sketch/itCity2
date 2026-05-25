<?php

namespace App\Http\Controllers\Central;

use App\Models\Tenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantAdminController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with('domains')
            ->latest()
            ->get();

        return view('central.tenants.index', compact('tenants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:120'],
            'domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'billing_email' => ['nullable', 'email', 'max:255'],
            'plan' => ['required', 'in:starter,business,enterprise'],
        ]);

        $tenant = new Tenant();
        $tenant->company_name = $validated['company_name'];
        $tenant->logo_url = $validated['logo_url'] ?? null;
        $tenant->plan = $validated['plan'];
        $tenant->is_active = false;
        $tenant->data = [
            'company_name' => $validated['company_name'],
            'logo_url' => $validated['logo_url'] ?? null,
            'billing_email' => $validated['billing_email'] ?? null,
            'plan' => $validated['plan'],
        ];
        $tenant->save();

        $tenant->domains()->create([
            'domain' => $validated['domain'],
        ]);

        return back()->with('status', 'Tenant creado correctamente. Activa su suscripción para habilitar acceso.');
    }
}
