<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ProductStatsService;

class ProductStatsController extends Controller
{
    public function index(Request $request, ProductStatsService $service)
    {
        $tenant = app('tenant');

        $statuses = $request->input('statuses', []);
        $from = $request->input('from');
        $to = $request->input('to');

        $stats = $service->getStats(
            tenantId: $tenant->id,
            statuses: $statuses,
            from: $from,
            to: $to
        );

        return Inertia::render('insights/Products', [
            'stats' => $stats,
            'filters' => [
                'statuses' => $statuses,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }
}
