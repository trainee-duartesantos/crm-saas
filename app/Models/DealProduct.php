<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealProduct extends Model
{
    protected $fillable = [
        'tenant_id',
        'deal_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

