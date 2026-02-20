<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\CustomerBankAccountService;
use Illuminate\Http\Request;

class CustomerBankAccountController extends Controller
{
    public function __construct(
        protected CustomerBankAccountService $service
    ) {}

    public function index(Request $request)
    {
        $data = $this->service->paginate($request->all());

        return response()->json([
            'success' => true,
            'message' => 'List customer bank accounts',
            'data'    => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'   => 'required|exists:customers,id',
            'bank_name'     => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:150',
            'is_primary'    => 'boolean',
        ]);

        $account = $this->service->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bank account created successfully',
            'data'    => $account,
        ], 201);
    }

    public function show($id)
    {
        $account = $this->service->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Bank account detail',
            'data'    => $account,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'bank_name'      => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:150',
            'is_primary'     => 'boolean',
        ]);

        $account = $this->service->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Bank account updated successfully',
            'data'    => $account,
        ]);
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Bank account deleted successfully',
        ]);
    }


    public function restore($id)
    {
        $account = $this->service->restore($id);

        return response()->json([
            'success' => true,
            'message' => 'Bank account restored successfully',
            'data'    => $account,
        ]);
    }
}
