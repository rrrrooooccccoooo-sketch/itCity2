<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscriptionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (filter_var(env('TENANT_BILLING_BYPASS', false), FILTER_VALIDATE_BOOL)) {
            return $next($request);
        }

        $tenant = tenant();

        if (!$tenant) {
            abort(404);
        }

        if (!$tenant->is_active || !$tenant->subscribed('default')) {
            return response()->view('tenant.subscription-required', [
                'tenantName' => $tenant->company_name ?? data_get($tenant, 'data.company_name', 'Cliente'),
            ], 402);
        }

        return $next($request);
    }
}
