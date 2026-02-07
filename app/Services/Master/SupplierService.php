<?php

namespace App\Services\Master;

use App\Models\Master\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function paginate(array $params)
    {
        $query = Supplier::with('defaultStore');

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

            return Supplier::create($data);
        });
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

    /**
     * Soft delete + set inactive
     */
    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $supplier = Supplier::findOrFail($id);

            $supplier->update([
                'is_active' => false,
            ]);

            $supplier->delete();

            return $supplier;
        });
    }

    /**
     * Restore + set active
     */
    public function restore(int $id)
    {
        return DB::transaction(function () use ($id) {
            $supplier = Supplier::withTrashed()
                ->with('defaultStore')
                ->findOrFail($id);

            $supplier->restore();

            $supplier->update([
                'is_active' => true,
            ]);

            return $supplier;
        });
    }

    /**
     * Generate auto code: SUP-0001
     */
    protected function generateCode(): string
    {
        $prefix = 'SUP-';

        $lastCode = Supplier::withTrashed()
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
