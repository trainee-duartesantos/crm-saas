<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // 🔑 aqui assumimos relação User -> Tenant
            $tenant = $user->tenant ?? null;

            if ($tenant) {
                app()->instance('tenant', $tenant);
            }
        }

        return $next($request);
    }
}
