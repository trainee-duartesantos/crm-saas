<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Support\TenantManager;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/tenant-test', function () {
    $user = auth()->user();

    return [
        'user_id' => $user->id,
        'tenant_id' => $user->tenant_id,
        'tenant' => $user->tenant,
    ];
})->middleware('auth');


require __DIR__.'/settings.php';
