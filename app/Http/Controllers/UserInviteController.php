<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserInviteController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
        $this->authorize('create', UserInvite::class);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'role'  => ['required', 'in:user,admin'],
        ]);

        $invite = UserInvite::create([
            'tenant_id' => auth()->user()->tenant_id,
            'email' => $data['email'],
            'role' => $data['role'],
            'token' => Str::uuid(),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invite->email)->send(
            new UserInviteMail($invite)
        );

        return back()->with('success', 'Invite sent');
    }
}
