<?php

use Illuminate\Support\Facades\Route;


Route::get('/ping', function () {
    return response()->json([
        'message' => 'API OK'
    ]);
});


/*
|--------------------------------------------------------------------------
| MASTER DATA
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\Master\UnitController;
use App\Http\Controllers\Api\Master\BrandController;
use App\Http\Controllers\Api\Master\CategoryController;
use App\Http\Controllers\Api\Master\StoreController;
use App\Http\Controllers\Api\Master\CustomerController;
use App\Http\Controllers\Api\Master\SupplierController;
use App\Http\Controllers\Api\Master\ProductController;
use App\Http\Controllers\Api\Master\DiscountController;
use App\Http\Controllers\Api\Master\VehicleController;
use App\Http\Controllers\Api\Master\DriverController;

Route::prefix('master')->group(function () {
    // Units
    Route::apiResource('units', UnitController::class);
    // Brands
    Route::apiResource('brands', BrandController::class);
    // Categories
    Route::apiResource('categories', CategoryController::class);
    // Stores
     Route::apiResource('stores', StoreController::class);
    // Customers
     Route::apiResource('customers', CustomerController::class);
    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);
    // Products
    Route::apiResource('products', ProductController::class);
    // Discounts
    Route::apiResource('discounts', DiscountController::class);
    // Vehicles
    Route::apiResource('vehicles', VehicleController::class);
    // Drivers
    Route::apiResource('drivers', DriverController::class);

});
