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
    $result = $this->service->getList($request->only(['status']));

    return response()->json([
      'success' => true,
      'meta' => [
        'filters' => [
          'status' => $result['filters']['status']
        ],
        'total' => $result['total']
      ],
      'data' => $result['data']
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

  public function search(Request $request)
  {
    $data = $request->validate([
      'search' => 'nullable|string',
      'so_number' => 'nullable|string',
      'status' => 'nullable',
    ]);
    $result = $this->service->search($data);

    return response()->json([
      'success' => true,
      'meta' => [
        'filters' => [
          'search' => $result['filters']['search'],
          'status' => $result['filters']['status'],
        ],
        'total' => $result['total'],
      ],
      'data' => $result['data'],
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
      'cash_discount' => 'nullable|numeric|min:0',
      'tax_included' => 'nullable|boolean',

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
      ])->only([
        'id',
        'so_number',
        'status',
        'order_date',
        'total_qty',
        'subtotal_amount',
        'cash_discount',
        'tax_included',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'created_by',
        'notes',
        'created_at',
        'updated_at',
        'items',
        'store',
        'customer',
      ])
    ], 201);
  }

  public function update(Request $request, int $id)
  {
    $data = $request->validate([
      'customer_id' => 'required|exists:customers,id',
      'order_date'  => 'required|date',
      'cash_discount' => 'nullable|numeric|min:0',
      'tax_included' => 'nullable|boolean',

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
    $result = $this->service->submitWithResponse($id);

    return response()->json([
      'success' => $result['ok'],
      'message' => $result['message'],
      'data' => $result['data'] ?? null,
    ], $result['status']);
  }

  /**
   * CANCEL SALES ORDER
   */
  public function cancel(int $id)
  {
    $result = $this->service->cancelWithResponse($id);

    return response()->json([
      'success' => $result['ok'],
      'message' => $result['message'],
      'data' => $result['data'] ?? null,
    ], $result['status']);
  }


  public function billable()
  {
    $result = $this->service->getBillable();

    return response()->json([
      'success' => true,
      'meta' => [
        'description' => 'Sales order submitted dan belum memiliki billing aktif',
        'total' => $result['total'],
      ],
      'data' => $result['data']
    ]);
  }
}
