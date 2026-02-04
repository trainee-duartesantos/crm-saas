<?php

namespace App\AI\Capabilities;

use App\Models\Deal;
use App\Models\Tenant;
use App\Models\User;

class DealsByStatus implements Capability
{
    public function key(): string
    {
        return 'deals.by_status';
    }

    public function description(): string
    {
        return 'Negócios agrupados por estado';
    }

    public function handle(Tenant $tenant, User $user, array $params = []): array
    {
        $data = Deal::where('tenant_id', $tenant->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $lines = $data->map(fn ($v, $k) => ucfirst($k).": {$v}")->implode(', ');

        return [
            'answer' => "Resumo de negócios por estado: {$lines}.",
            'cards' => [],
            'quick_actions' => [],
        ];
    }
}
