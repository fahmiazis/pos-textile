<?php

namespace App\Services\Master;

use App\Models\Master\PurchasePricing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Exception;

class PurchasePricingService
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = PurchasePricing::with(['product', 'supplier']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate(15);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function find(int $id): PurchasePricing
    {
        return PurchasePricing::with(['product', 'supplier'])->findOrFail($id);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create(array $data): PurchasePricing
    {
        return PurchasePricing::create([
            'product_id' => $data['product_id'],
            'supplier_id' => $data['supplier_id'],
            'price_per_meter' => $data['price_per_meter'],
            'min_qty' => $data['min_qty'] ?? 0,
            'valid_from' => $data['valid_from'],
            'valid_to' => $data['valid_to'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(int $id, array $data): PurchasePricing
    {
        $pricing = PurchasePricing::findOrFail($id);

        $pricing->update([
            'product_id' => $data['product_id'],
            'supplier_id' => $data['supplier_id'],
            'price_per_meter' => $data['price_per_meter'],
            'min_qty' => $data['min_qty'] ?? 0,
            'valid_from' => $data['valid_from'],
            'valid_to' => $data['valid_to'] ?? null,
            'is_active' => $data['is_active'] ?? $pricing->is_active,
        ]);

        return $pricing;
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function delete(int $id): void
    {
        PurchasePricing::findOrFail($id)->delete();
    }


        /*
    |--------------------------------------------------------------------------
    | RESOLVE PRICE (AUTO PRICING ENGINE)
    |--------------------------------------------------------------------------
    */
    public function resolvePrice(
        int $productId,
        int $supplierId,
        float $qtyBase,
        ?string $orderDate = null
    ): float {

        $date = $orderDate
            ? Carbon::parse($orderDate)
            : now();

        $pricing = PurchasePricing::where('product_id', $productId)
            ->where('supplier_id', $supplierId)
            ->where('is_active', true)
            ->where('min_qty', '<=', $qtyBase)
            ->where('valid_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')
                  ->orWhere('valid_to', '>=', $date);
            })
            ->orderByDesc('min_qty') // ambil tier terbaik
            ->first();

        if (! $pricing) {
            throw new Exception('Purchase pricing tidak ditemukan atau belum dibuat untuk produk atau supplier ini');
        }

        return (float) $pricing->price_per_meter;
    }
}
