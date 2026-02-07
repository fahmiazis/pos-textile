<?php

namespace App\Services\Master;

use App\Models\Master\Discount;
use Illuminate\Support\Facades\DB;

class DiscountService
{
    public function paginate(array $params)
    {
        $query = Discount::with('store');

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (isset($params['is_active'])) {
            $query->where('is_active', $params['is_active']);
        }

        if (!empty($params['discount_type'])) {
            $query->where('discount_type', $params['discount_type']);
        }

        if (!empty($params['store_id'])) {
            $query->where('store_id', $params['store_id']);
        }

        if (!empty($params['active_today']) && $params['active_today']) {
            $today = now()->toDateString();
            $query->whereDate('start_date', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $today);
                });
        }

        return $query
            ->orderBy('id', 'desc')
            ->paginate($params['per_page'] ?? 10);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['code']      = $this->generateCode();
            $data['is_active'] = $data['is_active'] ?? true;

            return Discount::create($data);
        });
    }

    public function find(int $id)
    {
        return Discount::with('store')->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $discount = Discount::findOrFail($id);
        $discount->update($data);

        return $discount;
    }

    /**
     * Soft delete + set inactive
     */
    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $discount = Discount::findOrFail($id);

            $discount->update([
                'is_active' => false,
            ]);

            $discount->delete();

            return $discount;
        });
    }

    /**
     * Restore + set active
     */
    public function restore(int $id)
    {
        return DB::transaction(function () use ($id) {
            $discount = Discount::withTrashed()
                ->with('store')
                ->findOrFail($id);

            $discount->restore();

            $discount->update([
                'is_active' => true,
            ]);

            return $discount;
        });
    }

    /**
     * Generate auto code: DISC-0001
     */
    protected function generateCode(): string
    {
        $prefix = 'DISC-';

        $lastCode = Discount::withTrashed()
            ->where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->value('code');

        if (!$lastCode) {
            return $prefix . '0001';
        }

        $number = (int) str_replace($prefix, '', $lastCode);

        return $prefix . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
    }
}
