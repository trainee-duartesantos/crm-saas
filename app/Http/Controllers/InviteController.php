<?php

namespace App\Http\Controllers;

use App\Models\UserInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\UserInviteMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InviteController extends Controller
{
    /**
     * Mostrar formulário de convite
     */
    public function create()
    {
        $this->authorize('inviteUsers');

        return Inertia::render('Users/Invite');
    }

    /**
     * Criar convite
     */
    public function store(Request $request)
    {
        $this->authorize('inviteUsers');

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role'  => ['required', 'in:user,admin'],
        ]);

        $tenant = app('tenant');

        /** @var UserInvite $invite */
        $invite = DB::transaction(function () use ($validated, $tenant) {
            return UserInvite::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'email' => $validated['email'],
                ],
                [
                    'role' => $validated['role'],
                    'token' => Str::uuid(),
                    'expires_at' => now()->addDays(7),
                    'accepted_at' => null,
                ]
            );
        });

        Mail::to($invite->email)->send(
            new UserInviteMail($invite)
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'Invitation sent successfully.');
    }
}
