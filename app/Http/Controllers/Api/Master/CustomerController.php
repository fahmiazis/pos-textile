<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // GET /api/master/customers (pagination 10)
    public function index()
    {
        $customers = Customer::with('defaultStore')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'List customers',
            'data' => $customers,
        ]);
    }

    // POST /api/master/customers
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:customers,code',
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'customer_type' => 'required|in:RETAIL,GROSIR,PROJECT',
            'default_store_id' => 'nullable|exists:stores,id',
            'is_active' => 'boolean',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $customer,
        ], 201);
    }

    // GET /api/master/customers/{id} (preview)
    public function show($id)
    {
        $customer = Customer::with('defaultStore')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Customer detail',
            'data' => $customer,
        ]);
    }

    // PUT /api/master/customers/{id}
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:customers,code,' . $customer->id,
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'customer_type' => 'required|in:RETAIL,GROSIR,PROJECT',
            'default_store_id' => 'nullable|exists:stores,id',
            'is_active' => 'boolean',
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'data' => $customer,
        ]);
    }

    // DELETE /api/master/customers/{id}
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully',
        ]);
    }
}
