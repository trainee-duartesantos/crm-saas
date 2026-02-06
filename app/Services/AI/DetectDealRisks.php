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
                    'risk' => true,
                    'risk_code' => 'stalled_deal',
                    'severity' => 'high',
                    'confidence' => 0.78,
                    'reason' => 'No activity in the last 7 days',
                    'action' => [
                        'label' => 'Create follow-up activity',
                        'method' => 'post',
                        'endpoint' => '/activities',
                        'payload' => [
                            'deal_id' => $deal->id,
                            'title' => 'Follow up deal',
                            'type' => 'call',
                        ],
                    ],
                ],
            ];
        }

        // 2) Proposal enviada sem resposta
        $lastProposal = $deal->proposals()
            ->whereNotNull('sent_at')
            ->latest('sent_at')
            ->first();

        if ($lastProposal && Carbon::parse($lastProposal->sent_at)->diffInDays(now()) >= 5) {
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
                    'action' => [
                        'label' => 'Create follow-up activity',
                        'type' => 'create_activity',
                        'endpoint' => "/activities",
                        'method' => 'post',
                        'payload' => [
                            'title' => 'Follow up on proposal',
                            'deal_id' => $deal->id,
                            
                        ],
                    ],
                ],
            ];
        }

        return $risks;
    }
}
