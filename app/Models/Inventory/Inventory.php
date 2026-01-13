<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventories';

    protected $fillable = [
        'store_id',
        'product_id',
        'uom_id',
        'qty_on_hand',
    ];

    /* ================= RELATIONS ================= */

    public function store()
    {
        return $this->belongsTo(\App\Models\Master\Store::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Master\Product::class);
    }

    public function uom()
    {
        return $this->belongsTo(\App\Models\Master\Unit::class, 'uom_id');
    }
}
