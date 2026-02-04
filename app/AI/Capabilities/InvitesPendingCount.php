<?php

namespace App\AI\Capabilities;

use App\Models\Invite;
use App\Models\Tenant;
use App\Models\User;

class InvitesPendingCount implements Capability
{
    public function key(): string
    {
        return 'invites.pending.count';
    }

    public function description(): string
    {
        return 'Número de convites pendentes';
    }

    public function handle(Tenant $tenant, User $user, array $params = []): array
    {
        $count = Invite::where('tenant_id', $tenant->id)
            ->whereNull('accepted_at')
            ->count();

        return [
            'answer' => "Existem {$count} convites pendentes.",
            'cards' => [],
            'quick_actions' => [
                [
                    'label' => 'Ver convites',
                    'action' => 'open',
                    'payload' => ['href' => route('users.index')],
                ],
            ],
        ];
    }
}
