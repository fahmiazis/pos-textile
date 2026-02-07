<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    // GET /api/master/categories
    public function index(Request $request)
    {
        $categories = $this->categoryService->paginate($request->all());

        return response()->json([
            'success' => true,
            'message' => 'List categories',
            'data'    => $categories,
        ]);
    }

    // POST /api/master/categories
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $category = $this->categoryService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data'    => $category,
        ], 201);
    }

    // GET /api/master/categories/{id}
    public function show($id)
    {
        $category = $this->categoryService->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Category detail',
            'data'    => $category,
        ]);
    }

    // PUT /api/master/categories/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $category = $this->categoryService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data'    => $category,
        ]);
    }

    // DELETE /api/master/categories/{id}
    public function destroy($id)
    {
        $this->categoryService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    // PUT /api/master/categories/{id}/restore
    public function restore($id)
    {
        $category = $this->categoryService->restore($id);

        return response()->json([
            'success' => true,
            'message' => 'Category restored successfully',
            'data'    => $category,
        ]);
    }
}
