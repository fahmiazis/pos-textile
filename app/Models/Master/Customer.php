<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
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
