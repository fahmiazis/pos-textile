<?php

namespace App\Services\Sales;

use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\Master\Unit;
use App\Models\Master\Product;
use App\Services\Common\DocumentNumberService;
use App\Services\Inventory\InventoryService;
use App\Services\Master\SalesPricingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class SalesOrderService
{
    private const TAX_RATE = 0.11;

    protected InventoryService $inventoryService;
    protected SalesPricingService $pricingService;

    public function __construct(
        InventoryService $inventoryService,
        SalesPricingService $pricingService
    ) {
        $this->inventoryService = $inventoryService;
        $this->pricingService   = $pricingService;
    }

    /**
     * CREATE SALES ORDER (DRAFT)
     */
    public function create(array $data, ?int $userId): SalesOrder
    {
        if (!$userId) {
            throw new Exception('User not authenticated');
        }

        return DB::transaction(function () use ($data, $userId) {

            $taxIncluded = array_key_exists('tax_included', $data)
                ? (bool) $data['tax_included']
                : false;

            $order = SalesOrder::create([
                'so_number'   => DocumentNumberService::generate(
                    'sales_orders',
                    'so_number',
                    'SO'
                ),
                'store_id'    => $data['store_id'],
                'customer_id' => $data['customer_id'],
                'order_date'  => $data['order_date'],
                'status'      => 'draft',
                'created_by'  => $userId,
                'notes'       => $data['notes'] ?? null,
                'cash_discount' => $data['cash_discount'] ?? 0,
                'tax_included' => $taxIncluded,
            ]);

            $this->syncItems($order, $data['items']);

            return $order;
        });
    }

    /**
     * LIST SALES ORDERS
     */
    public function getList(array $filters): array
    {
        $query = SalesOrder::with('customer');

        if (!empty($filters['status'])) {
            $statuses = is_array($filters['status'])
                ? $filters['status']
                : [$filters['status']];

            $query->whereIn('status', $statuses);
        }

        return [
            'filters' => [
                'status' => $filters['status'] ?? null,
            ],
            'total' => $query->count(),
            'data' => $query->latest()->get(),
        ];
    }

    /**
     * SEARCH SALES ORDERS
     */
    public function search(array $filters): array
    {
        $keyword = trim($filters['search'] ?? $filters['so_number'] ?? '');

        $query = SalesOrder::with('customer');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('so_number', 'like', '%' . $keyword . '%')
                    ->orWhereHas('customer', function ($customerQuery) use ($keyword) {
                        $customerQuery
                            ->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('code', 'like', '%' . $keyword . '%');
                    });
            });
        }

        if (!empty($filters['status'])) {
            $statuses = is_array($filters['status'])
                ? $filters['status']
                : [$filters['status']];

            $query->whereIn('status', $statuses);
        }

        return [
            'filters' => [
                'search' => $keyword !== '' ? $keyword : null,
                'status' => $filters['status'] ?? null,
            ],
            'total' => $query->count(),
            'data' => $query->latest()->get(),
        ];
    }

    /**
     * BILLABLE SALES ORDERS
     */
    public function getBillable(): array
    {
        $orders = SalesOrder::with('customer')
            ->where('status', 'submitted')
            ->whereDoesntHave('billings', function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            })
            ->latest()
            ->get();

        return [
            'total' => $orders->count(),
            'data' => $orders,
        ];
    }

    /**
     * SUBMIT SALES ORDER (with response payload)
     */
    public function submitWithResponse(int $id): array
    {
        try {
            $order = $this->submit($id);

            return [
                'ok' => true,
                'status' => 200,
                'message' => 'Sales order berhasil disubmit. Total sudah termasuk PPN 11%.',
                'data' => $order->load([
                    'items.product',
                    'customer',
                    'store'
                ]),
            ];
        } catch (Exception $e) {
            $order = SalesOrder::with(['customer', 'store'])
                ->find($id);

            if ($order && $order->status === 'cancelled') {
                return [
                    'ok' => false,
                    'status' => 400,
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
                ];
            }

            return [
                'ok' => false,
                'status' => 400,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * CANCEL SALES ORDER (with response payload)
     */
    public function cancelWithResponse(int $id): array
    {
        try {
            $order = $this->cancel($id);

            return [
                'ok' => true,
                'status' => 200,
                'message' => 'Sales order berhasil dibatalkan',
                'data' => $order,
            ];
        } catch (Exception $e) {
            return [
                'ok' => false,
                'status' => 400,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * UPDATE SALES ORDER (DRAFT)
     */
    public function updateDraft(int $id, array $data): SalesOrder
    {
        return DB::transaction(function () use ($id, $data) {

            $order = SalesOrder::with(['items', 'customer'])
                ->lockForUpdate()
                ->findOrFail($id);

            if ($order->status !== 'draft') {
                throw new Exception('Sales order tidak bisa diedit karena sudah disubmit');
            }

            $order->update([
                'customer_id' => $data['customer_id'],
                'order_date'  => $data['order_date'],
                'notes'       => $data['notes'] ?? $order->notes,
                'cash_discount' => array_key_exists('cash_discount', $data)
                    ? $data['cash_discount']
                    : $order->cash_discount,
                'tax_included' => array_key_exists('tax_included', $data)
                    ? (bool) $data['tax_included']
                    : $order->tax_included,
            ]);

            $order->items()->delete();

            $this->syncItems($order, $data['items']);

            return $order;
        });
    }

    /**
     * SUBMIT SALES ORDER (RESERVE STOCK)
     */
    public function submit(int $salesOrderId): SalesOrder
    {
        return DB::transaction(function () use ($salesOrderId) {

            $order = SalesOrder::with('items')
                ->lockForUpdate()
                ->findOrFail($salesOrderId);

            if ($order->status === 'cancelled') {
                $cancelledAt = $order->cancelled_at
                    ? $order->cancelled_at->toDateTimeString()
                    : null;
                $message = 'Sales order sudah dibatalkan';
                if ($cancelledAt) {
                    $message .= ' pada ' . $cancelledAt;
                }
                throw new Exception($message);
            }

            if ($order->status !== 'draft') {
                throw new Exception('Sales order tidak bisa disubmit');
            }

            $subtotalAmount = (float) $order->subtotal_amount;
            $cashDiscount = (float) ($order->cash_discount ?? 0);
            if ($cashDiscount < 0) {
                $cashDiscount = 0;
            }
            if ($cashDiscount > $subtotalAmount) {
                throw ValidationException::withMessages([
                    'cash_discount' => 'Cash discount tidak boleh melebihi subtotal.',
                ]);
            }

            $taxableAmount = $subtotalAmount - $cashDiscount;
            $taxRate = self::TAX_RATE;

            if ($order->tax_included) {
                // Jika sudah include, pakai nilai yang sudah dihitung saat create/update draft
                $taxAmount = $order->tax_amount;
                $totalAmount = $order->total_amount;

                // Fallback untuk data lama yang belum ada hitungan tax
                if ($taxAmount === null || $totalAmount === null) {
                    [$taxAmount, $totalAmount] = $this->calculateTaxTotals(
                        $taxableAmount,
                        true
                    );
                }
            } else {
                // Exclude: hitung pajak saat submit
                [$taxAmount, $totalAmount] = $this->calculateTaxTotals(
                    $taxableAmount,
                    true
                );
            }

            foreach ($order->items as $item) {
                $this->inventoryService->reserveStock(
                    $order->store_id,
                    $item->product_id,
                    $item->qty_base,
                    'sales_order',
                    $order->id
                );
            }

            $order->update([
                'status'       => 'submitted',
                'submitted_at' => now(),
                'cash_discount' => $cashDiscount,
                // setelah submit, total sudah include tax
                'tax_included' => true,
                'tax_rate'     => $taxRate,
                'tax_amount'   => $taxAmount,
                'total_amount' => $totalAmount,
            ]);

            return $order;
        });
    }

    /**
     * CANCEL SALES ORDER (RELEASE STOCK)
     */
    public function cancel(int $salesOrderId): SalesOrder
    {
        return DB::transaction(function () use ($salesOrderId) {

            $order = SalesOrder::with('items')
                ->lockForUpdate()
                ->findOrFail($salesOrderId);

            if ($order->status === 'cancelled') {
                throw new Exception('Sales order sudah dibatalkan');
            }

            if (!in_array($order->status, ['draft', 'submitted'])) {
                throw new Exception('Sales order tidak bisa dibatalkan');
            }

            if ($order->status === 'submitted') {
                foreach ($order->items as $item) {
                    $this->inventoryService->releaseReservedStock(
                        $order->store_id,
                        $item->product_id,
                        $item->qty_base,
                        'sales_order',
                        $order->id
                    );
                }
            }

            $order->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return $order;
        });
    }

    /**
     * ==================================================
     * INTERNAL HELPER
     * AUTO RESOLVE SALES PRICING (ERP WAY)
     * ==================================================
     */
    private function syncItems(SalesOrder $order, array $items): void
    {
        $totalQty = 0;
        $subtotalAmount = 0;

        // pastikan customer ke-load
        $order->loadMissing('customer');

        foreach ($items as $item) {

            $uom = Unit::findOrFail($item['uom_id']);
            $qtyBase = $item['qty_input'] * $uom->multiplier;

            // 🔥 CARI SALES PRICING
            $pricing = $this->pricingService->resolve(
                $item['product_id'],
                $order->store_id,
                $order->customer->customer_type,
                $qtyBase,
                $order->order_date
            );

            if (!$pricing) {
                throw new Exception(
                    "Sales pricing tidak ditemukan untuk product ID {$item['product_id']}"
                );
            }

            $price = $pricing->price_per_meter;

            $discount = $item['discount'] ?? 0;
            $subtotal = ($price * $qtyBase) - $discount;

            SalesOrderItem::create([
                'sales_order_id' => $order->id,
                'product_id'     => $item['product_id'],
                'uom_id'         => $uom->id,
                'qty_input'      => $item['qty_input'],
                'qty_base'       => $qtyBase,
                'price'          => $price, // 🔒 snapshot final
                'discount'       => $discount,
                'subtotal'       => $subtotal,
            ]);

            $totalQty += $qtyBase;
            $subtotalAmount += $subtotal;
        }

        $cashDiscount = (float) ($order->cash_discount ?? 0);
        if ($cashDiscount < 0) {
            $cashDiscount = 0;
        }
        if ($cashDiscount > $subtotalAmount) {
            throw ValidationException::withMessages([
                'cash_discount' => 'Cash discount tidak boleh melebihi subtotal.',
            ]);
        }

        $taxableAmount = $subtotalAmount - $cashDiscount;
        [$taxAmount, $totalAmount] = $this->calculateTaxTotals(
            $taxableAmount,
            (bool) $order->tax_included
        );

        $order->update([
            'total_qty'    => $totalQty,
            'subtotal_amount' => $subtotalAmount,
            'cash_discount' => $cashDiscount,
            'tax_rate'     => self::TAX_RATE,
            'tax_amount'   => $taxAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    private function calculateTaxTotals(
        float $taxableAmount,
        bool $shouldCompute
    ): array {
        $taxRate = self::TAX_RATE;

        if ($shouldCompute) {
            $taxAmount = round($taxableAmount * $taxRate, 2);
            $totalAmount = $taxableAmount + $taxAmount;
        } else {
            $taxAmount = 0;
            $totalAmount = $taxableAmount;
        }

        return [$taxAmount, $totalAmount];
    }
}
