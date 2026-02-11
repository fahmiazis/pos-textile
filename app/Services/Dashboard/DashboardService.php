<?php

namespace App\Services\Dashboard;

use App\Models\Sales\SalesOrder;
use App\Models\Sales\Billing;
use App\Models\Sales\Collection;
use App\Models\Master\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    protected int $cacheTtl = 60; // detik

    /*
    |--------------------------------------------------------------------------
    | SALES ORDER SUMMARY (Single Query + Cache)
    |--------------------------------------------------------------------------
    */

    public function salesOrderStatusSummary(): array
    {
        return Cache::remember('dashboard:so_status', $this->cacheTtl, function () {
            return SalesOrder::select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();
        });
    }

    public function totalSoToBilling(): int
    {
        return Cache::remember('dashboard:so_to_billing', $this->cacheTtl, function () {
            return Billing::distinct()->count('sales_order_id');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | REVENUE & CUSTOMER (Cached)
    |--------------------------------------------------------------------------
    */

    public function totalRevenue(): float
    {
        return Cache::remember('dashboard:total_revenue', $this->cacheTtl, function () {
            return (float) Collection::sum('amount');
        });
    }

    public function totalCustomers(): int
    {
        return Cache::remember('dashboard:total_customers', $this->cacheTtl, function () {
            return Customer::count();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUCT PERFORMANCE (Optimized Join + Cache)
    |--------------------------------------------------------------------------
    */

    public function topProducts()
    {
        return Cache::remember('dashboard:top_products', $this->cacheTtl, function () {
            return DB::table('sales_order_items as soi')
                ->join('sales_orders as so', function ($join) {
                    $join->on('so.id', '=', 'soi.sales_order_id')
                        ->where('so.status', '=', 'completed');
                })
                ->join('products as p', 'p.id', '=', 'soi.product_id')
                ->select('p.id', 'p.name', DB::raw('SUM(soi.qty_input) as total_qty'))
                ->groupBy('p.id', 'p.name')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get();
        });
    }

    public function bottomProducts()
    {
        return Cache::remember('dashboard:bottom_products', $this->cacheTtl, function () {
            return DB::table('sales_order_items as soi')
                ->join('sales_orders as so', function ($join) {
                    $join->on('so.id', '=', 'soi.sales_order_id')
                        ->where('so.status', '=', 'completed');
                })
                ->join('products as p', 'p.id', '=', 'soi.product_id')
                ->select('p.id', 'p.name', DB::raw('SUM(soi.qty_input) as total_qty'))
                ->groupBy('p.id', 'p.name')
                ->orderBy('total_qty')
                ->limit(5)
                ->get();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RECENT TRANSACTIONS (Lightweight Select)
    |--------------------------------------------------------------------------
    */

    public function recentTransactions()
    {
        return Cache::remember('dashboard:recent_transactions', $this->cacheTtl, function () {
            return SalesOrder::with([
                'customer:id,name',
                'billings:id,sales_order_id,total_amount'
            ])
                ->where('status', 'completed')
                ->latest('completed_at')
                ->limit(5)
                ->get([
                    'id',
                    'so_number',
                    'customer_id',
                    'total_amount',
                    'completed_at'
                ]);
        });
    }
}
