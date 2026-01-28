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

  public function store(Request $request)
  {
    $data = $request->validate([
      'billing_id'     => 'required|exists:billings,id',
      'amount'         => 'required|numeric|min:0.01',
      'payment_method' => 'required|string',
      'notes'          => 'nullable|string',
    ]);

    $collection = $this->service->pay(
      $data['billing_id'],
      $data['amount'],
      $data['payment_method'],
      auth()->user()->id,
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
      ]
    ], 201);
  }
}
