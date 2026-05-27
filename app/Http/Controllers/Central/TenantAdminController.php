<?php

namespace App\Http\Controllers\Central;

use App\Models\Tenant;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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
            'company_name' => ['required', 'string', 'max:120', Rule::unique('tenants', 'company_name')],
            'domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
            'admin_name' => ['required', 'string', 'max:120'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:10', 'max:72'],
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
            'admin_email' => $validated['admin_email'],
        ];
        $tenant->save();

        $tenant->domains()->create([
            'domain' => $validated['domain'],
        ]);

        tenancy()->initialize($tenant);

        try {
            User::query()->updateOrCreate(
                ['email' => $validated['admin_email']],
                [
                    'name' => $validated['admin_name'],
                    'password' => Hash::make($validated['admin_password']),
                    'role' => 'admin',
                    'auth_source' => 'local',
                    'is_active' => true,
                    'access_profile' => 'full_admin',
                    'branch_id' => null,
                ]
            );
        } finally {
            tenancy()->end();
        }

        return back()->with('status', 'Tenant y admin inicial creados correctamente. Activa su suscripción para habilitar acceso.');
    }
}
