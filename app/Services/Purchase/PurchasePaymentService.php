<?php

namespace App\Services\Purchase;

use App\Models\Purchase\PurchaseBilling;
use App\Models\Purchase\PurchasePayment;
use Illuminate\Support\Facades\DB;
use Exception;

class PurchasePaymentService
{
    /**
     * =========================
     * CREATE PAYMENT
     * =========================
     */
    public function pay(array $data, int $userId): PurchasePayment
    {
        if (!$userId) {
            throw new Exception('User not authenticated');
        }

        return DB::transaction(function () use ($data, $userId) {

            $billing = PurchaseBilling::lockForUpdate()
                ->findOrFail($data['purchase_billing_id']);

            if ($billing->status === 'paid') {
                throw new Exception('Billing sudah lunas');
            }

            if ($data['amount'] > $billing->remaining_amount) {
                throw new Exception('Nominal pembayaran melebihi sisa hutang');
            }

            //  BUSINESS RULE: CASH vs NON-CASH
            $method = strtolower($data['payment_method']);

            if ($method !== 'cash' && empty($data['reference_number'])) {
                throw new Exception(
                    'Reference number wajib diisi untuk metode pembayaran non-cash'
                );
            }

            $payment = PurchasePayment::create([
                'purchase_billing_id' => $billing->id,
                'supplier_id'         => $billing->supplier_id,
                'store_id'            => $billing->store_id,
                'payment_date'        => $data['payment_date'],
                'amount'              => $data['amount'],
                'payment_method'      => $method,
                'reference_number'    => $data['reference_number'] ?? null,
                'notes'               => $data['notes'] ?? null,
                'created_by'          => $userId,
            ]);

            $billing->paid_amount += $data['amount'];
            $billing->remaining_amount -= $data['amount'];

            $billing->status = $billing->remaining_amount == 0
                ? 'paid'
                : 'partial';

            $billing->save();

            return $payment;
        });
    }
}
