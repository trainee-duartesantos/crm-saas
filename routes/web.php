<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\UserController;

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
    Route::get('/users', [UserController::class, 'index'])
        ->name('users.index');
});

require __DIR__.'/settings.php';
