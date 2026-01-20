<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'code',
        'name',
        'phone',
        'address',
        'is_active',
    ];
}
