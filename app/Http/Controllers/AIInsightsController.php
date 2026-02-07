<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Deal;

class AIInsightsController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', ActivityLog::class);

        $tenantId = app('tenant')->id;

        // 🔹 Últimos eventos AI
        $aiEvents = ActivityLog::query()
            ->where('tenant_id', $tenantId)
            ->where('action', 'like', 'ai.%')
            ->latest()
            ->limit(15)
            ->get();

        // 🔹 Riscos ativos (últimos ai.risk.detected)
        $risks = $aiEvents->where('action', 'ai.risk.detected');

        // 🔹 Última recomendação
        $nextAction = $aiEvents->firstWhere(
            'action',
            'ai.recommendation.next_action'
        );

        // 🔹 Último summary de deal
        $lastDealSummary = $aiEvents->firstWhere(
            'action',
            'ai.deal.summary.generated'
        );

        return inertia('AI/Insights', [
            'stats' => [
                'ai_events_7d' => ActivityLog::where('tenant_id', $tenantId)
                    ->where('action', 'like', 'ai.%')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),

                'active_risks' => $risks->count(),

                'last_summary_at' => optional($lastDealSummary)->created_at,

                'next_action' => $nextAction?->metadata['message'] ?? null,
            ],

            'events' => $aiEvents,
        ]);
    }
}
