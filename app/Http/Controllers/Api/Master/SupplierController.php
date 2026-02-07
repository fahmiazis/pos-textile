<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(
        protected SupplierService $supplierService
    ) {}

    // GET /api/master/suppliers
    public function index(Request $request)
    {
        $suppliers = $this->supplierService->paginate($request->all());

        return response()->json([
            'success' => true,
            'message' => 'List suppliers',
            'data'    => $suppliers,
        ]);
    }

    // POST /api/master/suppliers
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:150',
            'phone'             => 'nullable|string|max:20',
            'address'           => 'nullable|string|max:255',
            'payment_term_days' => 'nullable|integer|min:0',
            'default_store_id'  => 'nullable|exists:stores,id',
            'is_active'         => 'boolean',
        ]);

        $supplier = $this->supplierService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier created successfully',
            'data'    => $supplier,
        ], 201);
    }

    // GET /api/master/suppliers/{id}
    public function show($id)
    {
        $supplier = $this->supplierService->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Supplier detail',
            'data'    => $supplier,
        ]);
    }

    // PUT /api/master/suppliers/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:150',
            'phone'             => 'nullable|string|max:20',
            'address'           => 'nullable|string|max:255',
            'payment_term_days' => 'nullable|integer|min:0',
            'default_store_id'  => 'nullable|exists:stores,id',
            'is_active'         => 'boolean',
        ]);

        $supplier = $this->supplierService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier updated successfully',
            'data'    => $supplier,
        ]);
    }

    // DELETE /api/master/suppliers/{id}
    public function destroy($id)
    {
        $this->supplierService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully',
        ]);
    }

    // PUT /api/master/suppliers/{id}/restore
    public function restore($id)
    {
        $supplier = $this->supplierService->restore($id);

        return response()->json([
            'success' => true,
            'message' => 'Supplier restored successfully',
            'data'    => $supplier,
        ]);
    }
}
