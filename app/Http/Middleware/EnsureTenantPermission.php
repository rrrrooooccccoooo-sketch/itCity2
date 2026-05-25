<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user || !method_exists($user, 'hasTenantPermission') || !$user->hasTenantPermission($permission)) {
            abort(403, 'Acceso denegado: no tienes permiso para esta acción.');
        }

        return $next($request);
    }
}
