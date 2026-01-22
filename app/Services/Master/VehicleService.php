<?php

namespace App\Services\Master;

use App\Models\Master\Vehicle;

class VehicleService
{
    public function paginate(array $params)
    {
        $query = Vehicle::query();

        // search (plate number)
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where('plate_number', 'like', "%{$search}%");
        }

        // filter vehicle type
        if (!empty($params['vehicle_type'])) {
            $query->where('vehicle_type', $params['vehicle_type']);
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
        return Vehicle::create($data);
    }

    public function find(int $id)
    {
        return Vehicle::findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update($data);
        return $vehicle;
    }

    /** soft delete */
    public function delete(int $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();
    }

    /** restore */
    public function restore(int $id)
    {
        $vehicle = Vehicle::withTrashed()->findOrFail($id);
        $vehicle->restore();
        return $vehicle;
    }
}
