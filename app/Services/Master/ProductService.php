<?php

namespace App\Services\Master;

use App\Models\Master\Product;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function paginate(array $params)
    {
        $query = Product::with(['brand', 'category', 'baseUnit']);

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (isset($params['is_active'])) {
            $query->where('is_active', $params['is_active']);
        }

        if (!empty($params['brand_id'])) {
            $query->where('brand_id', $params['brand_id']);
        }

        if (!empty($params['category_id'])) {
            $query->where('category_id', $params['category_id']);
        }

        if (!empty($params['base_uom_id'])) {
            $query->where('base_uom_id', $params['base_uom_id']);
        }

        return $query
            ->orderBy('id', 'desc')
            ->paginate($params['per_page'] ?? 10);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['sku']       = $this->generateSku();
            $data['is_active'] = $data['is_active'] ?? true;

            return Product::create($data);
        });
    }

    public function find(int $id)
    {
        return Product::with(['brand', 'category', 'baseUnit'])
            ->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);

        return $product;
    }

    /**
     * Soft delete + set inactive
     */
    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $product = Product::findOrFail($id);

            $product->update([
                'is_active' => false,
            ]);

            $product->delete();

            return $product;
        });
    }

    /**
     * Restore + set active
     */
    public function restore(int $id)
    {
        return DB::transaction(function () use ($id) {
            $product = Product::withTrashed()
                ->with(['brand', 'category', 'baseUnit'])
                ->findOrFail($id);

            $product->restore();

            $product->update([
                'is_active' => true,
            ]);

            return $product;
        });
    }

    /**
     * Generate auto SKU: PRD-000001
     */
    protected function generateSku(): string
    {
        $prefix = 'PRD-';

        $lastSku = Product::withTrashed()
            ->where('sku', 'like', $prefix . '%')
            ->orderBy('sku', 'desc')
            ->value('sku');

        if (!$lastSku) {
            return $prefix . '000001';
        }

        $number = (int) str_replace($prefix, '', $lastSku);

        return $prefix . str_pad($number + 1, 6, '0', STR_PAD_LEFT);
    }
}
