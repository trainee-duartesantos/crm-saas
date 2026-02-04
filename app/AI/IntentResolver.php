<?php

namespace App\AI;

use App\Models\User;
use App\Models\Tenant;
use App\Services\AI\OpenAIIntentClassifier;

class IntentResolver
{
    public static function resolve(string $message, Tenant $tenant, User $user): array
    {
        try {
            return app(OpenAIIntentClassifier::class)->classify($message, $tenant, $user);
        } catch (\Throwable $e) {
            // fallback simples (para não rebentar em produção/dev)
            $text = mb_strtolower($message);

            if (str_contains($text, 'convite')) {
                return ['intent' => 'invites.pending.count', 'confidence' => 0.40, 'parameters' => [], 'clarifying_question' => null];
            }

            if (str_contains($text, 'tenant') || str_contains($text, 'empresa')) {
                return ['intent' => 'tenant.name', 'confidence' => 0.40, 'parameters' => [], 'clarifying_question' => null];
            }

            if (str_contains($text, 'negócio') || str_contains($text, 'deal')) {
                return [
                    'intent' => 'deal_list_by_status',
                    'confidence' => 0.30,
                    'parameters' => ['status' => 'lead', 'limit' => 10],
                    'clarifying_question' => 'Queres negócios em que estado? (lead/qualified/proposal/won/lost)',
                ];
            }

            return [
                'intent' => 'unknown',
                'confidence' => 0.0,
                'parameters' => [],
                'clarifying_question' => 'Podes reformular? Ex: "Quantos convites pendentes?" ou "Negócios em proposal".',
            ];
        }
    }
}
