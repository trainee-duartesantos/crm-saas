<?php

use App\Support\TenantContext;
use App\Services\ActivityLogger;

if (! function_exists('activity_log')) {
    function activity_log(string $action, $subject = null, array $metadata = []): void
    {
        app(ActivityLogger::class)->log($action, $subject, $metadata);
    }
}


if (! function_exists('tenant')) {
    function tenant()
    {
        return app(TenantContext::class)->get();
    }
}
