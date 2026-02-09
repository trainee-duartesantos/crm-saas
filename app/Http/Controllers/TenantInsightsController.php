<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\UserInvite;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TenantInsightsController extends Controller
{
    public function index()
    {

        $tenant = app('tenant');

        /* ======================
         | Metrics
         ====================== */

        $invitesTotal = UserInvite::where('tenant_id', $tenant->id)->count();
        $invitesAccepted = UserInvite::where('tenant_id', $tenant->id)
            ->whereNotNull('accepted_at')
            ->count();
        $invitesPending = UserInvite::where('tenant_id', $tenant->id)
            ->whereNull('accepted_at')
            ->count();

        $activityByDay = ActivityLog::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('tenant_id', $tenant->id)
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $activityLabels = $activityByDay
            ->pluck('day')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'))
            ->values();

        $activityTotals = $activityByDay->pluck('total')->values();

        $aiEventsTotal = ActivityLog::where('tenant_id', $tenant->id)
            ->where('action', 'like', 'ai.%')
            ->count();

        $lastActivityAt = ActivityLog::where('tenant_id', $tenant->id)
            ->latest()
            ->value('created_at');

        $lastInsight = ActivityLog::where('tenant_id', $tenant->id)
            ->where('action', 'ai.tenant.insight')
            ->latest()
            ->first();

        $engagementScore = match (true) {
            $invitesPending > 5 => 'low',
            $invitesPending > 2 => 'moderate',
            default => 'high',
        };

        return Inertia::render('insights/Index', [
            'metrics' => [
                'invites_total' => $invitesTotal,
                'invites_accepted' => $invitesAccepted,
                'invites_pending' => $invitesPending,
                'ai_events_total' => $aiEventsTotal,
                'last_activity_at' => $lastActivityAt,
            ],
            'lastInsight' => $lastInsight
                ? [
                    'message' => $lastInsight->metadata['message'] ?? null,
                    'confidence' => $lastInsight->metadata['confidence'] ?? null,
                    'generated_at' => $lastInsight->created_at,
                ]
                : null,
            'engagementScore' => $engagementScore,
            'charts' => [
                'activity' => [
                    'labels' => $activityLabels,
                    'data' => $activityTotals,
                ],
                'invites' => [
                    'labels' => ['Accepted', 'Pending'],
                    'data' => [$invitesAccepted, $invitesPending],
                ],
            ],

            // 👇 IMPORTANTÍSSIMO para o frontend
            'can' => [
                'products' => auth()->user()->can('insights.products'),
                'deals' => auth()->user()->can('insights.deals'),
                'revenue' => auth()->user()->can('insights.revenue'),
                'ai_generate' => auth()->user()->can('insights.ai.generate'),
                'ai_next_action' => auth()->user()->can('insights.ai.next-action'),
            ],
        ]);
    }
}
