<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseBilling extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'supplier_id',
        'store_id',
        'billing_number',
        'billing_date',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Models\Master\Supplier::class);
    }

    public function store()
    {
        return $this->belongsTo(\App\Models\Master\Store::class);
    }
}
