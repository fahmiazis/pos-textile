<?php

namespace App\Services\Master;

use App\Models\Master\StoreBankAccount;
use Illuminate\Support\Facades\DB;

class StoreBankAccountService
{
    public function paginate(array $params)
    {
        $query = StoreBankAccount::with('store');

        if (!empty($params['store_id'])) {
            $query->where('store_id', $params['store_id']);
        }

        return $query
            ->orderBy('id', 'desc')
            ->paginate($params['per_page'] ?? 10);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            if (!empty($data['is_primary']) && $data['is_primary']) {
                StoreBankAccount::where('store_id', $data['store_id'])
                    ->update(['is_primary' => false]);
            }

            return StoreBankAccount::create($data);
        });
    }

    public function find(int $id)
    {
        return StoreBankAccount::with('store')->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {

            $account = StoreBankAccount::findOrFail($id);

            if (!empty($data['is_primary']) && $data['is_primary']) {
                StoreBankAccount::where('store_id', $account->store_id)
                    ->update(['is_primary' => false]);
            }

            $account->update($data);

            return $account;
        });
    }

    public function delete(int $id)
    {
        $account = StoreBankAccount::findOrFail($id);
        $account->delete();

        return $account;
    }

    public function restore(int $id)
    {
        return DB::transaction(function () use ($id) {

            $account = StoreBankAccount::withTrashed()
                ->findOrFail($id);

            $account->restore();

            return $account;
        });
    }
}