<?php

namespace App\Policies;

use App\Models\User;

class UserInvitePolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'owner']);
    }
}
