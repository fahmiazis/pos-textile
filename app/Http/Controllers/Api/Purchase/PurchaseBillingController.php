<?php

namespace App\Http\Controllers\Api\Purchase;

use App\Http\Controllers\Controller;
use App\Services\Purchase\PurchaseBillingService;
use App\Models\Purchase\PurchaseBilling;
use Illuminate\Http\Request;
use Exception;

class PurchaseBillingController extends Controller
{
    protected PurchaseBillingService $service;

    public function __construct(PurchaseBillingService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /purchase/billings
     */
    public function index(Request $request)
    {
        $query = PurchaseBilling::with(['supplier', 'store', 'purchaseOrder']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->get()
        ]);
    }

    /**
     * GET /purchase/billings/{id}
     */
    public function show(int $id)
    {
        $billing = PurchaseBilling::with(['supplier', 'store', 'purchaseOrder'])
            ->find($id);

        if (! $billing) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $billing
        ]);
    }


    /**
     * POST /purchase/billings/from-po/{id}
     */
    public function createFromPo(int $id)
    {
        try {
            $billing = $this->service->createFromPurchaseOrder($id);

            return response()->json([
                'success' => true,
                'message' => 'AP Billing created',
                'data' => $billing->load(['supplier', 'store', 'purchaseOrder'])
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
