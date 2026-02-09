<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    public function view(User $user, Deal $deal): bool
    {
        return true;
    }

    public function update(User $user, Deal $deal): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Deal $deal): bool
    {
        return $user->isOwner();
    }

    public function markAsWon(User $user, Deal $deal): bool
    {
        return $user->isOwner();
    }
}
