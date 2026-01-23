<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return in_array($authUser->role, ['owner', 'admin']);
    }

    public function create(User $authUser): bool
    {
        return in_array($authUser->role, ['owner', 'admin']);
    }

    public function update(User $authUser, User $targetUser): bool
    {
        if ($authUser->tenant_id !== $targetUser->tenant_id) {
            return false;
        }

        if ($targetUser->role === 'owner' && $authUser->role !== 'owner') {
            return false;
        }

        return in_array($authUser->role, ['owner', 'admin']);
    }

    public function delete(User $authUser, User $targetUser): bool
    {
        return $this->update($authUser, $targetUser);
    }
}
