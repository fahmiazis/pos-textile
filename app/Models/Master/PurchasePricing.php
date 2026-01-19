<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class PurchasePricing extends Model
{
    protected $fillable = [
        'product_id',
        'supplier_id',
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
