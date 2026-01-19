<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // GET /api/master/suppliers (pagination 10)
    public function index()
    {
        $suppliers = Supplier::with('defaultStore')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'List suppliers',
            'data' => $suppliers,
        ]);
    }

    // POST /api/master/suppliers
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:suppliers,code',
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'payment_term_days' => 'nullable|integer|min:0',
            'default_store_id' => 'nullable|exists:stores,id',
            'is_active' => 'boolean',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier created successfully',
            'data' => $supplier,
        ], 201);
    }

    // GET /api/master/suppliers/{id} (preview)
    public function show($id)
    {
        $supplier = Supplier::with('defaultStore')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Supplier detail',
            'data' => $supplier,
        ]);
    }

    // PUT /api/master/suppliers/{id}
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:suppliers,code,' . $supplier->id,
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'payment_term_days' => 'nullable|integer|min:0',
            'default_store_id' => 'nullable|exists:stores,id',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier updated successfully',
            'data' => $supplier,
        ]);
    }

    // DELETE /api/master/suppliers/{id}
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully',
        ]);
    }
}
