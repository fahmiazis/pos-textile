<?php

namespace App\Http\Controllers\Api\Purchase;

use App\Http\Controllers\Controller;
use App\Services\Purchase\PurchasePaymentService;
use Illuminate\Http\Request;
use Exception;

class PurchasePaymentController extends Controller
{
    protected PurchasePaymentService $service;

    public function __construct(PurchasePaymentService $service)
    {
        $this->service = $service;
    }

    /**
     * =========================
     * LIST PAYMENTS
     * =========================
     */
    public function index(Request $request)
    {
        $query = \App\Models\Purchase\PurchasePayment::with([
            'billing',
            'supplier',
            'store'
        ]);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('purchase_billing_id')) {
            $query->where('purchase_billing_id', $request->purchase_billing_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->get()
        ]);
    }

    /**
     * =========================
     * CREATE PAYMENT
     * =========================
     */
    public function store(Request $request)
    {
        try {

            $data = $request->validate([
                'purchase_billing_id' => 'required|exists:purchase_billings,id',
                'payment_date'        => 'required|date',
                'amount'              => 'required|numeric|min:0.01',
                'payment_method'      => 'nullable|string|max:50',
                'reference_number'    => 'nullable|string|max:100',
                'notes'               => 'nullable|string',
            ]);

            $payment = $this->service->pay(
                $data,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'AP Payment berhasil dicatat',
                'data' => $payment->load([
                    'billing',
                    'supplier',
                    'store'
                ])
            ], 201);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * =========================
     * SHOW PAYMENT
     * =========================
     */
    public function show(int $id)
    {
        $payment = \App\Models\Purchase\PurchasePayment::with([
            'billing',
            'supplier',
            'store'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }
}
