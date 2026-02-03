<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\CollectionService;
use Illuminate\Http\Request;
use App\Models\Sales\SalesOrder;

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
      auth()->id(),
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

  public function collected()
  {
    $orders = SalesOrder::with([
      'customer',
      'billings.collections'
    ])
      ->whereHas('billings.collections', function ($q) {
        $q->where('status', '!=', 'cancelled');
      })
      ->latest()
      ->get();

    return response()->json([
      'success' => true,
      'meta' => [
        'description' => 'Sales order yang sudah memiliki collection',
        'total' => $orders->count(),
      ],
      'data' => $orders->map(function ($order) {

        $billing = $order->billings->first();
        $collection = $billing?->collections->first();

        return [
          'id'            => $order->id,
          'so_number'     => $order->so_number,
          'status'        => $order->status,
          'order_date'    => $order->order_date,
          'total_amount'  => (float) $order->total_amount,

          'customer' => [
            'id'   => $order->customer->id,
            'name' => $order->customer->name,
          ],

          'invoice' => $billing ? [
            'id'             => $billing->id,
            'invoice_number' => $billing->invoice_number,
            'status'         => $billing->status,
          ] : null,

          'collection' => $collection ? [
            'id'                => $collection->id,
            'collection_number' => $collection->collection_number,
            'amount'            => (float) $collection->amount,
            'payment_method'    => $collection->payment_method,
            'created_at'        => $collection->created_at,
          ] : null,
        ];
      }),
    ]);
  }
}
