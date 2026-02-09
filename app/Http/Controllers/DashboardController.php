<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'owner' => Inertia::render('Dashboard/OwnerDashboard', [
                'plan' => $user->tenant->plan ?? 'free',
            ]),

            'admin' => Inertia::render('Dashboard/AdminDashboard'),

            default => Inertia::render('Dashboard/UserDashboard'),
        };
    }
}
