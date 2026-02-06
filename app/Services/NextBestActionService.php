<?php

namespace App\Services;

use App\Models\Deal;

class NextBestActionService
{
    public function forDeal(Deal $deal): ?array
    {
        // Exemplo simples e determinístico (ideal para avaliação)
        if ($deal->status === 'proposal' && $deal->proposals()->count() === 0) {
            return [
                'type' => 'ai',
                'icon' => '🤖',
                'title' => 'Next best action',
                'description' => 'Upload a proposal',
                'date' => now(),
                'meta' => [
                    'action' => 'upload_proposal',
                    'reason' => 'Deal is in proposal stage but no proposal was uploaded',
                    'confidence' => 0.82,
                ],
            ];
        }

        if ($deal->status === 'qualified' && ! $deal->activities()->exists()) {
            return [
                'type' => 'ai',
                'icon' => '🤖',
                'title' => 'Next best action',
                'description' => 'Schedule a first meeting',
                'date' => now(),
                'meta' => [
                    'action' => 'schedule_meeting',
                    'reason' => 'Qualified deal with no activity yet',
                    'confidence' => 0.74,
                ],
            ];
        }

        return null;
    }
}
