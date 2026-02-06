<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'supplier_id',
        'store_id',
        'order_date',
        'status',
        'created_by',
        'notes',
        'total_qty',
        'total_amount'
    ];


    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
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
