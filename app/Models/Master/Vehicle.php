<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{

    use SoftDeletes;
    protected $fillable = [
        'plate_number',
        'vehicle_type',
        'capacity_meter',
        'is_active',
    ];

    protected $casts = [
        'capacity_meter' => 'float',
        'is_active' => 'boolean',
    ];
}
