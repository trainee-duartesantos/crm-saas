<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Response;
use Laravel\Fortify\Features;

class TwoFactorAuthenticationController extends Controller
{
    public function __construct()
    {
        if (
            Features::enabled(Features::twoFactorAuthentication()) &&
            Features::optionEnabled(
                Features::twoFactorAuthentication(),
                'confirmPassword'
            )
        ) {
            $this->middleware('password.confirm')->only('show');
        }
    }

    /**
     * Show the user's two-factor authentication settings page.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();

        return inertia('settings/TwoFactor', [
            'twoFactorEnabled' => ! is_null($user->two_factor_secret),
            'requiresConfirmation' => Features::optionEnabled(
                Features::twoFactorAuthentication(),
                'confirm'
            ) && is_null($user->two_factor_confirmed_at),
        ]);
    }

}
