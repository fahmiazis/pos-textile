<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    // GET /api/master/drivers (pagination 10)
    public function index()
    {
        $drivers = Driver::orderBy('id', 'desc')->paginate(10);

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

        $driver = Driver::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Driver created successfully',
            'data' => $driver,
        ], 201);
    }

    // GET /api/master/drivers/{id} (preview)
    public function show($id)
    {
        $driver = Driver::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Driver detail',
            'data' => $driver,
        ]);
    }

    // PUT /api/master/drivers/{id}
    public function update(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:30',
            'is_active' => 'boolean',
        ]);

        $driver->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Driver updated successfully',
            'data' => $driver,
        ]);
    }

    // DELETE /api/master/drivers/{id}
    public function destroy($id)
    {
        $driver = Driver::findOrFail($id);
        $driver->delete();

        return response()->json([
            'success' => true,
            'message' => 'Driver deleted successfully',
        ]);
    }
}
