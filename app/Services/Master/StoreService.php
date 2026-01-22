<?php

namespace App\Services\Master;

use App\Models\Master\Store;

class StoreService
{
    public function paginate(array $params)
    {
        $query = Store::query();

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

        return $query
            ->orderBy('id', 'desc')
            ->paginate($params['per_page'] ?? 10);
    }

    public function create(array $data)
    {
        return Store::create($data);
    }

    public function find(int $id)
    {
        return Store::findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $store = $this->find($id);
        $store->update($data);
        return $store;
    }

    /** soft delete */
    public function delete(int $id)
    {
        $store = $this->find($id);
        $store->delete();
    }

    /** restore */
    public function restore(int $id)
    {
        $store = Store::withTrashed()->findOrFail($id);
        $store->restore();
        return $store;
    }
}
