<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(
        protected BrandService $brandService
    ) {}

    public function index(Request $request)
    {
        $brands = $this->brandService->paginate($request->all());

        return response()->json([
            'success' => true,
            'message' => 'List brands',
            'data' => $brands,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:150',
            'is_active'   => 'boolean',
        ]);

        $brand = $this->brandService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            'data'    => $brand,
        ], 201);
    }

    public function show($id)
    {
        $brand = $this->brandService->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Brand detail',
            'data'    => $brand,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:150',
            'is_active'   => 'boolean',
        ]);

        $brand = $this->brandService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully',
            'data'    => $brand,
        ]);
    }

    public function destroy($id)
    {
        $this->brandService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully',
        ]);
    }

    public function restore($id)
    {
        $this->brandService->restore($id);

        return response()->json([
            'success' => true,
            'message' => 'Brand restored successfully',
        ]);
    }
}
