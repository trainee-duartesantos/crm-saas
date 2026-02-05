<?php

use App\Support\TenantContext;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Carbon\CarbonInterface;

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

if (! function_exists('nextWorkTime')) {
    function nextWorkTime(CarbonInterface $date): CarbonInterface
    {
        // Garantir mutabilidade
        if ($date instanceof \Carbon\CarbonImmutable) {
            $date = $date->toMutable();
        }

        $start = config('followups.work_hours.start'); // 9
        $end   = config('followups.work_hours.end');   // 18

        // Fim de semana → próximo dia útil às 9h
        if ($date->isWeekend()) {
            return $date->nextWeekday()->setTime($start, 0);
        }

        // Antes das 9h
        if ($date->hour < $start) {
            return $date->setTime($start, 0);
        }

        // Depois das 18h
        if ($date->hour >= $end) {
            return $date->addDay()->setTime($start, 0);
        }

        return $date;
    }
}