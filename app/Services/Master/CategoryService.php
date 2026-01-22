<?php

namespace App\Services\Master;

use App\Models\Master\Category;

class CategoryService
{
    public function paginate(array $params)
    {
        $query = Category::query();

        // search (code / name)
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
        return Category::create($data);
    }

    public function find(int $id)
    {
        return Category::findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $category = $this->find($id);
        $category->update($data);
        return $category;
    }

    /** soft delete */
    public function delete(int $id)
    {
        $category = $this->find($id);
        $category->delete();
    }

    /** restore */
    public function restore(int $id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();
        return $category;
    }
}
