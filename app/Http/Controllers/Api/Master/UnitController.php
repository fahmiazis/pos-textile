<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    // GET /api/units (pagination 10)
    public function index()
    {
        $units = Unit::orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'List units',
            'data' => $units,
        ]);
    }

    // POST /api/units
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:units,code',
            'name' => 'required|string|max:30',
            'base_unit_id' => 'nullable|exists:units,id',
            'multiplier' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $unit = Unit::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Unit created successfully',
            'data' => $unit,
        ], 201);
    }

    // GET /api/units/{id} (preview / detail)
    public function show($id)
    {
        $unit = Unit::with('baseUnit')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Unit detail',
            'data' => $unit,
        ]);
    }

    // PUT /api/units/{id}
    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:units,code,' . $unit->id,
            'name' => 'required|string|max:30',
            'base_unit_id' => 'nullable|exists:units,id',
            'multiplier' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $unit->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Unit updated successfully',
            'data' => $unit,
        ]);
    }

    // DELETE /api/units/{id}
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unit deleted successfully',
        ]);
    }
}
