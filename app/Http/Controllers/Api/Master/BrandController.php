<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    // GET /api/master/brands (pagination 10)
    public function index()
    {
        $brands = Brand::orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'List brands',
            'data' => $brands,
        ]);
    }

    // POST /api/master/brands
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:brands,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:150',
            'is_activated' => 'boolean',
        ]);

        $brand = Brand::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            'data' => $brand,
        ], 201);
    }

    // GET /api/master/brands/{id} (preview)
    public function show($id)
    {
        $brand = Brand::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Brand detail',
            'data' => $brand,
        ]);
    }

    // PUT /api/master/brands/{id}
    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:brands,code,' . $brand->id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:150',
            'is_activated' => 'boolean',
        ]);

        $brand->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully',
            'data' => $brand,
        ]);
    }

    // DELETE /api/master/brands/{id}
    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully',
        ]);
    }
}
