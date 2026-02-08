<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesOrder;
use App\Services\Sales\RefundService;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function full(
        Request $request,
        SalesOrder $salesOrder,
        RefundService $refundService
    ) {
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
    }
}
