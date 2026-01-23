<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{

    use SoftDeletes;
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];
}
