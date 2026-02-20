<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    // GET /api/master/customers
    public function index(Request $request)
    {
        $customers = $this->customerService->paginate($request->all());

        return response()->json([
            'success' => true,
            'message' => 'List customers',
            'data'    => $customers,
        ]);
    }

    // POST /api/master/customers
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:150',
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:255',
            'customer_type'    => 'required|in:RETAIL,GROSIR,PROJECT',
            'default_store_id' => 'nullable|exists:stores,id',
            'is_active'        => 'boolean',

            // NEW TAX FIELDS
            'is_pkp'       => 'boolean',
            'nik'          => 'required_if:is_pkp,1|nullable|string|max:30',
            'sppkp'        => 'required_if:is_pkp,1|nullable|string|max:50',
            'npwp_address' => 'required_if:is_pkp,1|nullable|string',
        ]);

        $customer = $this->customerService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data'    => $customer,
        ], 201);
    }

    // GET /api/master/customers/{id}
    public function show($id)
    {
        $customer = $this->customerService->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Customer detail',
            'data'    => $customer,
        ]);
    }

    // PUT /api/master/customers/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:150',
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:255',
            'customer_type'    => 'required|in:RETAIL,GROSIR,PROJECT',
            'default_store_id' => 'nullable|exists:stores,id',
            'is_active'        => 'boolean',

            // NEW TAX FIELDS
            'is_pkp'       => 'boolean',
            'nik'          => 'required_if:is_pkp,1|nullable|string|max:30',
            'sppkp'        => 'required_if:is_pkp,1|nullable|string|max:50',
            'npwp_address' => 'required_if:is_pkp,1|nullable|string',
        ]);

        $customer = $this->customerService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'data'    => $customer,
        ]);
    }

    // DELETE /api/master/customers/{id}
    public function destroy($id)
    {
        $this->customerService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully',
        ]);
    }

    // PUT /api/master/customers/{id}/restore
    public function restore($id)
    {
        $customer = $this->customerService->restore($id);

        return response()->json([
            'success' => true,
            'message' => 'Customer restored successfully',
            'data'    => $customer,
        ]);
    }
}
