<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];
}
