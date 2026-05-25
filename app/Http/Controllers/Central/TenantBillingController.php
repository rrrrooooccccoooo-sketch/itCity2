<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class TenantBillingController extends Controller
{
    public function checkout(Request $request, Tenant $tenant): RedirectResponse
    {
        if ($tenant->subscribed('default')) {
            $tenant->is_active = true;
            $tenant->save();

            return back()->with('status', 'Este tenant ya cuenta con suscripción activa.');
        }

        $price = $this->planPriceId($tenant->plan);

        if (!$price) {
            return back()->with('status', 'Falta configurar el precio Stripe para este plan en .env.');
        }

        $tenant->createOrGetStripeCustomer();

        return $tenant->newSubscription('default', $price)
            ->checkout([
                'success_url' => route('admin.tenants.billing.success', $tenant),
                'cancel_url' => route('admin.tenants.index'),
            ]);
    }

    public function success(Tenant $tenant): RedirectResponse
    {
        $tenant->refresh();

        if ($tenant->subscribed('default')) {
            $tenant->is_active = true;
            $tenant->save();

            return redirect()
                ->route('admin.tenants.index')
                ->with('status', 'Suscripción activada correctamente para ' . ($tenant->company_name ?? $tenant->id));
        }

        return redirect()
            ->route('admin.tenants.index')
            ->with('status', 'Stripe regresó correctamente, pero la suscripción aún no aparece activa. Revisa webhook.');
    }

    public function cancel(Tenant $tenant): RedirectResponse
    {
        $subscription = $tenant->subscription('default');

        if (!$subscription) {
            $tenant->is_active = false;
            $tenant->save();

            return back()->with('status', 'Este tenant no tiene una suscripción activa para cancelar.');
        }

        if ($subscription->onGracePeriod()) {
            return back()->with('status', 'La suscripción ya está cancelada y en periodo de gracia.');
        }

        $subscription->cancel();
        $tenant->is_active = false;
        $tenant->save();

        return back()->with('status', 'Suscripción cancelada. El tenant quedó marcado como inactivo.');
    }

    public function resume(Tenant $tenant): RedirectResponse
    {
        $subscription = $tenant->subscription('default');

        if (!$subscription) {
            return back()->with('status', 'Este tenant no tiene suscripción para reanudar.');
        }

        if (!$subscription->onGracePeriod()) {
            if ($tenant->subscribed('default')) {
                $tenant->is_active = true;
                $tenant->save();
            }

            return back()->with('status', 'La suscripción no está en periodo de gracia; no requiere reanudación.');
        }

        $subscription->resume();
        $tenant->is_active = true;
        $tenant->save();

        return back()->with('status', 'Suscripción reanudada correctamente.');
    }

    private function planPriceId(?string $plan): ?string
    {
        return Arr::get([
            'starter' => env('STRIPE_PRICE_STARTER'),
            'business' => env('STRIPE_PRICE_BUSINESS'),
            'enterprise' => env('STRIPE_PRICE_ENTERPRISE'),
        ], $plan ?? 'starter');
    }
}
