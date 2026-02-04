<?php

namespace App\Services\AI;

use App\AI\IntentResolver;
use App\Models\User;
use App\Models\Tenant;

class ChatEngine
{
    public function interpret(string $message, Tenant $tenant, User $user): array
    {
        return IntentResolver::resolve($message, $tenant, $user);
    }
}
