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
      'meta' => [
        'source_document' => [
          'type' => $salesOrder->source_document_type,
          'id'   => $salesOrder->source_document_id,
        ]
      ]
    ]);
  }

  /**
   * Create Sales Order (DRAFT)
   */
  public function store(Request $request)
  {
    $data = $request->validate([
      'store_id' => 'required|exists:stores,id',
      'customer_id' => 'required|exists:customers,id',
      'order_date' => 'required|date',
      'notes' => 'nullable|string',
      // 'source_document_type' => 'nullable|string|max:30',
      // 'source_document_id'   => 'nullable|integer',

      'items' => 'required|array|min:1',
      'items.*.product_id' => 'required|exists:products,id',
      'items.*.uom_id' => 'required|exists:units,id',
      'items.*.qty_input' => 'required|numeric|min:0.001',
      'items.*.qty_base' => 'required|numeric|min:0.001',
      'items.*.price' => 'required|numeric|min:0',
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

  /**
   * Submit Sales Order (RESERVE STOCK)
   */
  public function submit(int $id)
  {
    try {
      $salesOrder = $this->service->submit($id);

      return response()->json([
        'success' => true,
        'message' => 'Sales order berhasil disubmit dan stok telah di-reserve',
        'data' => [
          'id'           => $salesOrder->id,
          'so_number'    => $salesOrder->so_number,
          'status'       => $salesOrder->status,
          'store'        => [
            'id'   => $salesOrder->store->id,
            'name' => $salesOrder->store->name,
          ],
          'customer'     => [
            'id'   => $salesOrder->customer->id,
            'name' => $salesOrder->customer->name,
          ],
          'total_qty'    => $salesOrder->total_qty,
          'total_amount' => $salesOrder->total_amount,
          'submitted_at' => $salesOrder->submitted_at,
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
        'meta' => [
          'allowed_actions' => [
            'submit' => false,
            'cancel' => true,
          ]
        ]
      ], 422);
    }
  }



  /**
   * Cancel Sales Order (RELEASE STOCK)
   */
  public function cancel(int $id)
  {
    try {
      $salesOrder = $this->service->cancel($id);

      return response()->json([
        'success' => true,
        'message' => 'Sales order berhasil dibatalkan dan stok dikembalikan',
        'data' => [
          'id'           => $salesOrder->id,
          'so_number'    => $salesOrder->so_number,
          'status'       => $salesOrder->status,
          'cancelled_at' => $salesOrder->cancelled_at,
          'store' => [
            'id'   => $salesOrder->store->id,
            'name' => $salesOrder->store->name,
          ],
          'customer' => [
            'id'   => $salesOrder->customer->id,
            'name' => $salesOrder->customer->name,
          ],
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
        'meta' => [
          'allowed_actions' => [
            'submit' => false,
            'cancel' => false,
          ]
        ]
      ], 422);
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
