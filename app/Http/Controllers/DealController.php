<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Activity;
use App\Models\DealFollowUp;

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

        $activities = $deal->activities()
            ->latest()
            ->get()
            ->map(fn ($a) => [
                'type' => 'activity',
                'icon' => match ($a->type) {
                    'call' => '📞',
                    'meeting' => '🤝',
                    'email' => '✉️',
                    default => '✅',
                },
                'title' => $a->title,
                'description' => $a->notes,
                'date' => $a->created_at,
            ]);

        $proposals = $deal->proposals()
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'type' => 'proposal',
                'icon' => '📄',
                'title' => $p->original_name,
                'description' => $p->sent_at
                    ? 'Proposal sent by email'
                    : 'Proposal uploaded',
                'date' => $p->sent_at ?? $p->created_at,
            ]);

        $logs = ActivityLog::query()
            ->where('tenant_id', app('tenant')->id)
            ->where('subject_type', $deal->getMorphClass())
            ->where('subject_id', $deal->id)
            ->latest()
            ->get()
            ->map(fn ($l) => [
                'type' => 'log',
                'icon' => '🕒',
                'title' => str_replace('.', ' ', ucfirst($l->action)),
                'description' => null,
                'date' => $l->created_at,
            ]);

        $timeline = collect()
            ->merge($activities)
            ->merge($proposals)
            ->merge($logs)
            ->sortByDesc('date')
            ->values();

        $followUp = DealFollowUp::where('deal_id', $deal->id)
            ->where('active', true)
            ->first();

        return Inertia::render('deals/Show', [
            'deal' => $deal->load(['person', 'entity']),
            'timeline' => $timeline,
            'followUp' => $followUp,
        ]);
    }

}
