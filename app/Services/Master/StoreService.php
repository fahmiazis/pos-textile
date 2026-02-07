<?php

namespace App\Services\Master;

use App\Models\Master\Store;
use Illuminate\Support\Facades\DB;

class StoreService
{
    public function paginate(array $params)
    {
        $query = Store::query();

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (isset($params['is_active'])) {
            $query->where('is_active', $params['is_active']);
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

            return Store::create($data);
        });
    }

    public function find(int $id)
    {
        return Store::findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $store = Store::findOrFail($id);
        $store->update($data);

        return $store;
    }

    /**
     * Soft delete + set inactive
     */
    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $store = Store::findOrFail($id);

            $store->update([
                'is_active' => false,
            ]);

            $store->delete();

            return $store;
        });
    }

    /**
     * Restore + set active
     */
    public function restore(int $id)
    {
        return DB::transaction(function () use ($id) {
            $store = Store::withTrashed()->findOrFail($id);

            $store->restore();

            $store->update([
                'is_active' => true,
            ]);

            return $store;
        });
    }

    /**
     * Generate auto code: STR-0001
     */
    protected function generateCode(): string
    {
        $prefix = 'STR-';

        $lastCode = Store::withTrashed()
            ->where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->value('code');

        if (!$lastCode) {
            return $prefix . '0001';
        }

        $number = (int) str_replace($prefix, '', $lastCode);

        return $prefix . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
    }
}
