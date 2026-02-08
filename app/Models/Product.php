<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'unit_price',
    ];

    public function dealProducts()
    {
        return $this->hasMany(DealProduct::class);
    }

    public function deals()
    {
        return $this->belongsToMany(Deal::class, 'deal_products')
            ->withPivot(['quantity', 'unit_price'])
            ->withTimestamps();
    }
}
