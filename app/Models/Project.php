<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantAware;

class Project extends Model
{
    use TenantAware;

    protected $fillable = [
        'name',
        'tenant_id',
    ];
}
