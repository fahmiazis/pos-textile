<?php

namespace App\Services\Master;

use App\Models\Master\Customer;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function paginate(array $params)
    {
        $query = Customer::with('defaultStore');

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (isset($params['is_active'])) {
            $query->where('is_active', $params['is_active']);
        }

        if (!empty($params['customer_type'])) {
            $query->where('customer_type', $params['customer_type']);
        }

        if (!empty($params['default_store_id'])) {
            $query->where('default_store_id', $params['default_store_id']);
        }

        return $query
            ->orderBy('id', 'desc')
            ->paginate($params['per_page'] ?? 10);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $data['code']      = $this->generateCode();
            $data['is_active'] = $data['is_active'] ?? true;
            $data['is_pkp']    = $data['is_pkp'] ?? false;

            if (!$data['is_pkp']) {
                $data['nik']          = null;
                $data['sppkp']        = null;
                $data['npwp_address'] = null;
            }

            return Customer::create($data);
        });
    }
    public function find(int $id)
    {
        return Customer::with('defaultStore')->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $customer = Customer::findOrFail($id);

        $data['is_pkp'] = $data['is_pkp'] ?? false;

        if (!$data['is_pkp']) {
            $data['nik']          = null;
            $data['sppkp']        = null;
            $data['npwp_address'] = null;
        }

        $customer->update($data);

        return $customer;
    }

    /**
     * Soft delete + set inactive
     */
    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $customer = Customer::findOrFail($id);

            $customer->update([
                'is_active' => false,
            ]);

            $customer->delete();

            return $customer;
        });
    }

    /**
     * Restore + set active
     */
    public function restore(int $id)
    {
        return DB::transaction(function () use ($id) {
            $customer = Customer::withTrashed()
                ->with('defaultStore')
                ->findOrFail($id);

            $customer->restore();

            $customer->update([
                'is_active' => true,
            ]);

            return $customer;
        });
    }

    /**
     * Generate auto code: CUST-0001
     */
    protected function generateCode(): string
    {
        $prefix = 'CUST-';

        $lastId = Customer::withTrashed()->max('id');

        $nextNumber = $lastId ? $lastId + 1 : 1;

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
