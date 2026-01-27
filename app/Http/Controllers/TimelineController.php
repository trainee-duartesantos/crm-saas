<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Inertia\Inertia;

class TimelineController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', ActivityLog::class);

        $tenant = app('tenant');

        $items = ActivityLog::query()
            ->where('tenant_id', $tenant->id)
            ->with('actor')
            ->latest()
            ->limit(50)
            ->get();

        return Inertia::render('timeline/Index', [
            'items' => $items,
        ]);
    }
}
