<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Deal;
use App\Models\Person;
use App\Models\Activity;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('tenant');
        $user = $request->user();

        // Base KPIs (tenant)
        $peopleCount = Person::query()->where('tenant_id', $tenant->id)->count();

        $deals = Deal::query()
            ->where('tenant_id', $tenant->id)
            ->get(['id', 'status', 'value']);

        $dealsCount = $deals->count();

        $pipelineValue = $deals->whereIn('status', ['lead','proposal'])->sum(fn ($d) => (float) ($d->value ?? 0));
        $wonValue = $deals->where('status', 'won')->sum(fn ($d) => (float) ($d->value ?? 0));
        $lostCount = $deals->where('status', 'lost')->count();
        $wonCount = $deals->where('status', 'won')->count();

        $winRate = ($wonCount + $lostCount) > 0
            ? round(($wonCount / ($wonCount + $lostCount)) * 100)
            : null;

        // Activities (tenant)
        $activitiesCount = Activity::query()->where('tenant_id', $tenant->id)->count();

        // User personal KPIs
        $myOpenActivities = Activity::query()
            ->where('tenant_id', $tenant->id)
            ->where('assigned_to', $user->id)
            ->whereNull('completed_at')
            ->count();

        $myNextDue = Activity::query()
            ->where('tenant_id', $tenant->id)
            ->where('assigned_to', $user->id)
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->orderBy('due_at')
            ->first(['id','title','due_at']);

        if ($user->isOwner()) {
            return Inertia::render('dashboards/OwnerDashboard', [
                'people' => $peopleCount,
                'deals' => $dealsCount,
                'activities' => $activitiesCount,
                'kpis' => [
                    'pipeline_value' => $pipelineValue,
                    'won_value' => $wonValue,
                    'win_rate' => $winRate,
                ],
            ]);
        }

        if ($user->isAdmin()) {
            return Inertia::render('dashboards/AdminDashboard', [
                'people' => $peopleCount,
                'deals' => $dealsCount,
                'activities' => $activitiesCount,
                'kpis' => [
                    'pipeline_value' => $pipelineValue,
                    'won_value' => $wonValue,
                    'win_rate' => $winRate,
                ],
            ]);
        }

        // Normal user
        return Inertia::render('dashboards/UserDashboard', [
            'my' => [
                'open_activities' => $myOpenActivities,
                'next_due' => $myNextDue,
            ],
        ]);
    }
}
