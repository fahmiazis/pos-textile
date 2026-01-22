<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'code',
        'name',
        'phone',
        'address',
        'customer_type',
        'default_store_id',
        'is_active',
    ];

    public function defaultStore()
    {
        return $this->belongsTo(Store::class, 'default_store_id');
    }
}
