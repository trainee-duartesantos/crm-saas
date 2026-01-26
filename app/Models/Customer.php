<?php

namespace App\Models;

use App\Models\Concerns\TenantAware;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use TenantAware;

    protected $fillable = [
        'name',
        'email',
        'tenant_id',
    ];
}
