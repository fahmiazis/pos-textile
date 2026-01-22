<?php

namespace App\Services\Master;

use App\Models\Master\Product;

class ProductService
{
    public function paginate(array $params)
    {
        $query = Product::with(['brand', 'category', 'baseUnit']);

        // search (sku, name)
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // filter active
        if (isset($params['is_active'])) {
            $query->where('is_active', $params['is_active']);
        }

        // filter brand
        if (!empty($params['brand_id'])) {
            $query->where('brand_id', $params['brand_id']);
        }

        // filter category
        if (!empty($params['category_id'])) {
            $query->where('category_id', $params['category_id']);
        }

        // filter base unit
        if (!empty($params['base_uom_id'])) {
            $query->where('base_uom_id', $params['base_uom_id']);
        }

        return $query
            ->orderBy('id', 'desc')
            ->paginate($params['per_page'] ?? 10);
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function find(int $id)
    {
        return Product::with(['brand', 'category', 'baseUnit'])->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        return $product;
    }

    /** soft delete */
    public function delete(int $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
    }

    /** restore */
    public function restore(int $id)
    {
        $product = Product::withTrashed()
            ->with(['brand', 'category', 'baseUnit'])
            ->findOrFail($id);

        $product->restore();
        return $product;
    }
}
