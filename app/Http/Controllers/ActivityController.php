<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\Deal;
use App\Models\Person;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityController extends Controller
{
    public function index()
    {
        // auth + tenant já vêm do middleware group
        $this->authorize('viewAny', ActivityLog::class);

        $tenantId = app('tenant')->id;

        $today = now()->startOfDay();
        $tomorrow = now()->endOfDay();

        $activitiesToday = Activity::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('completed_at')
            ->whereBetween('due_at', [$today, $tomorrow])
            ->with(['person', 'deal'])
            ->orderBy('due_at')
            ->get();

        $activitiesOverdue = Activity::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('completed_at')
            ->where('due_at', '<', $today)
            ->with(['person', 'deal'])
            ->orderBy('due_at')
            ->get();

        $activitiesUpcoming = Activity::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('completed_at')
            ->where('due_at', '>', $tomorrow)
            ->with(['person', 'deal'])
            ->orderBy('due_at')
            ->limit(20)
            ->get();

        $activities = Activity::query()
            ->where('tenant_id', $tenantId)
            ->with(['person', 'deal'])
            ->orderByRaw('completed_at IS NULL DESC') // pendentes primeiro
            ->orderBy('due_at')
            ->latest()
            ->limit(100)
            ->get();

        $people = Person::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        $deals = Deal::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('title')
            ->get(['id', 'title', 'status']);

        return Inertia::render('activities/Index', [
            'activities' => $activities,
            'people' => $people,
            'deals' => $deals,
            'calendar' => [
                'today' => $activitiesToday,
                'overdue' => $activitiesOverdue,
                'upcoming' => $activitiesUpcoming,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('viewAny', ActivityLog::class);

        $data = $request->validate([
            'type' => ['required', 'string', 'in:call,meeting,email,task'],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'person_id' => ['nullable', 'integer'],
            'deal_id' => ['nullable', 'integer'],
        ]);

        $activity = Activity::create([
            'tenant_id' => app('tenant')->id,
            'created_by' => auth()->id(),
            'type' => $data['type'],
            'title' => $data['title'],
            'notes' => $data['notes'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'person_id' => $data['person_id'] ?? null,
            'deal_id' => $data['deal_id'] ?? null,
        ]);

        // ✅ timeline log
        activity_log('activity.created', null, [
            'title' => $activity->title,
            'type' => $activity->type,
            'due_at' => optional($activity->due_at)->toISOString(),
            'person_id' => $activity->person_id,
            'deal_id' => $activity->deal_id,
        ]);

        return back();
    }

    public function complete(Activity $activity)
    {
        $this->authorize('viewAny', ActivityLog::class);

        abort_if($activity->tenant_id !== app('tenant')->id, 404);

        $activity->update([
            'completed_at' => now(),
        ]);

        activity_log('activity.completed', null, [
            'title' => $activity->title,
            'type' => $activity->type,
        ]);

        return back();
    }

    public function calendar()
    {
        $this->authorize('viewAny', ActivityLog::class);

        $tenantId = app('tenant')->id;

        // Minimal: puxar atividades com due_at (quando existir) e fallback em created_at
        // Inclui person/deal para enriquecer título (sem exageros)
        $activities = Activity::query()
            ->where('tenant_id', $tenantId)
            ->with(['person', 'deal'])
            ->latest()
            ->limit(500)
            ->get();

        $events = $activities->map(function ($a) {
            $start = $a->due_at ?? $a->created_at;

            // End opcional (ex.: +30 min) para ficar “visível” em week/day view
            $end = $a->due_at
                ? optional($a->due_at)->copy()->addMinutes(30)
                : null;

            $who = $a->person
                ? trim(($a->person->first_name ?? '') . ' ' . ($a->person->last_name ?? ''))
                : null;

            $deal = $a->deal?->title;

            $suffix = collect([$who, $deal])->filter()->implode(' • ');

            return [
                'id' => $a->id,
                'title' => $suffix ? "{$a->title} — {$suffix}" : $a->title,
                'start' => optional($start)->toISOString(),
                'end' => $end ? $end->toISOString() : null,
                'allDay' => false,
                'extendedProps' => [
                    'type' => $a->type,
                    'completed_at' => $a->completed_at,
                ],
                // opcional: ir para a página de activities (ou futuramente abrir modal)
                'url' => '/activities',
            ];
        })->values();

        return Inertia::render('calendar/Index', [
            'events' => $events,
        ]);
    }

    public function show(\App\Models\Activity $activity)
    {
        abort_if($activity->tenant_id !== app('tenant')->id, 403);

        return inertia('activities/Show', [
            'activity' => $activity->load(['person', 'deal']),
        ]);
    }

}
