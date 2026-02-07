<?php

namespace App\Services\Master;

use App\Models\Master\Category;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function paginate(array $params)
    {
        $query = Category::query();

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

            return Category::create($data);
        });
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

    /**
     * Soft delete + set inactive
     */
    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $category = Category::findOrFail($id);

            $category->update([
                'is_active' => false,
            ]);

            $category->delete();

            return $category;
        });
    }

    /**
     * Restore + set active
     */
    public function restore(int $id)
    {
        return DB::transaction(function () use ($id) {
            $category = Category::withTrashed()->findOrFail($id);

            $category->restore();

            $category->update([
                'is_active' => true,
            ]);

            return $category;
        });
    }

    /**
     * Generate auto code: CAT-0001
     */
    protected function generateCode(): string
    {
        $prefix = 'CAT-';

        $lastCode = Category::withTrashed()
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
