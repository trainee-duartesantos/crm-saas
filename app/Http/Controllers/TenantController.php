<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class TenantController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');

        return Inertia::render('tenant/Index', [
            'tenant' => $tenant,
        ]);
    }
}
