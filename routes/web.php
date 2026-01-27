<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\InviteAcceptController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\TenantInsightsController;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'tenant'])->group(function () {

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

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->name('activity-logs.index');

    Route::get('/timeline', [TimelineController::class, 'index'])
        ->name('timeline.index');

    Route::post('/ai/suggest/invite-follow-up', [AIController::class, 'suggestInviteFollowUp'])
        ->name('ai.suggest.invite');

    Route::post('/ai/summarize/timeline', [AIController::class, 'summarizeTimeline'])
        ->name('ai.timeline.summary');

    Route::post('/ai/detect/risks', [AIController::class, 'detectRisks'])
        ->name('ai.detect.risks');

    Route::post('/ai/draft/follow-up', [AIController::class, 'draftInviteFollowUp'])
        ->name('ai.draft.followup');

    Route::get('/insights', [TenantInsightsController::class, 'index'])
        ->name('insights.index');

    Route::post('/ai/tenant/insight', [AIController::class, 'generateTenantInsight'])
        ->name('ai.tenant.insight');

});

Route::get('/invites/accept/{token}', [InviteAcceptController::class, 'show'])
    ->name('invites.accept');

Route::post('/invites/accept', [InviteAcceptController::class, 'store'])
    ->name('invites.accept.store');


require __DIR__.'/settings.php';
