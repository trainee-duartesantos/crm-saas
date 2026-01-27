<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\UserInvite;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TenantInsightsController extends Controller
{
    public function index()
    {
        // Reutilizamos a mesma policy do ActivityLog (simples e consistente)
        $this->authorize('viewAny', ActivityLog::class);

        $tenant = app('tenant');

        // ✅ Invites
        $invitesTotal = UserInvite::query()
            ->where('tenant_id', $tenant->id)
            ->count();

        $invitesAccepted = UserInvite::query()
            ->where('tenant_id', $tenant->id)
            ->whereNotNull('accepted_at')
            ->count();

        $invitesPending = UserInvite::query()
            ->where('tenant_id', $tenant->id)
            ->whereNull('accepted_at')
            ->count();

        // ✅ Activity volume (últimos 14 dias)
        $activityByDay = ActivityLog::query()
            ->selectRaw("DATE(created_at) as day, COUNT(*) as total")
            ->where('tenant_id', $tenant->id)
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $activityLabels = $activityByDay->pluck('day')->values();
        $activityTotals = $activityByDay->pluck('total')->values();

        // ✅ AI events (quantos logs "ai.*")
        $aiEventsTotal = ActivityLog::query()
            ->where('tenant_id', $tenant->id)
            ->where('action', 'like', 'ai.%')
            ->count();

        // ✅ último evento (saúde do tenant)
        $lastActivityAt = ActivityLog::query()
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->value('created_at');

        return Inertia::render('insights/Index', [
            'metrics' => [
                'invites_total' => $invitesTotal,
                'invites_accepted' => $invitesAccepted,
                'invites_pending' => $invitesPending,
                'ai_events_total' => $aiEventsTotal,
                'last_activity_at' => $lastActivityAt,
            ],
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
        ]);
    }
}
