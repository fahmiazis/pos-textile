<?php

namespace App\Services\Master;

use App\Models\Master\Unit;
use Illuminate\Support\Facades\DB;

class UnitService
{
    public function paginate(array $params)
    {
        $query = Unit::with('baseUnit');

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

        // filter base unit
        if (array_key_exists('base_unit_id', $params)) {
            $query->where('base_unit_id', $params['base_unit_id']);
        }

        return $query
            ->orderBy('id', 'desc')
            ->paginate($params['per_page'] ?? 10);
    }

    public function create(array $data)
    {
        return Unit::create($data);
    }

    public function find(int $id)
    {
        return Unit::with('baseUnit')->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $unit = Unit::findOrFail($id);
        $unit->update($data);
        return $unit;
    }

    /**
     * Soft delete + set inactive
     */
    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $unit = Unit::findOrFail($id);

            $unit->update([
                'is_active' => false,
            ]);

            $unit->delete();

            return $unit;
        });
    }

    /**
     * Restore + set active
     */
    public function restore(int $id)
    {
        return DB::transaction(function () use ($id) {
            $unit = Unit::withTrashed()
                ->with('baseUnit')
                ->findOrFail($id);

            $unit->restore();

            $unit->update([
                'is_active' => true,
            ]);

            return $unit;
        });
    }
}
