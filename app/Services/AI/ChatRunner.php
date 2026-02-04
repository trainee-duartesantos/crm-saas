<?php

namespace App\Services\AI;

use App\Models\Activity;
use App\Models\Deal;
use App\Models\Person;
use App\Models\UserInvite;

class ChatRunner
{
    public function run(array $intent, array $context): array
    {
        $tenantId = $context['tenant']->id;

        if (($intent['intent'] ?? 'unknown') === 'unknown') {
            return [
                'answer' => $intent['clarifying_question'] ?? 'Não percebi — podes clarificar?',
                'cards' => [],
                'quick_actions' => $this->defaultQuickQuestions(),
            ];
        }

        return match ($intent['intent']) {
            'invites.pending.count' => $this->invitesPendingCount($tenantId),
            'tenant.name' => $this->tenantName($context),

            'deal_volume_by_status' => $this->dealVolumeByStatus($tenantId, $intent),
            'deal_list_by_status' => $this->dealListByStatus($tenantId, $intent),
            'person_phone_lookup' => $this->personPhoneLookup($tenantId, $intent),
            'activity_suggest_followups' => $this->suggestFollowupsHint(),
            'create_activity' => $this->createActivity($tenantId, $intent),
            default => [
                'answer' => 'Ainda não consigo responder a isso. Tenta: "Volume em proposal" ou "telemóvel do António".',
                'cards' => [],
                'quick_actions' => $this->defaultQuickQuestions(),
            ],
        };

    }

    private function dealVolumeByStatus(int $tenantId, array $intent): array
    {
        $status = $intent['parameters']['status'] ?? 'proposal';

        $total = Deal::query()
            ->where('tenant_id', $tenantId)
            ->where('status', $status)
            ->sum('value');

        $count = Deal::query()
            ->where('tenant_id', $tenantId)
            ->where('status', $status)
            ->count();

        $formatted = number_format((float) $total, 2, ',', '.');

        return [
            'answer' => "Tens € {$formatted} distribuídos por {$count} negócio(s) no estado **{$status}**.",
            'cards' => [
                [
                    'title' => "Abrir Pipeline ({$status})",
                    'href' => "/deals",
                    'meta' => ['status' => $status, 'count' => $count],
                ],
            ],
            'quick_actions' => [
                ['label' => 'Listar negócios neste estado', 'action' => 'ask', 'payload' => ['text' => "Lista negócios em {$status}"]],
                ['label' => 'Criar follow-up', 'action' => 'ask', 'payload' => ['text' => "Sugere follow-ups para negócios parados"]],
            ],
        ];
    }

    private function dealListByStatus(int $tenantId, array $intent): array
    {
        $status = $intent['parameters']['status'] ?? 'proposal';
        $limit = (int) ($intent['parameters']['limit'] ?? 10);
        $limit = max(1, min(20, $limit));

        $deals = Deal::query()
            ->where('tenant_id', $tenantId)
            ->where('status', $status)
            ->latest()
            ->limit($limit)
            ->get(['id', 'title', 'value', 'status']);

        $cards = $deals->map(fn ($d) => [
            'title' => $d->title,
            'href' => "/deals/{$d->id}",
            'meta' => [
                'status' => $d->status,
                'value' => $d->value,
            ],
        ])->values()->all();

        if ($deals->isEmpty()) {
            return [
                'answer' => "Não encontrei negócios no estado **{$status}**.",
                'cards' => [],
                'quick_actions' => $this->defaultQuickQuestions(),
            ];
        }

        return [
            'answer' => "Aqui estão os últimos {$deals->count()} negócios em **{$status}**:",
            'cards' => $cards,
            'quick_actions' => [
                ['label' => 'Ver Pipeline', 'action' => 'open', 'payload' => ['href' => '/deals']],
            ],
        ];
    }

    private function personPhoneLookup(int $tenantId, array $intent): array
    {
        $fullName = trim((string) ($intent['parameters']['full_name'] ?? ''));

        if ($fullName === '') {
            return [
                'answer' => 'Diz-me o nome da pessoa. Ex: "telemóvel do António Pinheiro".',
                'cards' => [],
                'quick_actions' => $this->defaultQuickQuestions(),
            ];
        }

        // Match simples: tenta bater em first/last
        $person = Person::query()
            ->where('tenant_id', $tenantId)
            ->whereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ['%' . $fullName . '%'])
            ->first();

