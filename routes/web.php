<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

/* Controllers */
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\InviteAcceptController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\TenantInsightsController;
use App\Http\Controllers\ProductStatsController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\DealProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\DealFollowUpController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/invites/accept/{token}', [InviteAcceptController::class, 'show'])
    ->name('invites.accept');

Route::post('/invites/accept', [InviteAcceptController::class, 'store'])
    ->name('invites.accept.store');

/*
|--------------------------------------------------------------------------
| Authenticated + Tenant routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'tenant'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard (role-aware)
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Core
    |--------------------------------------------------------------------------
    */
    Route::get('/timeline', [TimelineController::class, 'index'])
        ->name('timeline.index');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->name('activity-logs.index');

    Route::get('/calendar', [ActivityController::class, 'calendar'])
        ->name('calendar.index');

    /*
    |--------------------------------------------------------------------------
    | AI
    |--------------------------------------------------------------------------
    */
    Route::post('/ai/chat', [AIChatController::class, 'chat'])
        ->name('ai.chat');

    Route::post('/ai/suggest/invite-follow-up', [AIController::class, 'suggestInviteFollowUp'])
        ->name('ai.suggest.invite');

    Route::post('/ai/summarize/timeline', [AIController::class, 'summarizeTimeline'])
        ->name('ai.timeline.summary');

    Route::post('/ai/detect/risks', [AIController::class, 'detectRisks'])
        ->name('ai.detect.risks');

    Route::post('/ai/draft/follow-up', [AIController::class, 'draftInviteFollowUp'])
        ->name('ai.draft.followup');

    /*
    |--------------------------------------------------------------------------
    | CRM
    |--------------------------------------------------------------------------
    */
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

    Route::get('/deals/{deal}/timeline', [DealController::class, 'timeline'])
        ->name('deals.timeline');

    Route::post('/deals/{deal}/products', [DealProductController::class, 'store']);
    Route::delete('/deal-products/{dealProduct}', [DealProductController::class, 'destroy']);

    Route::delete('/deals/{deal}/products/{product}', [DealController::class, 'removeProduct'])
        ->name('deals.products.remove');

    Route::post('/deals/{deal}/proposals', [ProposalController::class, 'store'])
        ->name('proposals.store');

    Route::post('/proposals/{proposal}/send', [ProposalController::class, 'send'])
        ->name('proposals.send');

    Route::get('/proposals/{proposal}/download', [ProposalController::class, 'download'])
        ->name('proposals.download');

    Route::post('/deals/{deal}/follow-ups/cancel', [DealFollowUpController::class, 'cancel']);

    Route::get('/products/{product}', [ProductController::class, 'show'])
        ->name('products.show');

    Route::resource('entities', EntityController::class);

    /*
    |--------------------------------------------------------------------------
    | Activities
    |--------------------------------------------------------------------------
    */
    Route::get('/activities', [ActivityController::class, 'index'])
        ->name('activities.index');

    Route::post('/activities', [ActivityController::class, 'store'])
        ->name('activities.store');

    Route::get('/activities/{activity}', [ActivityController::class, 'show'])
        ->name('activities.show');

    Route::post('/activities/{activity}/complete', [ActivityController::class, 'complete']);

    Route::post('/ai/activities/follow-ups', [AIController::class, 'detectActivityFollowUps']);
    Route::post('/ai/send-follow-up/{activity}', [AIController::class, 'sendFollowUp']);

    /*
    |--------------------------------------------------------------------------
    | INSIGHTS (ADMIN + OWNER ONLY)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,owner')->group(function () {

        Route::get('/insights', [TenantInsightsController::class, 'index'])
            ->name('insights.index');

        Route::get('/insights/products', [ProductStatsController::class, 'index'])
            ->name('insights.products');

        Route::get('/insights/deals', function () {
            return Inertia::render('insights/Deals');
        })->name('insights.deals');

        // ✅ Revenue só Owner
        Route::middleware('role:owner')->group(function () {
            Route::get('/insights/revenue', function () {
                return Inertia::render('insights/Revenue');
            })->name('insights.revenue');

            Route::post('/ai/tenant/insight', [AIController::class, 'generateTenantInsight'])
                ->name('ai.tenant.insight');

            Route::post('/ai/tenant/next-action', [AIController::class, 'recommendNextAction'])
                ->name('ai.tenant.next-action');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,owner')->group(function () {

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

    /*
    |--------------------------------------------------------------------------
    | Owner only
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:owner')->group(function () {

        Route::get('/tenant', [TenantController::class, 'index'])
            ->name('tenant.index');

        Route::get('/billing', [BillingController::class, 'index'])
            ->name('billing.index');
    });
});

require __DIR__ . '/settings.php';
