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
    return [
        'tenant' => tenant(),
        'tenant_id' => tenant()?->id,
    ];
})->middleware('auth');


require __DIR__.'/settings.php';
