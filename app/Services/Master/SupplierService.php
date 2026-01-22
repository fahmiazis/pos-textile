<?php

namespace App\Services\Master;

use App\Models\Master\Supplier;

class SupplierService
{
    public function paginate(array $params)
    {
        $query = Supplier::with('defaultStore');

        // search (code, name)
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // filter active
        if (isset($params['is_active'])) {
            $query->where('is_active', $params['is_active']);
        }

        // filter store
        if (!empty($params['default_store_id'])) {
            $query->where('default_store_id', $params['default_store_id']);
        }

        return $query
            ->orderBy('id', 'desc')
            ->paginate($params['per_page'] ?? 10);
    }

    public function create(array $data)
    {
        return Supplier::create($data);
    }

    public function find(int $id)
    {
        return Supplier::with('defaultStore')->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);
        return $supplier;
    }

    /** soft delete */
    public function delete(int $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
    }

    /** restore */
    public function restore(int $id)
    {
        $supplier = Supplier::withTrashed()
            ->with('defaultStore')
            ->findOrFail($id);

        $supplier->restore();
        return $supplier;
    }
}
