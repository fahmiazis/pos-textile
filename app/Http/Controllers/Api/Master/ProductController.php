<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /api/master/products (pagination 10)
    public function index()
    {
        $products = Product::with(['brand', 'category', 'baseUnit'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'List products',
            'data' => $products,
        ]);
    }

    // POST /api/master/products
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:30|unique:products,sku',
            'name' => 'required|string|max:150',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'base_uom_id' => 'required|exists:units,id',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    // GET /api/master/products/{id} (preview)
    public function show($id)
    {
        $product = Product::with(['brand', 'category', 'baseUnit'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Product detail',
            'data' => $product,
        ]);
    }

    // PUT /api/master/products/{id}
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'sku' => 'required|string|max:30|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:150',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'base_uom_id' => 'required|exists:units,id',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    // DELETE /api/master/products/{id}
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
