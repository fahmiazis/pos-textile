<?php

namespace App\Services\Master;

use App\Models\Master\Brand;

class BrandService
{
    public function paginate(array $params)
    {
        $query = Brand::query();

        // search
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
        return Brand::create($data);
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

    public function delete(int $id)
    {
        $brand = $this->find($id);
        $brand->delete();
    }

    public function restore(int $id)
    {
        return Brand::withTrashed()->findOrFail($id)->restore();
    }
}
