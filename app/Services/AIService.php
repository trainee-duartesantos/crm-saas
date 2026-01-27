<?php

namespace App\Services;

class AIService
{
    /**
     * Gera sugestões ou alertas com base num contexto semântico.
     * Esta função representa o "cérebro" da AI.
     */
    public function suggest(string $context, array $data = []): string
    {
        return match ($context) {

            // 📩 Convites pendentes
            'invite_pending' =>
                'Consider sending a follow-up email to increase the response rate.',

            // ⚠️ Risco: convites antigos sem resposta
            'invite_risk' =>
                "There are {$data['count']} pending invitations older than 7 days. "
                . "This may indicate onboarding friction or low engagement. "
                . "A follow-up or onboarding review is recommended.",

            // 🧠 Fallback
            default =>
                'Review this situation and decide on the next appropriate action.',
        };
    }

    /**
     * Resume eventos da timeline num texto executivo.
     * Mantido separado porque o input é grande e contextual.
     */
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

    /**
     * Gera rascunho de email (ex: follow-up).
     */
    public function draftEmail(string $goal): array
    {
        return [
            'subject' => 'Quick follow-up',
            'body' => "Hi,\n\nJust following up regarding {$goal}.\n\nBest regards",
        ];
    }

    /**
     * Motor interno de resposta AI (mock controlado).
     * Aqui é onde no futuro entra OpenAI / LLM real.
     */
    protected function ask(string $prompt): string
    {
        // 🔧 MOCK CONTROLADO E EXPLICÁVEL (avaliável)
        if (str_contains($prompt, 'last 30 days')) {
            return 'Over the last 30 days, the system shows recurring invite activity '
                . 'with limited engagement. Several invitations were sent and followed up, '
                . 'but no significant onboarding actions occurred. '
                . 'This may indicate friction in the onboarding process or low user responsiveness.';
        }

        return 'No significant patterns were detected during the analyzed period.';
    }
}
