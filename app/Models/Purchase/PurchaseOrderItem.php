<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'uom_id',
        'qty_input',
        'qty_base',
        'price',
        'discount',
        'subtotal',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Master\Product::class);
    }

    public function uom()
    {
        return $this->belongsTo(\App\Models\Master\Unit::class);
    }
}
