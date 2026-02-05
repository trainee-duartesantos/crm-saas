<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Activity;
use App\Models\Person;
use App\Models\Tenant;
use App\Models\Entity;
use App\Models\DealFollowUp;

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
        'qualified',
        'proposal',
        'won',
        'lost',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function followUps()
    {
        return $this->hasMany(DealFollowUp::class);
    }
}
