<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreBankAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'bank_name',
        'account_number',
        'account_holder',
        'is_primary',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}