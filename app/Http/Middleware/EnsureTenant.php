<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->bound('tenant')) {
            abort(403, 'NO TENANT CONTEXT AVAILABLE.');
        }

        return $next($request);
    }
}
