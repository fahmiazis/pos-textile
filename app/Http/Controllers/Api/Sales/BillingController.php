<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\BillingService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
  protected BillingService $service;

  public function __construct(BillingService $service)
  {
    $this->service = $service;
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'sales_order_id' => 'required|exists:sales_orders,id',
    ]);

    $billing = $this->service->createFromSalesOrder($data['sales_order_id']);

    return response()->json([
      'success' => true,
      'message' => 'Billing berhasil dibuat (total sudah termasuk PPN 11%)',
      'data' => [
        'billing_id' => $billing->id,
        'invoice_number' => $billing->invoice_number,
        'subtotal_amount' => $billing->subtotal_amount,
        'tax_rate' => $billing->tax_rate,
        'tax_amount' => $billing->tax_amount,
        'total_amount' => $billing->total_amount,
        'status' => $billing->status,
      ]
    ], 201);
  }

  public function index(Request $request)
  {
    $billings = $this->service->list([
      'status' => $request->status
    ]);

    return response()->json([
      'success' => true,
      'meta' => [
        'filters' => [
          'status' => $request->status
        ],
        'total' => $billings->count()
      ],
      'data' => $billings
    ]);
  }
}
