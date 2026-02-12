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
                'tax_included' => $data['tax_included'] ?? false,
            ]);

            $this->syncItems($order, $data['items']);

            return $order;
        });
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
                $cashDiscount = $subtotalAmount;
            }

            $taxableAmount = $subtotalAmount - $cashDiscount;
            $taxRate = self::TAX_RATE;

            if ($order->tax_included) {
                $taxAmount = round($taxableAmount * $taxRate / (1 + $taxRate), 2);
                $totalAmount = $taxableAmount;
            } else {
                $taxAmount = round($taxableAmount * $taxRate, 2);
                $totalAmount = $taxableAmount + $taxAmount;
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
            $cashDiscount = $subtotalAmount;
        }

        $order->update([
            'total_qty'    => $totalQty,
            'subtotal_amount' => $subtotalAmount,
            'cash_discount' => $cashDiscount,
            'tax_rate'     => self::TAX_RATE,
            'tax_amount'   => 0,
            'total_amount' => $subtotalAmount - $cashDiscount,
        ]);
    }
}
