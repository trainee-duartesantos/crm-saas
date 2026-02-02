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
use App\Services\ActivityLogger;

class InviteController extends Controller
{
    use AuthorizesRequests;

    /**
     * Criar convite
     */
       public function store(Request $request, ActivityLogger $logger)
    {
        // ✅ Policy
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

        // ✅ AGORA SIM: Activity Log
        $logger->log('invites.sent', $invite, [
            'email' => $invite->email,
            'expires_at' => $invite->expires_at?->toISOString(),
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Invitation sent successfully.');
    }


    public function create()
    {
        $this->authorize('viewAny', \App\Models\ActivityLog::class);

        return \Inertia\Inertia::render('Users/Invite');
    }

    public function resend(UserInvite $invite, ActivityLogger $logger)
    {
        $this->authorize('resend', $invite);

        $invite->update([
            'token' => (string) Str::uuid(),
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
        ]);

        Mail::to($invite->email)->send(new UserInviteMail($invite));

        $logger->log('invites.resent', $invite, [
            'email' => $invite->email,
        ]);

        return back()->with('success', 'Invitation resent successfully.');
    }

    public function destroy(UserInvite $invite, ActivityLogger $logger)
    {
        $this->authorize('delete', $invite);

        $logger->log('invites.canceled', $invite, [
            'email' => $invite->email,
        ]);

        $invite->delete();

        return back()->with('success', 'Invitation cancelled successfully.');
    }

}
