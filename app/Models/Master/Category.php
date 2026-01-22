<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];
}
