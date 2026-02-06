<?php

namespace App\Services\AI;

use App\Models\Deal;
use Carbon\Carbon;

class DetectDealRisks
{
    public function detect(Deal $deal): array
    {
        $risks = [];

        // 1) Deal parado (sem atividade)
        if (
            $deal->last_activity_at &&
            Carbon::parse($deal->last_activity_at)->diffInDays(now()) >= 7
        ) {
            $risks[] = [
                'type' => 'ai',
                'icon' => '⚠️',
                'title' => 'Risk detected',
                'description' => 'Deal has no activity in the last 7 days',
                'date' => now(),
                'meta' => [
                    'risk' => 'stalled_deal',
                    'severity' => 'high',
                    'confidence' => 0.78,
                    'reason' => 'No activities or status changes recently',
                ],
            ];
        }

        // 2) Proposal enviada sem resposta
        $lastProposal = $deal->proposals()
            ->whereNotNull('sent_at')
            ->latest('sent_at')
            ->first();

        if (
            $lastProposal &&
            Carbon::parse($lastProposal->sent_at)->diffInDays(now()) >= 5
        ) {
            $risks[] = [
                'type' => 'ai',
                'icon' => '⚠️',
                'title' => 'Risk detected',
                'description' => 'Proposal sent but no response yet',
                'date' => now(),
                'meta' => [
                    'risk' => 'proposal_no_reply',
                    'severity' => 'medium',
                    'confidence' => 0.71,
                    'reason' => 'Proposal sent more than 5 days ago',
                ],
            ];
        }

        return $risks;
    }
}
