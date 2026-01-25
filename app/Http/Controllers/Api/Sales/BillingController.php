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

  /**
   * Create Billing from Sales Order
   */
  public function store(Request $request)
  {
    $data = $request->validate([
      'sales_order_id' => 'required|exists:sales_orders,id',
    ]);

    $billing = $this->service->createFromSalesOrder($data['sales_order_id']);

    return response()->json([
      'message' => 'Billing created',
      'data' => $billing
    ], 201);
  }
}