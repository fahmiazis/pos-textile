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

  /**
   * Create Collection (Payment)
   */
  public function store(Request $request)
  {
    $data = $request->validate([
      'billing_id' => 'required|exists:billings,id',
      'amount' => 'required|numeric|min:0.01',
      'payment_method' => 'required|string',
      'notes' => 'nullable|string',
    ]);

    $collection = $this->service->pay(
      $data['billing_id'],
      $data['amount'],
      $data['payment_method'],
      auth()->id(),
      $data['notes'] ?? null
    );

    return response()->json([
      'message' => 'Payment processed',
      'data' => $collection
    ], 201);
  }
}