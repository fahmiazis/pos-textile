<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class SalesPricing extends Model
{
    protected $fillable = [
        'product_id',
        'store_id',
        'customer_type',
        'price_per_meter',
        'min_qty',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected $casts = [
        'price_per_meter' => 'float',
        'min_qty' => 'float',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
