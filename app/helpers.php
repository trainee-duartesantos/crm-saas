<?php

use App\Support\TenantContext;

if (! function_exists('tenant')) {
    function tenant()
    {
        return app(TenantContext::class)->get();
    }
}
