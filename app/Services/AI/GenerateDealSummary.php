<?php

namespace App\Services\AI;

use App\Models\Deal;
use App\Services\DealTimelineBuilder;

class GenerateDealSummary
{
    public function generate(Deal $deal): array
    {
        $timeline = app(DealTimelineBuilder::class)->build($deal)['items'];

        // Aqui futuramente podes trocar por LLM real
        // Para já: heuristic + prompt-like logic
        $lastItems = collect($timeline)->take(6);

        $summary = "This deal is currently in '{$deal->status}' stage. ";

        if ($lastItems->contains(fn ($i) => $i['type'] === 'ai')) {
            $summary .= "AI insights were recently generated. ";
        }

        if ($deal->status === 'proposal') {
            $summary .= "A proposal stage deal typically requires sending or following up on proposals. ";
        }

        $summary .= "Recent activity suggests attention is needed to keep momentum.";

        return [
            'type' => 'ai',
            'icon' => '🧠',
            'title' => 'AI summary',
            'description' => $summary,
            'date' => now(),
            'meta' => [
                'confidence' => 0.9,
            ],
        ];
    }
}
