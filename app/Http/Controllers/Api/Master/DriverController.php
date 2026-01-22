<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\DriverService;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function __construct(
        protected DriverService $driverService
    ) {}

    // GET /api/master/drivers
    public function index(Request $request)
    {
        $drivers = $this->driverService->paginate($request->all());

        return response()->json([
            'success' => true,
            'message' => 'List drivers',
            'data' => $drivers,
        ]);
    }

    // POST /api/master/drivers
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:30',
            'is_active' => 'boolean',
        ]);

        $driver = $this->driverService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Driver created successfully',
            'data' => $driver,
        ], 201);
    }

    // GET /api/master/drivers/{id}
    public function show($id)
    {
        $driver = $this->driverService->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Driver detail',
            'data' => $driver,
        ]);
    }

    // PUT /api/master/drivers/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:30',
            'is_active' => 'boolean',
        ]);

        $driver = $this->driverService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Driver updated successfully',
            'data' => $driver,
        ]);
    }

    // DELETE /api/master/drivers/{id} (soft delete)
    public function destroy($id)
    {
        $this->driverService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Driver deleted successfully',
        ]);
    }

    // PUT /api/master/drivers/{id}/restore
    public function restore($id)
    {
        $driver = $this->driverService->restore($id);

        return response()->json([
            'success' => true,
            'message' => 'Driver restored successfully',
            'data' => $driver,
        ]);
    }
}
