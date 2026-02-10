<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\Refund;
use App\Services\Sales\RefundService;
use Illuminate\Http\Request;
use Exception;

class RefundController extends Controller
{
    public function full(
        Request $request,
        SalesOrder $salesOrder,
        RefundService $refundService
    ) {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:255',
            ]);

            $refund = $refundService->fullRefund(
                $salesOrder,
                $request->reason
            );

            $message = 'Full refund berhasil diproses';
            if ($refund->reason) {
                $message .= '. Alasan: ' . $refund->reason;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $refund,
            ]);
        } catch (Exception $e) {
            $refund = Refund::where('sales_order_id', $salesOrder->id)
                ->latest()
                ->first();

            $message = $e->getMessage();
            if ($refund && $refund->reason) {
                $message .= '. Alasan: ' . $refund->reason;
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => $refund ? [
                    'refund_id' => $refund->id,
                    'refund_number' => $refund->refund_number,
                    'reason' => $refund->reason,
                ] : null,
            ], 400);
        }
    }
}
