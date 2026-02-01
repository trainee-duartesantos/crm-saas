<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class BillingController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');

        return Inertia::render('billing/Index', [
            'tenant' => [
                'name' => $tenant->name,
                'plan' => $tenant->plan,
                'created_at' => $tenant->created_at,
            ],
        ]);
    }
}
