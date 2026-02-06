<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\SalesPricingService;
use Illuminate\Http\Request;

class SalesPricingController extends Controller
{
    protected SalesPricingService $service;

    public function __construct(SalesPricingService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->paginate($request->all()),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'store_id' => 'nullable|exists:stores,id',
            'customer_type' => 'required|in:RETAIL,GROSIR,PROJECT',
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
}
