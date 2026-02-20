<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Master\CustomerBankAccount;

class Customer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'code',
        'name',
        'phone',
        'address',
        'customer_type',
        'default_store_id',
        'is_active',
        'is_pkp',
        'nik',
        'sppkp',
        'npwp_address',
    ];

    public function defaultStore()
    {
        return $this->belongsTo(Store::class, 'default_store_id');
    }

    public function bankAccounts()
    {
        return $this->hasMany(CustomerBankAccount::class);
    }

}

