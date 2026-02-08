<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\SalesOrderService;
use Illuminate\Http\Request;
use App\Models\Sales\SalesOrder;

class SalesOrderController extends Controller
{
  protected SalesOrderService $service;

  public function __construct(SalesOrderService $service)
  {
    $this->service = $service;
  }

  public function index(Request $request)
  {
    $query = SalesOrder::with('customer');

    if ($request->has('status')) {
      $statuses = is_array($request->status)
        ? $request->status
        : [$request->status];

      $query->whereIn('status', $statuses);
    }

    return response()->json([
      'success' => true,
      'meta' => [
        'filters' => [
          'status' => $request->status
        ],
        'total' => $query->count()
      ],
      'data' => $query->latest()->get()
    ]);
  }

  public function show(int $id)
  {
    $salesOrder = SalesOrder::with([
      'store',
      'customer',
      'items.product',
      'billings.collections'
    ])->findOrFail($id);

    return response()->json([
      'success' => true,
      'data' => $salesOrder,
    ]);
  }

  /**
   * CREATE SALES ORDER (DRAFT)
   */
  public function store(Request $request)
  {
    $data = $request->validate([
      'store_id' => 'required|exists:stores,id',
      'customer_id' => 'required|exists:customers,id',
      'order_date' => 'required|date',
      'notes' => 'nullable|string',

      'items' => 'required|array|min:1',
      'items.*.product_id' => 'required|exists:products,id',
      'items.*.uom_id' => 'required|exists:units,id',
      'items.*.qty_input' => 'required|numeric|min:0.001',
      'items.*.discount' => 'nullable|numeric|min:0',
    ]);

    $salesOrder = $this->service->create($data, auth()->id());

    return response()->json([
      'success' => true,
      'message' => 'Sales order draft created',
      'data' => $salesOrder->load([
        'items.product',
        'store',
        'customer'
      ])
    ], 201);
  }

  public function update(Request $request, int $id)
  {
    $data = $request->validate([
      'customer_id' => 'required|exists:customers,id',
      'order_date'  => 'required|date',

      'items' => 'required|array|min:1',
      'items.*.product_id' => 'required|exists:products,id',
      'items.*.uom_id'     => 'required|exists:units,id',
      'items.*.qty_input'  => 'required|numeric|min:0.001',
      'items.*.discount'   => 'nullable|numeric|min:0',
    ]);

    $order = $this->service->updateDraft($id, $data);

    return response()->json([
      'success' => true,
      'message' => 'Sales order draft berhasil diupdate',
      'data'    => $order->load([
        'items.product',
        'customer',
        'store'
      ]),
    ]);
  }



  /**
   * SUBMIT SALES ORDER
   */
  public function submit(int $id)
  {
    try {
      $order = $this->service->submit($id);

      return response()->json([
        'success' => true,
        'message' => 'Sales order berhasil disubmit',
        'data' => $order->load([
          'items.product',
          'customer',
          'store'
        ])
      ]);
    } catch (\Exception $e) {
      $order = SalesOrder::with(['customer', 'store'])
        ->find($id);

      if ($order && $order->status === 'cancelled') {
        return response()->json([
          'success' => false,
          'message' => 'Sales Order Cancelled tidak bisa disubmit',
          'data' => [
            'id' => $order->id,
            'so_number' => $order->so_number,
            'status' => $order->status,
            'order_date' => $order->order_date,
            'submitted_at' => $order->submitted_at,
            'cancelled_at' => $order->cancelled_at,
            'total_amount' => $order->total_amount,
            'customer' => $order->customer ? [
              'id' => $order->customer->id,
              'name' => $order->customer->name,
            ] : null,
            'store' => $order->store ? [
              'id' => $order->store->id,
              'name' => $order->store->name,
            ] : null,
          ],
        ], 400);
      }

      return response()->json([
        'success' => false,
        'message' => $e->getMessage()
      ], 400);
    }
  }

  /**
   * CANCEL SALES ORDER
   */
  public function cancel(int $id)
  {
    try {
      $order = $this->service->cancel($id);

      return response()->json([
        'success' => true,
        'message' => 'Sales order berhasil dibatalkan',
        'data' => $order
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => $e->getMessage()
      ], 400);
    }
  }


  public function billable()
  {
    $orders = SalesOrder::with('customer')
      ->where('status', 'submitted')
      ->whereDoesntHave('billings', function ($q) {
        $q->whereNotIn('status', ['cancelled']);
      })
      ->latest()
      ->get();

    return response()->json([
      'success' => true,
      'meta' => [
        'description' => 'Sales order submitted dan belum memiliki billing aktif',
        'total' => $orders->count(),
      ],
      'data' => $orders
    ]);
  }
}
