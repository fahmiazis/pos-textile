<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\UnitService;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(
        protected UnitService $unitService
    ) {}

    // GET /api/master/units
    public function index(Request $request)
    {
        $units = $this->unitService->paginate($request->all());

        return response()->json([
            'success' => true,
            'message' => 'List units',
            'data'    => $units,
        ]);
    }

    // POST /api/master/units
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:30',
            'base_unit_id' => 'nullable|exists:units,id',
            'multiplier'   => 'required|numeric|min:0',
            'is_active'    => 'boolean',
        ]);

        $unit = $this->unitService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Unit created successfully',
            'data'    => $unit,
        ], 201);
    }

    // GET /api/master/units/{id}
    public function show($id)
    {
        $unit = $this->unitService->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Unit detail',
            'data'    => $unit,
        ]);
    }

    // PUT /api/master/units/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:30',
            'base_unit_id' => 'nullable|exists:units,id',
            'multiplier'   => 'required|numeric|min:0',
            'is_active'    => 'boolean',
        ]);

        $unit = $this->unitService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Unit updated successfully',
            'data'    => $unit,
        ]);
    }

    // DELETE /api/master/units/{id}
    public function destroy($id)
    {
        $this->unitService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Unit deleted successfully',
        ]);
    }

    // PUT /api/master/units/{id}/restore
    public function restore($id)
    {
        $unit = $this->unitService->restore($id);

        return response()->json([
            'success' => true,
            'message' => 'Unit restored successfully',
            'data'    => $unit,
        ]);
    }
}
