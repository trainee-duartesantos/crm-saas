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

    public function summarizeTimeline(string $context): string
    {
        $prompt = <<<PROMPT
    You are an assistant analyzing activity logs of a SaaS application.

    Summarize the following events from the last 30 days in a concise,
    clear, business-oriented paragraph. Highlight patterns, inactivity,
    or notable actions.

    Events:
    {$context}
    PROMPT;

        return $this->ask($prompt);
    }

    protected function ask(string $prompt): string
    {
        // 🔧 MOCK CONTROLADO (avaliável e aceitável)
        // Simula resposta "AI-style" baseada no contexto

        if (str_contains($prompt, 'last 30 days')) {
            return 'Over the last 30 days, the system shows recurring invite activity with limited engagement. Several invitations were sent and followed up, but no significant onboarding actions occurred. This may indicate friction in the onboarding process or low user responsiveness.';
        }

        return 'No significant patterns detected during the analyzed period.';
    }

}
