<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\AIService;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

class AIController extends Controller
{
    public function suggestInviteFollowUp(AIService $ai)
    {
        $message = $ai->suggest('invite_pending');

        activity_log(
            'ai.suggestion',
            null,
            [
                'message' => $message,
                'context' => 'invite_pending',
            ]
        );

        return back()->with('success', 'AI suggestion generated.');
    }

    public function summarizeTimeline(
        AIService $ai,
        ActivityLogger $logger
    ): RedirectResponse {
        $this->authorize('viewAny', ActivityLog::class);

        $tenant = app('tenant');

        // 1️⃣ Buscar últimos 30 dias
        $logs = ActivityLog::query()
            ->where('tenant_id', $tenant->id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->latest()
            ->get();

        // 2️⃣ Preparar contexto para a AI
        $context = $logs->map(function ($log) {
            return "{$log->created_at}: {$log->action}";
        })->implode("\n");

        // 3️⃣ Prompt
        $summary = $ai->summarizeTimeline($context);

        // 4️⃣ Guardar na timeline
        $logger->log(
            action: 'ai.timeline.summary',
            metadata: [
                'message' => $summary,
                'period' => 'last_30_days',
            ]
        );

        return back();
    }
}
