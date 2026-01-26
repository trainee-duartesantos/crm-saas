<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\TenantContext;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

class SetTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant_id) {
            $tenant = Tenant::find($user->tenant_id);

            if ($tenant) {
                // 🔥 ISTO É O QUE FALTAVA
                app()->instance('tenant', $tenant);
            }
        }

        return $next($request);
    }
}
