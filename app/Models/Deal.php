<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $fillable = [
        'tenant_id',
        'person_id',
        'title',
        'value',
        'status',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'value' => 'decimal:2',
    ];

    public const STATUSES = [
        'lead',
        'contacted',
        'proposal',
        'won',
        'lost',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
