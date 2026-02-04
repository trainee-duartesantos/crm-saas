<?php

namespace App\Services\AI;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class OpenAIIntentClassifier
{
    public function classify(string $message, Tenant $tenant, User $user): array
    {
        $model = config('services.openai.model', 'gpt-5-nano');
        $url   = config('services.openai.url', 'https://api.openai.com/v1/responses');
        $key   = config('services.openai.key');

        $system = <<<SYS
You are a CRM intent parser.
Return ONLY valid JSON. No markdown. No extra text.

Understand Portuguese questions and map into one of these intents:

1) invites_pending_count
   fields: none

2) tenant_name
   fields: none

3) deal_volume_by_status
   fields: status (lead|qualified|proposal|won|lost)

4) deal_list_by_status
   fields: status, limit (1..20)

5) person_phone_lookup
   fields: full_name (string)

If unclear, return intent "unknown" with a clarifying_question.

JSON format:
{
  "intent": "...",
  "confidence": 0.0-1.0,
  "parameters": { ... },
  "clarifying_question": null|string
}
SYS;

        // Contexto mínimo e seguro
        $context = [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'page' => 'insights',
        ];

        $payload = [
            'model' => $model,
            'input' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => "Context: " . json_encode($context) . "\nQuestion: " . $message],
            ],
        ];

        $res = Http::withToken($key)
            ->acceptJson()
            ->asJson()
            ->post($url, $payload);

        logger()->debug('OpenAI raw response', [
            'status' => $res->status(),
            'body' => $res->json(),
        ]);

        if (!$res->ok()) {
            return [
                'intent' => 'unknown',
                'confidence' => 0.0,
                'parameters' => [],
                'clarifying_question' => 'Não consegui processar agora. Podes repetir?',
                'error' => $res->json(),
            ];
        }

        $text = null;

        foreach ($res->json('output', []) as $item) {
            if (($item['type'] ?? null) === 'message') {
                foreach ($item['content'] ?? [] as $content) {
                    if (($content['type'] ?? null) === 'output_text') {
                        $text = $content['text'] ?? null;
                        break 2;
                    }
                }
            }
        }


        if (!$text) {
            return [
                'intent' => 'unknown',
                'confidence' => 0.0,
                'parameters' => [],
                'clarifying_question' => 'Não consegui interpretar a pergunta. Podes reformular?',
            ];
        }

        $decoded = json_decode($text, true);

        if (!is_array($decoded) || empty($decoded['intent'])) {
            return [
                'intent' => 'unknown',
                'confidence' => 0.0,
                'parameters' => [],
                'clarifying_question' => 'Podes dizer isso de outra forma? Ex: "Quantos convites pendentes?"',
                'raw' => $text,
            ];
        }

        // Normalizar intents (mapeia para o que o teu CapabilityRegistry espera)
        $intent = $decoded['intent'];

        $map = [
            'invites_pending_count' => 'invites.pending.count',
            'tenant_name' => 'tenant.name',
        ];

        return [
            'intent' => $map[$intent] ?? $intent,
            'confidence' => (float) ($decoded['confidence'] ?? 0.5),
            'parameters' => $decoded['parameters'] ?? [],
            'clarifying_question' => $decoded['clarifying_question'] ?? null,
        ];
    }
}
