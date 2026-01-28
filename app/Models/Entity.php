<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'vat',
        'email',
        'phone',
        'website',
        'status',
        'notes',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function people()
    {
        return $this->hasMany(Person::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}
