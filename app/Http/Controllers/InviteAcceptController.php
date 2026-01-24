<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InviteAcceptController extends Controller
{
    public function show(string $token)
    {
        $invite = UserInvite::where('token', $token)->firstOrFail();

        abort_if($invite->isExpired(), 403);

        return Inertia::render('Auth/AcceptInvite', [
            'email' => $invite->email,
            'token' => $token,
        ]);
    }

    public function store(Request $request)
    {
        $invite = UserInvite::where('token', $request->token)->firstOrFail();

        abort_if($invite->isExpired(), 403);

        $user = User::create([
            'name' => $request->name,
            'email' => $invite->email,
            'password' => Hash::make($request->password),
            'tenant_id' => $invite->tenant_id,
            'role' => $invite->role,
        ]);

        $invite->delete();

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
