<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    public function suggest(string $context, array $data = []): string
    {
        // 🔧 MOCK inicial (aceitável e inteligente)
        // Mais tarde ligamos a OpenAI real
        return match ($context) {
            'invite_pending' =>
                'Consider sending a follow-up email to increase the response rate.',
            'deal_stalled' =>
                'This deal has had no activity recently. A follow-up might help.',
            default =>
                'Review this item and decide on the next action.',
        };
    }

    public function generateNote(string $summary): string
    {
        return "AI summary: {$summary}";
    }

    public function draftEmail(string $goal): array
    {
        return [
            'subject' => 'Quick follow-up',
            'body' => "Hi,\n\nJust following up regarding {$goal}.\n\nBest regards",
        ];
    }
}
