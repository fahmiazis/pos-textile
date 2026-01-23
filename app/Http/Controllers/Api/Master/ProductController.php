<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    // GET /api/master/products
    public function index(Request $request)
    {
        $products = $this->productService->paginate($request->all());

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

        $product = $this->productService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    // GET /api/master/products/{id}
    public function show($id)
    {
        $product = $this->productService->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Product detail',
            'data' => $product,
        ]);
    }

    // PUT /api/master/products/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:30|unique:products,sku,' . $id,
            'name' => 'required|string|max:150',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'base_uom_id' => 'required|exists:units,id',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $product = $this->productService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    // DELETE /api/master/products/{id} (soft delete)
    public function destroy($id)
    {
        $this->productService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    // PUT /api/master/products/{id}/restore
    public function restore($id)
    {
        $product = $this->productService->restore($id);

        return response()->json([
            'success' => true,
            'message' => 'Product restored successfully',
            'data' => $product,
        ]);
    }
}
