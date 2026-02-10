<?php

namespace App\Services\Purchase;

use App\Models\Purchase\PurchaseOrder;
use App\Models\Purchase\PurchaseOrderItem;
use App\Models\Master\Unit;
use App\Services\Common\DocumentNumberService;
use App\Services\Inventory\InventoryService;
use App\Services\Master\PurchasePricingService;
use Illuminate\Support\Facades\DB;
use Exception;

class PurchaseOrderService
{
    protected InventoryService $inventoryService;
    protected PurchasePricingService $pricingService;

    public function __construct(
        InventoryService $inventoryService,
        PurchasePricingService $pricingService
    ) {
        $this->inventoryService = $inventoryService;
        $this->pricingService   = $pricingService;
    }

    /*
    =========================
    FINAL LOCK CHECK
    =========================
    */
    private function isLocked(PurchaseOrder $po): bool
    {
        return $po->submitted_at !== null
            || $po->received_at !== null
            || $po->cancelled_at !== null;
    }

    /*
    =========================
    CREATE PO (DRAFT)
    =========================
    */
    public function create(array $data, ?int $userId): PurchaseOrder
    {
        if (! $userId) {
            throw new Exception('User not authenticated');
        }

        return DB::transaction(function () use ($data, $userId) {

            $po = PurchaseOrder::create([
                'po_number'   => DocumentNumberService::generate('purchase_orders', 'po_number', 'PO'),
                'store_id'    => $data['store_id'],
                'supplier_id' => $data['supplier_id'],
                'order_date'  => $data['order_date'],
                'status'      => 'draft',
                'created_by'  => $userId,
                'notes'       => $data['notes'] ?? null,
            ]);

            $this->syncItems($po, $data['items']);

            return $po;
        });
    }

    /*
    =========================
    UPDATE DRAFT
    =========================
    */
    public function updateDraft(int $id, array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($id, $data) {

            $po = PurchaseOrder::with('items')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($this->isLocked($po)) {
                throw new Exception('Gagal, tidak bisa diedit PO sudah diproses atau dibatalkan');
            }

            if ($po->status !== 'draft') {
                throw new Exception('PO bukan draft');
            }

            $po->update([
                'supplier_id' => $data['supplier_id'],
                'order_date'  => $data['order_date'],
                'notes'       => $data['notes'] ?? $po->notes,
            ]);

            $po->items()->delete();
            $this->syncItems($po, $data['items']);

            return $po;
        });
    }

    /*
    =========================
    SUBMIT PO
    =========================
    */
    public function submit(int $id): PurchaseOrder
    {
        return DB::transaction(function () use ($id) {

            $po = PurchaseOrder::lockForUpdate()->findOrFail($id);

            if ($this->isLocked($po)) {
                throw new Exception('Gagal submit PO, PO sudah diproses atau dibatalkan');
            }

            if ($po->status !== 'draft') {
                throw new Exception('PO hanya bisa disubmit dari draft');
            }

            $po->update([
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]);

            return $po;
        });
    }

    /*
    =========================
    CANCEL PO
    =========================
    */
    public function cancel(int $id): PurchaseOrder
    {
        return DB::transaction(function () use ($id) {

            $po = PurchaseOrder::lockForUpdate()->findOrFail($id);

            if ($this->isLocked($po)) {
                throw new Exception('Gagal membatalkan, PO sudah diproses atau dibatalkan');
            }

            if (! in_array($po->status, ['draft', 'submitted'])) {
                throw new Exception('PO tidak bisa dibatalkan');
            }

            $po->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return $po;
        });
    }

    /*
    =========================
    RECEIVE PO
    =========================
    */
    public function receive(int $id): PurchaseOrder
    {
        return DB::transaction(function () use ($id) {

            $po = PurchaseOrder::with('items')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($po->received_at !== null) {
                throw new Exception('PO sudah diterima');
            }

            if ($po->status !== 'submitted') {
                throw new Exception('PO belum disubmit');
            }

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
                'status'      => 'received',
                'received_at' => now(),
            ]);

            return $po;
        });
    }

    /*
    =========================
    SYNC ITEMS (PRICING BASED)
    =========================
    */
    private function syncItems(PurchaseOrder $po, array $items): void
    {
        $totalQty    = 0;
        $totalAmount = 0;

        foreach ($items as $item) {

            $uom = Unit::findOrFail($item['uom_id']);
            $qtyBase = $item['qty_input'] * $uom->multiplier;

            $price = $this->pricingService->resolvePrice(
                $item['product_id'],
                $po->supplier_id,
                $qtyBase,
                $po->order_date
            );

            $subtotal = $price * $qtyBase;

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id'        => $item['product_id'],
                'uom_id'            => $uom->id,
                'qty_input'         => $item['qty_input'],
                'qty_base'          => $qtyBase,
                'price'             => $price,
                'discount'          => 0,
                'subtotal'          => $subtotal,
            ]);

            $totalQty    += $qtyBase;
            $totalAmount += $subtotal;
        }

        $po->update([
            'total_qty'    => $totalQty,
            'total_amount' => $totalAmount,
        ]);
    }


}
