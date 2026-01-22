<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\StoreService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(
        protected StoreService $storeService
    ) {}

    // GET /api/master/stores
    public function index(Request $request)
    {
        $stores = $this->storeService->paginate($request->all());

        return response()->json([
            'success' => true,
            'message' => 'List stores',
            'data' => $stores,
        ]);
    }

    // POST /api/master/stores
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:stores,code',
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $store = $this->storeService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Store created successfully',
            'data' => $store,
        ], 201);
    }

    // GET /api/master/stores/{id}
    public function show($id)
    {
        $store = $this->storeService->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Store detail',
            'data' => $store,
        ]);
    }

    // PUT /api/master/stores/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:stores,code,' . $id,
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $store = $this->storeService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Store updated successfully',
            'data' => $store,
        ]);
    }

    // DELETE /api/master/stores/{id} (soft delete)
    public function destroy($id)
    {
        $this->storeService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Store deleted successfully',
        ]);
    }

    // PUT /api/master/stores/{id}/restore
    public function restore($id)
    {
        $store = $this->storeService->restore($id);

        return response()->json([
            'success' => true,
            'message' => 'Store restored successfully',
            'data' => $store,
        ]);
    }
}
