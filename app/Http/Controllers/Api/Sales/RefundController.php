<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesOrder;
use App\Services\Sales\RefundService;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function full(Request $request, $salesOrderId, RefundService $refundService)
    {
        $salesOrder = SalesOrder::with(['billing', 'items'])
            ->findOrFail($salesOrderId);

        $refund = $refundService->fullRefund(
            $salesOrder,
            $request->input('reason')
        );

        return response()->json([
            'message' => 'Refund processed successfully',
            'data'    => $refund,
        ]);
    }
}
