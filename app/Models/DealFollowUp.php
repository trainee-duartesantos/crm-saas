<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealFollowUp extends Model
{
    protected $fillable = [
        'tenant_id',
        'deal_id',
        'active',
        'step',
        'next_run_at',
        'stopped_at',
        'stop_reason',
    ];

    protected $casts = [
        'active' => 'boolean',
        'next_run_at' => 'datetime',
        'stopped_at' => 'datetime',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
}
