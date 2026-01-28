<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\CollectionService;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
  protected CollectionService $service;

  public function __construct(CollectionService $service)
  {
    $this->service = $service;
  }

  public function store(Request $request, $billingId)
  {
    $data = $request->validate([
      'amount'         => 'required|numeric|min:0.01',
      'payment_method' => 'required|string|max:50',
      'notes'          => 'nullable|string|max:255',
    ]);

    $collection = $this->service->pay(
      $billingId,
      $data['amount'],
      $data['payment_method'],
      auth()->id(), // boleh null
      $data['notes'] ?? null
    );

    $billing = $collection->billing;

    return response()->json([
      'success' => true,
      'message' => $billing->status === 'paid'
        ? 'Pembayaran lunas, stok telah dikeluarkan'
        : 'Pembayaran tercatat (parsial)',
      'data' => [
        'billing_id'     => $billing->id,
        'paid_amount'    => $billing->paid_amount,
        'billing_status' => $billing->status,
        'is_stock_out'   => $billing->status === 'paid',
      ],
    ], 201);
  }
}
