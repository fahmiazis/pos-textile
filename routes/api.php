<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

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

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');


Route::middleware('api.auth')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/me', function () {
        $user = auth()->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'abilities' => \App\Support\AbilityFormatter::fromPermissions(
                $user->getAllPermissions()->pluck('name')
            ),
        ]);
    });

    Route::prefix('master')->group(function () {

        /* ====== UNITS ====== */
        Route::middleware('permission:unit.view')->get('/units', [UnitController::class, 'index']);
        Route::middleware('permission:unit.view')->get('/units/{unit}', [UnitController::class, 'show']);
        Route::middleware('permission:unit.create')->post('/units', [UnitController::class, 'store']);
        Route::middleware('permission:unit.update')->put('/units/{unit}', [UnitController::class, 'update']);
        Route::middleware('permission:unit.delete')->delete('/units/{unit}', [UnitController::class, 'destroy']);

        /* ====== BRANDS ====== */
        Route::middleware('permission:brand.view')->get('/brands', [BrandController::class, 'index']);
        Route::middleware('permission:brand.view')->get('/brands/{brand}', [BrandController::class, 'show']);
        Route::middleware('permission:brand.create')->post('/brands', [BrandController::class, 'store']);
        Route::middleware('permission:brand.update')->put('/brands/{brand}', [BrandController::class, 'update']);
        Route::middleware('permission:brand.delete')->delete('/brands/{brand}', [BrandController::class, 'destroy']);
        Route::middleware('permission:brand.update')->put('/brands/{brand}/restore', [BrandController::class, 'restore']);

        /* ====== CATEGORIES ====== */
        Route::middleware('permission:category.view')->get('/categories', [CategoryController::class, 'index']);
        Route::middleware('permission:category.view')->get('/categories/{category}', [CategoryController::class, 'show']);
        Route::middleware('permission:category.create')->post('/categories', [CategoryController::class, 'store']);
        Route::middleware('permission:category.update')->put('/categories/{category}', [CategoryController::class, 'update']);
        Route::middleware('permission:category.delete')->delete('/categories/{category}', [CategoryController::class, 'destroy']);
        Route::middleware('permission:category.update')->put('/categories/{category}/restore', [CategoryController::class, 'restore']);

        /* ====== STORES ====== */
        Route::middleware('permission:store.view')->get('/stores', [StoreController::class, 'index']);
        Route::middleware('permission:store.view')->get('/stores/{store}', [StoreController::class, 'show']);
        Route::middleware('permission:store.create')->post('/stores', [StoreController::class, 'store']);
        Route::middleware('permission:store.update')->put('/stores/{store}', [StoreController::class, 'update']);
        Route::middleware('permission:store.delete')->delete('/stores/{store}', [StoreController::class, 'destroy']);
        Route::middleware('permission:store.update')->put('/stores/{store}/restore', [StoreController::class, 'restore']);

        /* ====== CUSTOMERS ====== */
        Route::middleware('permission:customer.view')->get('/customers', [CustomerController::class, 'index']);
        Route::middleware('permission:customer.view')->get('/customers/{customer}', [CustomerController::class, 'show']);
        Route::middleware('permission:customer.create')->post('/customers', [CustomerController::class, 'store']);
        Route::middleware('permission:customer.update')->put('/customers/{customer}', [CustomerController::class, 'update']);
        Route::middleware('permission:customer.delete')->delete('/customers/{customer}', [CustomerController::class, 'destroy']);
        Route::middleware('permission:customer.update')->put('/customers/{customer}/restore', [CustomerController::class, 'restore']);

        /* ====== SUPPLIERS ====== */
        Route::middleware('permission:supplier.view')->get('/suppliers', [SupplierController::class, 'index']);
        Route::middleware('permission:supplier.view')->get('/suppliers/{supplier}', [SupplierController::class, 'show']);
        Route::middleware('permission:supplier.create')->post('/suppliers', [SupplierController::class, 'store']);
        Route::middleware('permission:supplier.update')->put('/suppliers/{supplier}', [SupplierController::class, 'update']);
        Route::middleware('permission:supplier.delete')->delete('/suppliers/{supplier}', [SupplierController::class, 'destroy']);

        /* ====== PRODUCTS ====== */
        Route::middleware('permission:product.view')->get('/products', [ProductController::class, 'index']);
        Route::middleware('permission:product.view')->get('/products/{product}', [ProductController::class, 'show']);
        Route::middleware('permission:product.create')->post('/products', [ProductController::class, 'store']);
        Route::middleware('permission:product.update')->put('/products/{product}', [ProductController::class, 'update']);
        Route::middleware('permission:product.delete')->delete('/products/{product}', [ProductController::class, 'destroy']);
        Route::middleware('permission:product.update')->put('/products/{product}/restore', [ProductController::class, 'restore']);

        /* ====== DISCOUNTS ====== */
        Route::middleware('permission:discount.view')->get('/discounts', [DiscountController::class, 'index']);
        Route::middleware('permission:discount.view')->get('/discounts/{discount}', [DiscountController::class, 'show']);
        Route::middleware('permission:discount.create')->post('/discounts', [DiscountController::class, 'store']);
        Route::middleware('permission:discount.update')->put('/discounts/{discount}', [DiscountController::class, 'update']);
        Route::middleware('permission:discount.delete')->delete('/discounts/{discount}', [DiscountController::class, 'destroy']);
        Route::middleware('permission:discount.update')->put('/discounts/{discount}/restore', [DiscountController::class, 'restore']);

        /* ====== VEHICLES ====== */
        Route::middleware('permission:vehicle.view')->get('/vehicles', [VehicleController::class, 'index']);
        Route::middleware('permission:vehicle.view')->get('/vehicles/{vehicle}', [VehicleController::class, 'show']);
        Route::middleware('permission:vehicle.create')->post('/vehicles', [VehicleController::class, 'store']);
        Route::middleware('permission:vehicle.update')->put('/vehicles/{vehicle}', [VehicleController::class, 'update']);
        Route::middleware('permission:vehicle.delete')->delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy']);

        /* ====== DRIVERS ====== */
        Route::middleware('permission:driver.view')->get('/drivers', [DriverController::class, 'index']);
        Route::middleware('permission:driver.view')->get('/drivers/{driver}', [DriverController::class, 'show']);
        Route::middleware('permission:driver.create')->post('/drivers', [DriverController::class, 'store']);
        Route::middleware('permission:driver.update')->put('/drivers/{driver}', [DriverController::class, 'update']);
        Route::middleware('permission:driver.delete')->delete('/drivers/{driver}', [DriverController::class, 'destroy']);
        Route::middleware('permission:driver.update')->put('/drivers/{driver}/restore', [DriverController::class, 'restore']);
    });
});

/*
|--------------------------------------------------------------------------
| Inventory 
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Purhase 
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Sales Orders 
|--------------------------------------------------------------------------
*/