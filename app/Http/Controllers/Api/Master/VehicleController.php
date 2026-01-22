<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\VehicleService;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(
        protected VehicleService $vehicleService
    ) {}

    // GET /api/master/vehicles
    public function index(Request $request)
    {
        $vehicles = $this->vehicleService->paginate($request->all());

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

        $vehicle = $this->vehicleService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle created successfully',
            'data' => $vehicle,
        ], 201);
    }

    // GET /api/master/vehicles/{id}
    public function show($id)
    {
        $vehicle = $this->vehicleService->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle detail',
            'data' => $vehicle,
        ]);
    }

    // PUT /api/master/vehicles/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:15|unique:vehicles,plate_number,' . $id,
            'vehicle_type' => 'required|in:TRUCK,PICKUP',
            'capacity_meter' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $vehicle = $this->vehicleService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully',
            'data' => $vehicle,
        ]);
    }

    // DELETE /api/master/vehicles/{id} (soft delete)
    public function destroy($id)
    {
        $this->vehicleService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully',
        ]);
    }

    // PUT /api/master/vehicles/{id}/restore
    public function restore($id)
    {
        $vehicle = $this->vehicleService->restore($id);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle restored successfully',
            'data' => $vehicle,
        ]);
    }
}
