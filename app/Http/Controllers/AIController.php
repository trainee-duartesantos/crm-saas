<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\AIService;
use App\Services\ActivityLogger;
use App\Models\UserInvite;
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

    public function detectRisks(
        AIService $ai,
        ActivityLogger $logger
    ): RedirectResponse {
        $this->authorize('viewAny', ActivityLog::class);

        $tenant = app('tenant');

        // 1️⃣ Convites pendentes há mais de 7 dias
        $pendingInvites = UserInvite::query()
            ->where('tenant_id', $tenant->id)
            ->whereNull('accepted_at')
            ->where('created_at', '<=', Carbon::now()->subDays(7))
            ->count();

        if ($pendingInvites === 0) {
            return back()->with('info', 'No risks detected.');
        }

        // 2️⃣ Pedir explicação à AI
        $message = $ai->detectRisk('pending_invites', [
            'count' => $pendingInvites,
        ]);

        // 3️⃣ Guardar na timeline
        $logger->log(
            action: 'ai.risk.detected',
            metadata: [
                'type' => 'pending_invites',
                'count' => $pendingInvites,
                'message' => $message,
            ]
        );

        return back()->with('warning', 'Potential risks detected.');
    }
}
