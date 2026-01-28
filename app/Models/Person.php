<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $fillable = [
        'tenant_id',
        'company_id',
        'first_name',
        'last_name',
        'email',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
