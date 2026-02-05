<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Deal;
use Illuminate\Support\Collection;

class DealTimelineBuilder
{
    public function build(
        Deal $deal,
        ?array $types = null,
        ?string $q = null
    ): array {
        $items = collect();

        // 1) Activities (tarefas, calls, meetings, emails, etc)
        $activities = $deal->activities()
            ->latest()
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
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
                'meta' => [
                    'activity_id' => $a->id,
                    'activity_type' => $a->type,
                    'due_at' => $a->due_at,
                    'completed_at' => $a->completed_at,
                ],
            ]);

        // 2) Proposals (upload / sent)
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
                'meta' => [
                    'proposal_id' => $p->id,
                    'sent_at' => $p->sent_at,
                    'original_name' => $p->original_name,
                ],
            ]);

        // 3) Activity logs (status changed, follow-up started, cancelled, etc)
        $logs = ActivityLog::query()
            ->where('tenant_id', $deal->tenant_id)
            ->where('subject_type', $deal->getMorphClass())
            ->where('subject_id', $deal->id)
            ->latest()
            ->get()
            ->map(fn ($l) => [
                'type' => 'log',
                'icon' => '🕒',
                'title' => $l->action,
                'description' => null,
                'date' => $l->created_at,
                'meta' => [
                    'log_id' => $l->id,
                    'action' => $l->action,
                    'metadata' => $l->metadata,
                    'actor_id' => $l->actor_id,
                ],
            ]);

        $items = $items
            ->merge($activities)
            ->merge($proposals)
            ->merge($logs);

        // ---- filtros opcionais ----
        if ($types && count($types)) {
            $types = array_values(array_unique(array_map('strtolower', $types)));
            $items = $items->filter(fn ($i) => in_array(strtolower($i['type']), $types, true));
        }

        if ($q && trim($q) !== '') {
            $needle = mb_strtolower(trim($q));
            $items = $items->filter(function ($i) use ($needle) {
                $hay = mb_strtolower(
                    ($i['title'] ?? '') . ' ' . ($i['description'] ?? '') . ' ' . json_encode($i['meta'] ?? [])
                );
                return str_contains($hay, $needle);
            });
        }

        $items = $items->sortByDesc('date')->values();

        $counts = $items->countBy('type')->toArray();

        return [
            'items' => $items,
            'counts' => $counts,
        ];
    }
}
