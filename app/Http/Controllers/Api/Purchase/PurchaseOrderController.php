<?php

namespace App\Http\Controllers\Api\Purchase;

use App\Http\Controllers\Controller;
use App\Services\Purchase\PurchaseOrderService;
use Illuminate\Http\Request;
use App\Models\Purchase\PurchaseOrder;
use Exception;

class PurchaseOrderController extends Controller
{
    protected PurchaseOrderService $service;

    public function __construct(PurchaseOrderService $service)
    {
        $this->service = $service;
    }

    /*
    =========================
    LIST PO
    =========================
    */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'store']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->get()
        ]);
    }

    /*
    =========================
    SHOW PO
    =========================
    */
    public function show(int $id)
    {
        $po = PurchaseOrder::with([
            'store',
            'supplier',
            'items.product'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $po
        ]);
    }

    /*
    =========================
    CREATE PO
    =========================
    */
    public function store(Request $request)
    {
        try {

            $data = $request->validate([
                'store_id' => 'required|exists:stores,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'order_date' => 'required|date',
                'notes' => 'nullable|string',

                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.uom_id' => 'required|exists:units,id',
                'items.*.qty_input' => 'required|numeric|min:0.01',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
            ]);

            $po = $this->service->create($data, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order draft created',
                'data' => $po->load(['items.product', 'supplier', 'store'])
            ], 201);
        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /*
    =========================
    UPDATE DRAFT
    =========================
    */
    public function update(Request $request, int $id)
    {
        try {

            $data = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'order_date' => 'required|date',
                'notes' => 'nullable|string',

                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.uom_id' => 'required|exists:units,id',
                'items.*.qty_input' => 'required|numeric|min:0.01',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
            ]);

            $po = $this->service->updateDraft($id, $data);

            return response()->json([
                'success' => true,
                'message' => 'PO draft updated',
                'data' => $po->load(['items.product', 'supplier', 'store'])
            ]);
        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /*
    =========================
    SUBMIT PO
    =========================
    */
    public function submit(int $id)
    {
        try {

            $po = $this->service->submit($id);

            return response()->json([
                'success' => true,
                'message' => 'PO submitted',
                'data' => $po->load(['supplier', 'store', 'items.product'])
            ]);
        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /*
    =========================
    CANCEL PO
    =========================
    */
    public function cancel(int $id)
    {
        try {

            $po = $this->service->cancel($id);

            return response()->json([
                'success' => true,
                'message' => 'PO cancelled',
                'data' => $po->load(['supplier', 'store', 'items.product'])
            ]);
        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }



     /*
    =========================
    RECEIVE PO (GR)
    =========================
    */
    public function receive(int $id)
    {
        try {

            $po = $this->service->receive($id);

            return response()->json([
                'success' => true,
                'message' => 'PO received & stock updated',
                'data' => $po->load(['supplier', 'store', 'items.product'])
            ]);
        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
