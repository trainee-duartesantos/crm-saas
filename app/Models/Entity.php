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
        return $this->hasMany(Person::class, 'company_id');
    }

    public function deals()
    {
        return $this->hasManyThrough(
            Deal::class,      // Modelo final
            Person::class,    // Modelo intermédio
            'company_id',     // Chave estrangeira em Person que aponta para Entity
            'person_id',      // Chave estrangeira em Deal que aponta para Person
            'id',             // Chave primária em Entity
            'id'              // Chave primária em Person
        );
    }
}
