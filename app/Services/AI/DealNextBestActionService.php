<?php

namespace App\Services\AI;

use App\Models\Deal;
use Carbon\Carbon;

class DealNextBestActionService
{
    public function forDeal(Deal $deal): ?array
    {
        $lastActivityAt = $deal->last_activity_at
            ? Carbon::parse($deal->last_activity_at)
            : null;

        // 1️⃣ No activity recently → follow-up
        if ($lastActivityAt && $lastActivityAt->diffInDays(now()) >= 5) {
            return $this->action(
                'send_follow_up',
                'Send a follow-up email',
                'No activity in the last 5 days',
                0.78
            );
        }

        // 2️⃣ Proposal stage but no proposal sent
        if ($deal->status === 'proposal' && $deal->proposals()->count() === 0) {
            return $this->action(
                'upload_proposal',
                'Upload a proposal',
                'Deal is in proposal stage but no proposal was uploaded',
                0.82
            );
        }

        // 3️⃣ Proposal sent but no reply
        if ($deal->status === 'proposal' &&
            $deal->proposals()->whereNotNull('sent_at')->exists() &&
            $lastActivityAt &&
            $lastActivityAt->diffInDays(now()) >= 3
        ) {
            return $this->action(
                'follow_up_proposal',
                'Follow up on proposal',
                'Proposal sent but no response yet',
                0.74
            );
        }

        // 4️⃣ Qualified & active → schedule call
        if ($deal->status === 'qualified') {
            return $this->action(
                'schedule_call',
                'Schedule a call',
                'Deal is qualified and active',
                0.70
            );
        }

        return null;
    }

    protected function action(
        string $action,
        string $title,
        string $reason,
        float $confidence
    ): array {
        return [
            'type' => 'ai',
            'icon' => '🤖',
            'title' => 'Next best action',
            'description' => $title,
            'date' => now(),
            'meta' => [
                'action' => $action,
                'reason' => $reason,
                'confidence' => $confidence,
            ],
        ];
    }
}
