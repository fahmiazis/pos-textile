<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    // GET /api/master/vehicles (pagination 10)
    public function index()
    {
        $vehicles = Vehicle::orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'List vehicles',
            'data' => $vehicles,
        ]);
    }

    // POST /api/master/vehicles
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:15|unique:vehicles,plate_number',
            'vehicle_type' => 'required|in:TRUCK,PICKUP',
            'capacity_meter' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $vehicle = Vehicle::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle created successfully',
            'data' => $vehicle,
        ], 201);
    }

    // GET /api/master/vehicles/{id} (preview)
    public function show($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle detail',
            'data' => $vehicle,
        ]);
    }

    // PUT /api/master/vehicles/{id}
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'plate_number' => 'required|string|max:15|unique:vehicles,plate_number,' . $vehicle->id,
            'vehicle_type' => 'required|in:TRUCK,PICKUP',
            'capacity_meter' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $vehicle->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully',
            'data' => $vehicle,
        ]);
    }

    // DELETE /api/master/vehicles/{id}
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully',
        ]);
    }
}
