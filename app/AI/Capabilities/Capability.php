<?php

namespace App\AI\Capabilities;

use App\Models\Tenant;
use App\Models\User;

interface Capability
{
    public function key(): string;

    public function description(): string;

    public function handle(Tenant $tenant, User $user, array $params = []): array;
}
