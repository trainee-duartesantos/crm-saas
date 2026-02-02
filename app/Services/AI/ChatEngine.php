<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class ChatEngine
{
    public function interpret(string $message, array $context): array
    {
        // ⚠️ Nota: o modelo exato depende da tua config.
        // Usa env/services para trocar sem mexer no código.
        $model = config('services.openai.model', 'gpt-5-nano');

        $system = <<<SYS
You are a CRM intent parser.
Return ONLY valid JSON. No markdown. No extra text.

Your job:
- understand the user's question in Portuguese
- convert it into a structured intent for a CRM SaaS

Allowed intents:
1) deal_volume_by_status
   fields: status (lead|qualified|proposal|won|lost)
2) deal_list_by_status
   fields: status, limit (1..20)
3) person_phone_lookup
   fields: full_name (string)
4) activity_suggest_followups
   fields: none
5) create_activity
   fields: type (task|call|meeting|email), title, due_at (nullable ISO), deal_id (nullable int), person_id (nullable int), notes (nullable string)

Rules:
- If status is "Em negociação" or similar → map to "proposal" (best effort).
- If the question is unclear, return intent "unknown" with a clarifying_question.

JSON format:
{
  "intent": "...",
  "confidence": 0.0-1.0,
  "parameters": { ... },
  "clarifying_question": null|string
}
SYS;

        $payload = [
            'model' => $model,
            'input' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => "Context: " . json_encode($context) . "\nQuestion: " . $message],
            ],
        ];

        // Se já tens integração OpenAI noutra parte, adapta este call
        $res = Http::withToken(config('services.openai.key'))
            ->post(config('services.openai.url', 'https://api.openai.com/v1/responses'), $payload);

        if (!$res->ok()) {
            return [
                'intent' => 'unknown',
                'confidence' => 0.0,
                'parameters' => [],
                'clarifying_question' => 'Não consegui processar agora. Podes repetir?',
                'error' => $res->json(),
            ];
        }

        // A resposta muda conforme o endpoint; aqui fazemos defensivo.
        $text = $res->json('output_text') ?? $res->json('choices.0.message.content') ?? null;

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
                'clarifying_question' => 'Podes dizer isso de outra forma? Ex: "Volume em proposal" ou "telemóvel do António".',
                'raw' => $text,
            ];
        }

        return [
            'intent' => $decoded['intent'],
            'confidence' => (float) ($decoded['confidence'] ?? 0.5),
            'parameters' => $decoded['parameters'] ?? [],
            'clarifying_question' => $decoded['clarifying_question'] ?? null,
        ];
    }
}
