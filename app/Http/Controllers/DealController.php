<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Activity;
use App\Models\DealFollowUp;
use App\Services\DealTimelineBuilder;

class DealController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', ActivityLog::class);

        $tenant = app('tenant');

        $deals = Deal::query()
            ->where('tenant_id', $tenant->id)
            ->with('person')
            ->get()
            ->groupBy('status');

        return Inertia::render('deals/Index', [
            'statuses' => Deal::STATUSES,
            'deals' => $deals,
        ]);
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');

        $data = $request->validate([
            'title' => ['required', 'string'],
            'value' => ['nullable', 'numeric'],
            'person_id' => ['nullable', 'exists:people,id'],
        ]);

        $deal = Deal::create([
            'tenant_id' => $tenant->id,
            'title' => $data['title'],
            'value' => $data['value'] ?? null,
            'person_id' => $data['person_id'] ?? null,
            'status' => 'lead',
            'last_activity_at' => now(),
        ]);

        activity_log('deal.created', $deal, [
            'title' => $deal->title,
        ]);

        return back();
    }

    public function move(Request $request, Deal $deal)
    {
        $this->authorize('viewAny', ActivityLog::class);

        $request->validate([
            'status' => ['required', 'in:' . implode(',', Deal::STATUSES)],
        ]);

        $oldStatus = $deal->status;

        $deal->update([
            'status' => $request->status,
            'last_activity_at' => now(),
        ]);

        activity_log('deal.status.changed', $deal, [
            'from' => $oldStatus,
            'to' => $deal->status,
            'title' => $deal->title,
        ]);

        if ($request->status === 'proposal' && $oldStatus !== 'proposal') {
            DealFollowUp::create([
                'tenant_id' => $deal->tenant_id,
                'deal_id' => $deal->id,
                'next_run_at' => nextWorkTime(
                    now()->addDays(config('followups.interval_days'))
                ),
            ]);

            activity_log('deal.follow_up.started', $deal);
        }

        return back();
    }

    public function show(Deal $deal)
    {
        $this->authorize('viewAny', ActivityLog::class);

        abort_if($deal->tenant_id !== app('tenant')->id, 403);

        $deal->load(['person', 'entity']);

        $types = request()->filled('types')
            ? explode(',', request('types'))
            : null;

        $q = request('q');

        $timeline = app(\App\Services\DealTimelineBuilder::class)
            ->build($deal, $types, $q);

        $followUp = $deal->followUps()
            ->where('active', true)
            ->latest('next_run_at')
            ->first();

        return Inertia::render('deals/Show', [
            'deal' => $deal,
            'timeline' => $timeline['items'],
            'timeline_counts' => $timeline['counts'],
            'followUp' => $followUp,
        ]);
    }


    public function followUps()
    {
        return $this->hasMany(\App\Models\DealFollowUp::class);
    }

    public function timeline(Request $request, Deal $deal)
    {
        $this->authorize('viewAny', ActivityLog::class);
        abort_if($deal->tenant_id !== app('tenant')->id, 403);

        $types = $request->input('types');
        $q = $request->input('q');

        $timeline = app(DealTimelineBuilder::class)
            ->build($deal, $types, $q);

        $followUp = $deal->followUps()
            ->where('active', true)
            ->latest('next_run_at')
            ->first();

        return response()->json([
            'items' => $timeline['items'],
            'counts' => $timeline['counts'],
            'followUp' => $followUp,
        ]);
    }
}
