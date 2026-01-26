<?php

namespace App\Http\Controllers;

use App\Mail\UserInviteMail;
use App\Models\User;
use App\Models\UserInvite;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    use AuthorizesRequests;

    /**
     * Criar convite
     */
    public function store(Request $request)
    {
        // ✅ Policy: UserInvitePolicy@create
        $this->authorize('create', UserInvite::class);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role'  => ['required', 'in:user,admin'],
        ]);

        $tenant = app('tenant');

        // 🚫 Já existe user no tenant
        if (
            User::where('tenant_id', $tenant->id)
                ->where('email', $validated['email'])
                ->exists()
        ) {
            return back()->withErrors([
                'email' => 'This user already belongs to this workspace.',
            ]);
        }

        /** @var UserInvite $invite */
        $invite = DB::transaction(function () use ($validated, $tenant) {
            return UserInvite::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'email' => $validated['email'],
                ],
                [
                    'role' => $validated['role'],
                    'token' => (string) Str::uuid(),
                    'expires_at' => now()->addDays(7),
                    'accepted_at' => null,
                ]
            );
        });

        Mail::to($invite->email)->send(new UserInviteMail($invite));

        return redirect()
            ->route('users.index')
            ->with('success', 'Invitation sent successfully.');
    }

    public function resend(UserInvite $invite)
    {
        // ✅ Policy: UserInvitePolicy@resend
        $this->authorize('resend', $invite);

        $invite->update([
            'token' => (string) Str::uuid(),
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
        ]);

        Mail::to($invite->email)->send(new UserInviteMail($invite));

        return back()->with('success', 'Invitation resent successfully.');
    }

    public function destroy(UserInvite $invite)
    {
        // ✅ Policy: UserInvitePolicy@delete
        $this->authorize('delete', $invite);

        $invite->delete();

        return back()->with('success', 'Invitation cancelled successfully.');
    }
}
