<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'tenant_id',
        'created_by',
        'person_id',
        'deal_id',
        'type',
        'title',
        'notes',
        'due_at',
        'completed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }
}
