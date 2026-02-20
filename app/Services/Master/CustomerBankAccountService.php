<?php

namespace App\Services\Master;

use App\Models\Master\CustomerBankAccount;
use Illuminate\Support\Facades\DB;

class CustomerBankAccountService
{
    public function paginate(array $params)
    {
        $query = CustomerBankAccount::with('customer');

        if (!empty($params['customer_id'])) {
            $query->where('customer_id', $params['customer_id']);
        }

        return $query
            ->orderBy('id', 'desc')
            ->paginate($params['per_page'] ?? 10);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            // Jika set primary, nonaktifkan primary lain
            if (!empty($data['is_primary']) && $data['is_primary']) {
                CustomerBankAccount::where('customer_id', $data['customer_id'])
                    ->update(['is_primary' => false]);
            }

            return CustomerBankAccount::create($data);
        });
    }

    public function find(int $id)
    {
        return CustomerBankAccount::with('customer')->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {

            $account = CustomerBankAccount::findOrFail($id);

            if (!empty($data['is_primary']) && $data['is_primary']) {
                CustomerBankAccount::where('customer_id', $account->customer_id)
                    ->update(['is_primary' => false]);
            }

            $account->update($data);

            return $account;
        });
    }

    public function delete(int $id)
    {
        $account = CustomerBankAccount::findOrFail($id);
        $account->delete();

        return $account;
    }

    public function restore(int $id)
    {
        return DB::transaction(function () use ($id) {

            $account = CustomerBankAccount::withTrashed()
                ->findOrFail($id);

            $account->restore();

            return $account;
        });
    }
}
