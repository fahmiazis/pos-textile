<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'license_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