        if (!$person) {
            return [
                'answer' => "Não encontrei nenhuma pessoa parecida com **{$fullName}**.",
                'cards' => [],
                'quick_actions' => [
                    ['label' => 'Abrir People', 'action' => 'open', 'payload' => ['href' => '/people']],
                ],
            ];
        }

        $phone = $person->phone ?? null;

        return [
            'answer' => $phone
                ? "O telemóvel de **{$person->first_name} {$person->last_name}** é **{$phone}**."
                : "Encontrei **{$person->first_name} {$person->last_name}**, mas não tem telefone preenchido.",
            'cards' => [
                [
                    'title' => "{$person->first_name} {$person->last_name}",
                    'href' => "/people/{$person->id}",
                    'meta' => ['email' => $person->email ?? null, 'phone' => $phone],
                ],
            ],
            'quick_actions' => [
                ['label' => 'Criar atividade para esta pessoa', 'action' => 'create_activity_prefill', 'payload' => ['person_id' => $person->id]],
            ],
        ];
    }

    private function suggestFollowupsHint(): array
    {
        // Nota: tu já tens /ai/activities/follow-ups
        return [
            'answer' => "Posso sugerir follow-ups com base nas tuas activities. Queres que eu gere sugestões agora?",
            'cards' => [],
            'quick_actions' => [
                ['label' => 'Gerar sugestões agora', 'action' => 'open', 'payload' => ['href' => '/activities']],
            ],
        ];
    }

    private function createActivity(int $tenantId, array $intent): array
    {
        $p = $intent['parameters'] ?? [];

        if (empty($p['title'])) {
            return [
                'answer' => 'Para criar atividade preciso de um título. Ex: "cria uma tarefa para ligar ao cliente amanhã".',
                'cards' => [],
                'quick_actions' => $this->defaultQuickQuestions(),
            ];
        }

        $activity = Activity::create([
            'tenant_id' => $tenantId,
            'type' => $p['type'] ?? 'task',
            'title' => $p['title'],
            'notes' => $p['notes'] ?? null,
            'due_at' => $p['due_at'] ?? null,
            'person_id' => $p['person_id'] ?? null,
            'deal_id' => $p['deal_id'] ?? null,
        ]);

        return [
            'answer' => "Atividade criada ✅ **{$activity->title}**",
            'cards' => [
                [
                    'title' => "Abrir Activities",
                    'href' => "/activities",
                    'meta' => ['created_activity_id' => $activity->id],
                ],
            ],
            'quick_actions' => $this->defaultQuickQuestions(),
        ];
    }

    private function defaultQuickQuestions(): array
    {
        return [
            ['label' => 'Qual o volume em proposal?', 'action' => 'ask', 'payload' => ['text' => 'Qual o volume de negócios no estado proposal?']],
            ['label' => 'Lista negócios em lead', 'action' => 'ask', 'payload' => ['text' => 'Lista negócios no estado lead (10)']],
            ['label' => 'Qual o telemóvel do António Pinheiro?', 'action' => 'ask', 'payload' => ['text' => 'Qual o telemóvel do António Pinheiro?']],
        ];
    }

    private function tenantName(array $context): array
    {
        $tenant = $context['tenant'] ?? null;

        return [
            'answer' => $tenant
                ? "O nome do tenant é **{$tenant->name}**."
                : "Não consegui obter o tenant.",
            'cards' => [],
            'quick_actions' => $this->defaultQuickQuestions(),
        ];
    }

    private function invitesPendingCount(int $tenantId): array
    {
        $count = UserInvite::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->count();

        return [
            'answer' => "Tens **{$count}** convite(s) pendente(s).",
            'cards' => [
                [
                    'title' => 'Gerir Convites',
                    'href' => '/users/invite',
                    'meta' => ['pending' => $count],
                ],
            ],
            'quick_actions' => [
                [
                    'label' => 'Convidar novo utilizador',
                    'action' => 'open',
                    'payload' => ['href' => '/users/invite'],
                ],
            ],
        ];
    }
}
