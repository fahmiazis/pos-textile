<?php

namespace App\Services\Purchase;

use App\Models\Purchase\PurchaseOrder;
use App\Models\Purchase\PurchaseOrderItem;
use App\Models\Master\Unit;
use App\Services\Common\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use App\Services\Inventory\InventoryService;
use Exception;

class PurchaseOrderService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }


    /**
     * =========================
     * CREATE PO (DRAFT)
     * =========================
     */
    public function create(array $data, ?int $userId): PurchaseOrder
    {
        if (!$userId) {
            throw new Exception('User not authenticated');
        }

        return DB::transaction(function () use ($data, $userId) {

            $po = PurchaseOrder::create([
                'po_number' => DocumentNumberService::generate(
                    'purchase_orders',
                    'po_number',
                    'PO'
                ),
                'store_id' => $data['store_id'],
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'],
                'status' => 'draft',
                'created_by' => $userId,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($po, $data['items']);

            return $po;
        });
    }

    /**
     * =========================
     * UPDATE DRAFT PO
     * =========================
     */
    public function updateDraft(int $id, array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($id, $data) {

            $po = PurchaseOrder::with('items')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($po->status !== 'draft') {
                throw new Exception('PO sudah diproses, tidak bisa diedit');
            }

            $po->update([
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'],
                'notes' => $data['notes'] ?? $po->notes,
            ]);

            $po->items()->delete();

            $this->syncItems($po, $data['items']);

            return $po;
        });
    }


    public function submit(int $id): PurchaseOrder
    {
        return DB::transaction(function () use ($id) {

            $po = PurchaseOrder::lockForUpdate()->findOrFail($id);

            if ($po->status !== 'draft') {
                throw new Exception('PO hanya bisa disubmit dari status draft');
            }

            $po->update([
                'status' => 'submitted',
                'submitted_at' => now()
            ]);

            return $po;
        });
    }

    public function cancel(int $id): PurchaseOrder
    {
        return DB::transaction(function () use ($id) {

            $po = PurchaseOrder::lockForUpdate()->findOrFail($id);

            if (!in_array($po->status, ['draft', 'submitted'])) {
                throw new Exception('PO tidak bisa dibatalkan');
            }

            $po->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            return $po;
        });
    }

    /**
     * =========================
     * RECEIVE PO
     * =========================
     */


    public function receive(int $id): PurchaseOrder
    {
        return DB::transaction(function () use ($id) {

            $po = PurchaseOrder::with('items')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($po->status !== 'submitted') {
                throw new Exception('PO hanya bisa diterima dari status submitted');
            }

            // === STOCK IN ===
            foreach ($po->items as $item) {
                $this->inventoryService->stockIn(
                    $po->store_id,
                    $item->product_id,
                    $item->qty_base,
                    'purchase_order',
                    $po->id
                );
            }

            $po->update([
                'status' => 'received',
                'received_at' => now()
            ]);

            return $po;
        });
    }




    /**
     * =========================
     * SYNC ITEMS
     * =========================
     */
    private function syncItems(PurchaseOrder $po, array $items): void
    {
        $totalQty = 0;
        $totalAmount = 0;

        foreach ($items as $item) {

            $uom = Unit::findOrFail($item['uom_id']);

            $qtyBase = $item['qty_input'] * $uom->multiplier;

            $subtotal = ($item['price'] * $qtyBase)
                - ($item['discount'] ?? 0);

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id' => $item['product_id'],
                'uom_id' => $uom->id,
                'qty_input' => $item['qty_input'],
                'qty_base' => $qtyBase,
                'price' => $item['price'],
                'discount' => $item['discount'] ?? 0,
                'subtotal' => $subtotal,
            ]);

            $totalQty += $qtyBase;
            $totalAmount += $subtotal;
        }

        $po->update([
            'total_qty' => $totalQty,
            'total_amount' => $totalAmount,
        ]);
    }
}
