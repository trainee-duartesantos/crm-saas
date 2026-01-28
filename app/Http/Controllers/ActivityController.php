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
}
