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
use App\Http\Controllers\PersonController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\DealFollowUpController;
use App\Http\Controllers\ProductStatsController;
use App\Http\Controllers\DealProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InsightsController;


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

    Route::post('/ai/chat', [AIChatController::class, 'chat'])
        ->name('ai.chat');

    /* =====================
    | Insights (Overview + subpages)
    ===================== */

    Route::get('/insights', [TenantInsightsController::class, 'index'])
        ->name('insights.index');

    Route::get('/insights/products', [ProductStatsController::class, 'index'])
        ->name('insights.products');

    /* Placeholders – evitam 404 e mantêm navegação SaaS */

    Route::get('/insights/deals', function () {
        return Inertia::render('insights/Deals');
    })->name('insights.deals');

    Route::get('/insights/revenue', function () {
        return Inertia::render('insights/Revenue');
    })->name('insights.revenue');


    Route::post('/ai/tenant/insight', [AIController::class, 'generateTenantInsight'])
        ->name('ai.tenant.insight');

    Route::post('/ai/tenant/next-action', [AIController::class, 'recommendNextAction'])
        ->name('ai.tenant.next-action');

    Route::get('/people', [PersonController::class, 'index'])
        ->name('people.index');

    Route::post('/people', [PersonController::class, 'store'])
        ->name('people.store');

    Route::get('/deals', [DealController::class, 'index'])
        ->name('deals.index');

    Route::post('/deals', [DealController::class, 'store'])
        ->name('deals.store');

    Route::post('/deals/{deal}/move', [DealController::class, 'move'])
        ->name('deals.move');

    Route::get('/deals/{deal}', [DealController::class, 'show'])
        ->name('deals.show');

    Route::post('/deals/{deal}/products', [DealProductController::class, 'store']);
    Route::delete('/deal-products/{dealProduct}', [DealProductController::class, 'destroy']);

    Route::delete('/deals/{deal}/products/{product}', [DealController::class, 'removeProduct'])
        ->name('deals.products.remove');

    Route::get('/products/{product}', [ProductController::class, 'show'])
        ->name('products.show');

    Route::get('/tenant', [TenantController::class, 'index'])
        ->name('tenant.index');

    Route::get('/billing', [BillingController::class, 'index'])
        ->name('billing.index');

    Route::post('/deals/{deal}/proposals', [ProposalController::class, 'store'])
        ->name('proposals.store');

    Route::post('/proposals/{proposal}/send', [ProposalController::class, 'send'])
        ->name('proposals.send');

    Route::get('/proposals/{proposal}/download', [ProposalController::class, 'download'])
        ->name('proposals.download');

    Route::post('/deals/{deal}/follow-ups/cancel', [DealFollowUpController::class, 'cancel']);

    Route::get('/deals/{deal}/timeline', [DealController::class, 'timeline'])
        ->name('deals.timeline');

    Route::get('/ai/deals/{deal}/next-action', [AIController::class, 'dealNextAction'])
        ->name('ai.deals.next-action');

    Route::post('/ai/deals/{deal}/summary', [AIController::class, 'summarizeDeal'])
        ->name('ai.deal.summary');

    Route::get('/calendar', [ActivityController::class, 'calendar'])
        ->name('calendar.index');

    Route::get('/activities/{activity}', [ActivityController::class, 'show'])
        ->name('activities.show');


    Route::get('/activities', [ActivityController::class, 'index']);
    Route::post('/activities', [ActivityController::class, 'store']);
    Route::post('/activities/{activity}/complete', [ActivityController::class, 'complete']);

    Route::post(
        '/ai/activities/follow-ups',
        [AIController::class, 'detectActivityFollowUps']
    );

    Route::post('/ai/send-follow-up/{activity}', [AIController::class, 'sendFollowUp']);

    Route::resource('entities', EntityController::class);


});

Route::get('/invites/accept/{token}', [InviteAcceptController::class, 'show'])
    ->name('invites.accept');

Route::post('/invites/accept', [InviteAcceptController::class, 'store'])
    ->name('invites.accept.store');


require __DIR__.'/settings.php';
