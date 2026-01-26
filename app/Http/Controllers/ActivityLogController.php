<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $logs = ActivityLog::query()
            ->where('tenant_id', $user->tenant_id)
            ->with('actor:id,name,email')
            ->latest()
            ->paginate(20);

        return Inertia::render('ActivityLogs/Index', [
            'logs' => $logs,
        ]);
    }
}
