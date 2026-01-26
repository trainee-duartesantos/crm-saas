<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInvite;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $tenant = app('tenant');

        return Inertia::render('Users/Index', [
            'users' => User::where('tenant_id', $tenant->id)->get(),
            'invites' => UserInvite::where('tenant_id', $tenant->id)
                ->get()
                ->map(fn ($invite) => [
                    ...$invite->toArray(),
                    'is_expired' => $invite->isExpired(),
                ]),
        ]);
    }
}
