<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    // GET /api/master/stores (pagination 10)
    public function index()
    {
        $stores = Store::orderBy('id', 'desc')->paginate(10);

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

        $store = Store::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Store created successfully',
            'data' => $store,
        ], 201);
    }

    // GET /api/master/stores/{id} (preview)
    public function show($id)
    {
        $store = Store::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Store detail',
            'data' => $store,
        ]);
    }

    // PUT /api/master/stores/{id}
    public function update(Request $request, $id)
    {
        $store = Store::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:stores,code,' . $store->id,
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $store->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Store updated successfully',
            'data' => $store,
        ]);
    }

    // DELETE /api/master/stores/{id}
    public function destroy($id)
    {
        $store = Store::findOrFail($id);
        $store->delete();

        return response()->json([
            'success' => true,
            'message' => 'Store deleted successfully',
        ]);
    }
}
