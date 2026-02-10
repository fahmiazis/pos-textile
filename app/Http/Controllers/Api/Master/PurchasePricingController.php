<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\PurchasePricingService;
use Illuminate\Http\Request;
use App\Exceptions\PurchasePricingException;

class PurchasePricingController extends Controller
{
    protected PurchasePricingService $service;

    public function __construct(PurchasePricingService $service)
    {
        $this->service = $service;
    }

    

    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->paginate($request->all()),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(int $id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->find($id),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'price_per_meter' => 'required|numeric|min:0',
            'min_qty' => 'nullable|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);
        

        return response()->json([
            'success' => true,
            'data' => $this->service->create($data),
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'price_per_meter' => 'required|numeric|min:0',
            'min_qty' => 'nullable|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->service->update($id, $data),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(int $id)
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Purchase pricing deleted',
        ]);
    }
}
