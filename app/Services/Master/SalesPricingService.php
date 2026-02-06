<?php

namespace App\Services\Master;

use App\Models\Master\SalesPricing;
use Carbon\Carbon;

class SalesPricingService
{
    /**
     * ======================
     * MASTER DATA (MENU)
     * ======================
     */
    public function paginate(array $params)
    {
        $query = SalesPricing::with(['product', 'store']);

        if (!empty($params['product_id'])) {
            $query->where('product_id', $params['product_id']);
        }

        if (!empty($params['store_id'])) {
            $query->where('store_id', $params['store_id']);
        }

        if (!empty($params['customer_type'])) {
            $query->where('customer_type', $params['customer_type']);
        }

        if (isset($params['is_active'])) {
            $query->where('is_active', $params['is_active']);
        }

        return $query
            ->orderByDesc('id')
            ->paginate($params['per_page'] ?? 10);
    }

    public function create(array $data): SalesPricing
    {
        return SalesPricing::create($data);
    }

    /**
     * ======================
     * BUSINESS LOGIC
     * Dipakai oleh Sales Order
     * ======================
     *
     * RULE:
     * - product_id cocok
     * - store_id cocok
     * - customer_type cocok
     * - min_qty <= qty
     * - aktif
     * - valid date
     * - ambil tier min_qty TERBESAR
     */
    public function resolve(
        int $productId,
        int $storeId,
        string $customerType,
        float $qtyBase,
        ?string $orderDate = null
    ): ?SalesPricing {

        $date = $orderDate
            ? Carbon::parse($orderDate)
            : Carbon::today();

        return SalesPricing::query()
            ->where('product_id', $productId)
            ->where('store_id', $storeId)
            ->where('customer_type', $customerType)
            ->where('is_active', true)
            ->where('min_qty', '<=', $qtyBase)
            ->whereDate('valid_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')
                    ->orWhereDate('valid_to', '>=', $date);
            })
            ->orderByDesc('min_qty') 
            ->first();
    }
}
