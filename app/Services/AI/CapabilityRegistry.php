<?php

namespace App\Services\AI;

use App\Models\Tenant;
use App\Models\User;

class CapabilityRegistry
{
    public static function run(string $intent, Tenant $tenant, User $user, array $params = []): array
    {
        return match ($intent) {
            'invites.pending.count' => [
                'answer' => 'Ainda não liguei a base de dados.',
                'cards' => [],
                'quick_actions' => [],
            ],

            'tenant.name' => [
                'answer' => "O nome do tenant é {$tenant->name}.",
                'cards' => [],
                'quick_actions' => [],
            ],

            default => [
                'answer' => 'Intent reconhecido, mas ainda não implementado.',
                'cards' => [],
                'quick_actions' => [],
            ],
        };
    }
}
