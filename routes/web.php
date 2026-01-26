<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\InviteAcceptController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('/users', [UserController::class, 'index'])
        ->name('users.index');

    Route::get('/users/invite', [InviteController::class, 'create'])
        ->name('users.invite');

    Route::post('/users/invite', [InviteController::class, 'store'])
        ->name('users.invite.store');

    Route::post('/invites/{invite}/resend', [InviteController::class, 'resend'])
        ->name('invites.resend');

    Route::delete('/invites/{invite}', [InviteController::class, 'destroy'])
        ->name('invites.destroy');
});

Route::get('/invites/accept/{token}', [InviteAcceptController::class, 'show'])
    ->name('invites.accept');

Route::post('/invites/accept', [InviteAcceptController::class, 'store'])
    ->name('invites.accept.store');

require __DIR__.'/settings.php';
