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

Route::get('/admin-only', function () {
    return 'Hello admin';
})->middleware(['auth', 'role:admin']);

Route::middleware(['auth'])->group(function () {

    Route::get('/users', function () {
        authorize('viewAny', \App\Models\User::class);

        return Inertia::render('Users/Index');
    });

});


require __DIR__.'/settings.php';
