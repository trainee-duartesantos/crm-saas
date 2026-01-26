<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InviteAcceptController extends Controller
{
    public function show(string $token)
    {
        $invite = UserInvite::where('token', $token)->firstOrFail();

        if ($invite->isExpired() || $invite->isAccepted()) {
            abort(403, 'Invitation is invalid or expired.');
        }

        return Inertia::render('auth/AcceptInvite', [
            'email' => $invite->email,
            'token' => $invite->token,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
            'token' => ['required'],
        ]);

        $invite = UserInvite::where('token', $validated['token'])->firstOrFail();

        if ($invite->isExpired() || $invite->isAccepted()) {
            abort(403, 'Invitation is invalid or expired.');
        }

        DB::transaction(function () use ($invite, $validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $invite->email,
                'password' => Hash::make($validated['password']),
                'role' => $invite->role,
                'tenant_id' => $invite->tenant_id,
                'email_verified_at' => now(),
            ]);

            $invite->update([
                'accepted_at' => now(),
            ]);

            Auth::login($user);
        });

        return redirect()->route('dashboard');
    }
}
