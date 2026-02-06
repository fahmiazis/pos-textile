<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Master\Supplier;
use App\Models\Master\Store;

class PurchasePayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_billing_id',
        'supplier_id',
        'store_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'created_by',
    ];

    public function billing()
    {
        return $this->belongsTo(PurchaseBilling::class, 'purchase_billing_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}

