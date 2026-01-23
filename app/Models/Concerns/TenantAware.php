<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;

trait TenantAware
{
    protected static function bootTenantAware(): void
    {
        // 🔒 Aplica o isolamento automaticamente
        static::addGlobalScope(new TenantScope);

        // 🧠 Injeta tenant_id automaticamente no create()
        static::creating(function ($model) {
            if (app()->bound('tenant') && empty($model->tenant_id)) {
                $model->tenant_id = app('tenant')->id;
            }
        });
    }
}
