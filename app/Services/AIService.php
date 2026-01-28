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

            'activity_follow_up' =>
                "The activity '{$data['title']}' is overdue. "
                . "Consider reaching out via {$data['type']} to maintain momentum.",

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

    public function askExecutiveInsight(string $context): string
    {
        // 🔧 Mock inteligente e defensável
        return "Based on the current tenant metrics, invitation activity is present but engagement appears uneven. "
            . "While some users have completed onboarding, a notable number of invitations remain pending. "
            . "Recent activity levels suggest moderate engagement. "
            . "Improving follow-up communication and onboarding clarity could positively impact adoption.";
    }

    public function generateTenantInsight(array $metrics): string
    {
        return match (true) {
            $metrics['invites_pending'] > 5 =>
                'Engagement appears low due to several pending invitations older than one week, which may indicate onboarding friction.',

            $metrics['ai_events_total'] > 5 =>
                'The tenant shows active usage of AI features, suggesting strong engagement with the platform.',

            default =>
                'The tenant demonstrates stable activity with no critical risks detected.',
        };
    }

}
