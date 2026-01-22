<?php

namespace App\Services\Master;

use App\Models\Master\Customer;

class CustomerService
{
    public function paginate(array $params)
    {
        $query = Customer::with('defaultStore');

        // search (code, name, phone)
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // filter active
        if (isset($params['is_active'])) {
            $query->where('is_active', $params['is_active']);
        }

        // filter customer type
        if (!empty($params['customer_type'])) {
            $query->where('customer_type', $params['customer_type']);
        }

        // filter default store
        if (!empty($params['default_store_id'])) {
            $query->where('default_store_id', $params['default_store_id']);
        }

        return $query
            ->orderBy('id', 'desc')
            ->paginate($params['per_page'] ?? 10);
    }

    public function create(array $data)
    {
        return Customer::create($data);
    }

    public function find(int $id)
    {
        return Customer::with('defaultStore')->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($data);
        return $customer;
    }

    /** soft delete */
    public function delete(int $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
    }

    /** restore */
    public function restore(int $id)
    {
        $customer = Customer::withTrashed()
            ->with('defaultStore')
            ->findOrFail($id);

        $customer->restore();
        return $customer;
    }
}
