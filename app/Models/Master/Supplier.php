<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'code',
        'name',
        'phone',
        'address',
        'payment_term_days',
        'default_store_id',
        'is_active',
    ];

    public function defaultStore()
    {
        return $this->belongsTo(Store::class, 'default_store_id');
    }
}
