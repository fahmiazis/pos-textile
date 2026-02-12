<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {}

    /*
    |--------------------------------------------------------------------------
    | SALES ORDER METRICS
    |--------------------------------------------------------------------------
    */

    protected function getStatusSummary(): array
    {
        return $this->service->salesOrderStatusSummary();
    }

    public function totalSoToBilling()
    {
        return response()->json([
            'data' => $this->service->totalSoToBilling()
        ]);
    }

    public function totalSoDraft()
    {
        $summary = $this->getStatusSummary();

        return response()->json([
            'data' => $summary['draft'] ?? 0
        ]);
    }

    public function totalSoSubmitted()
    {
        $summary = $this->getStatusSummary();

        return response()->json([
            'data' => $summary['submitted'] ?? 0
        ]);
    }

    public function totalSoCompleted()
    {
        $summary = $this->getStatusSummary();

        return response()->json([
            'data' => $summary['completed'] ?? 0
        ]);
    }

    public function totalSoCancelled()
    {
        $summary = $this->getStatusSummary();

        return response()->json([
            'data' => $summary['cancelled'] ?? 0
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | REVENUE & CUSTOMER
    |--------------------------------------------------------------------------
    */

    public function totalRevenue()
    {
        return response()->json([
            'data' => $this->service->totalRevenue()
        ]);
    }

    public function totalCustomers()
    {
        return response()->json([
            'data' => $this->service->totalCustomers()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUCT PERFORMANCE
    |--------------------------------------------------------------------------
    */

    public function topProducts()
    {
        return response()->json([
            'data' => $this->service->topProducts()
        ]);
    }

    public function bottomProducts()
    {
        return response()->json([
            'data' => $this->service->bottomProducts()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RECENT TRANSACTIONS
    |--------------------------------------------------------------------------
    */

    public function recentTransactions()
    {
        return response()->json([
            'data' => $this->service->recentTransactions()
        ]);
    }
}
