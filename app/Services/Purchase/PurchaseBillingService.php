<?php

namespace App\Services\Purchase;

use App\Models\Purchase\PurchaseBilling;
use App\Models\Purchase\PurchaseOrder;
use App\Services\Common\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Exception;

class PurchaseBillingService
{
    /**
     * Create AP Billing from PO (GR)
     */
    public function createFromPurchaseOrder(int $purchaseOrderId): PurchaseBilling
    {
        return DB::transaction(function () use ($purchaseOrderId) {

            $po = PurchaseOrder::lockForUpdate()->findOrFail($purchaseOrderId);

            if ($po->status !== 'received') {
                throw new Exception('AP Billing hanya bisa dibuat dari PO received');
            }

            $exists = PurchaseBilling::where('purchase_order_id', $po->id)->exists();
            if ($exists) {
                throw new Exception('Billing untuk PO ini sudah ada');
            }

            return PurchaseBilling::create([
                'purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'store_id' => $po->store_id,
                'billing_number' => DocumentNumberService::generate(
                    'purchase_billings',
                    'billing_number',
                    'AP'
                ),
                'billing_date' => now()->toDateString(),
                'total_amount' => $po->total_amount,
                'paid_amount' => 0,
                'remaining_amount' => $po->total_amount,
                'status' => 'unpaid',
            ]);
        });
    }
}
