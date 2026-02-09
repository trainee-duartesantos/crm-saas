<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | INSIGHTS – permissões por ação (SaaS real)
        |--------------------------------------------------------------------------
        */

        // Ver Insights (Overview)
        Gate::define('insights.view', fn ($user) =>
            $user->isAdmin() || $user->isOwner()
        );

        // Insights > Products
        Gate::define('insights.products', fn ($user) =>
            $user->isAdmin() || $user->isOwner()
        );

        // Insights > Deals
        Gate::define('insights.deals', fn ($user) =>
            $user->isAdmin() || $user->isOwner()
        );

        // Insights > Revenue (OWNER only)
        Gate::define('insights.revenue', fn ($user) =>
            $user->isOwner()
        );

        // AI actions (custos → Owner only)
        Gate::define('insights.ai.generate', fn ($user) =>
            $user->isOwner()
        );

        Gate::define('insights.ai.next-action', fn ($user) =>
            $user->isOwner()
        );
    }
}
