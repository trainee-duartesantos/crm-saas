<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Deal;
use App\Policies\DealPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Deal::class => DealPolicy::class,
    ];
}
