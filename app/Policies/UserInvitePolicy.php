<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserInvite;

class UserInvitePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'owner'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'owner'], true);
    }

    public function resend(User $user, UserInvite $invite): bool
    {
        return in_array($user->role, ['admin', 'owner'], true)
            && $user->tenant_id === $invite->tenant_id
            && ! $invite->isAccepted();
    }

    public function delete(User $user, UserInvite $invite): bool
    {
        return in_array($user->role, ['admin', 'owner'], true)
            && $user->tenant_id === $invite->tenant_id
            && ! $invite->isAccepted();
    }

    public function inviteUsers(User $user): bool
    {
        return in_array($user->role, ['admin', 'owner']);
    }

    public function manage(User $user, UserInvite $invite): bool
    {
        // Apenas Admin ou Owner do mesmo tenant
        return in_array($user->role, ['admin', 'owner'])
            && $user->tenant_id === $invite->tenant_id;
    }
}
