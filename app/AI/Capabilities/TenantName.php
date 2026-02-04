<?php

namespace App\AI\Capabilities;

use App\Models\Tenant;
use App\Models\User;

class TenantName implements Capability
{
    public function key(): string
    {
        return 'tenant.name';
    }

    public function description(): string
    {
        return 'Nome do tenant atual';
    }

    public function handle(Tenant $tenant, User $user, array $params = []): array
    {
        return [
            'answer' => "O nome do tenant é **{$tenant->name}**.",
            'cards' => [],
            'quick_actions' => [],
        ];
    }
}
