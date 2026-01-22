<?php

namespace App\Services\Master;

use App\Models\Master\Driver;

class DriverService
{
    public function paginate(array $params)
    {
        $query = Driver::query();

        // search (name, phone, license)
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
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
        return Driver::create($data);
    }

    public function find(int $id)
    {
        return Driver::findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $driver = Driver::findOrFail($id);
        $driver->update($data);
        return $driver;
    }

    /** soft delete */
    public function delete(int $id)
    {
        $driver = Driver::findOrFail($id);
        $driver->delete();
    }

    /** restore */
    public function restore(int $id)
    {
        $driver = Driver::withTrashed()->findOrFail($id);
        $driver->restore();
        return $driver;
    }
}
