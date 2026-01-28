<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['tenant_id', 'name'];

    public function people()
    {
        return $this->hasMany(Person::class);
    }
}

