<?php

namespace App\Services\Master;

use App\Models\Master\Brand;
use Illuminate\Support\Facades\DB;

class BrandService
{
    public function paginate(array $params)
    {
        $query = Brand::query();

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

            return Brand::create($data);
        });
    }

    public function find(int $id)
    {
        return Brand::findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $brand = $this->find($id);
        $brand->update($data);

        return $brand;
    }

    /**
     * Soft delete + set inactive
     */
    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $brand = Brand::findOrFail($id);

            $brand->update([
                'is_active' => false,
            ]);

            $brand->delete();

            return $brand;
        });
    }

    /**
     * Restore + set active
     */
    public function restore(int $id)
    {
        return DB::transaction(function () use ($id) {
            $brand = Brand::withTrashed()->findOrFail($id);

            $brand->restore();

            $brand->update([
                'is_active' => true,
            ]);

            return $brand;
        });
    }

    /**
     * Generate auto code: BR-0001
     */
    protected function generateCode(): string
    {
        $prefix = 'BR-';

        $lastCode = Brand::withTrashed()
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
