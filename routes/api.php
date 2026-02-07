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
use App\Http\Controllers\Api\Master\SalesPricingController;


Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');


Route::middleware('api.auth')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/me', function () {
        $user = request()->user();

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
        Route::middleware('permission:unit.update')->put('/units/{unit}/restore', [UnitController::class, 'restore']);

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
        Route::middleware('permission:supplier.update')->put('/suppliers/{supplier}/restore', [SupplierController::class, 'restore']);

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
        Route::middleware('permission:vehicle.update')->put('/vehicles/{vehicle}/restore', [VehicleController::class, 'restore']);

        /* ====== DRIVERS ====== */
        Route::middleware('permission:driver.view')->get('/drivers', [DriverController::class, 'index']);
        Route::middleware('permission:driver.view')->get('/drivers/{driver}', [DriverController::class, 'show']);
        Route::middleware('permission:driver.create')->post('/drivers', [DriverController::class, 'store']);
        Route::middleware('permission:driver.update')->put('/drivers/{driver}', [DriverController::class, 'update']);
        Route::middleware('permission:driver.delete')->delete('/drivers/{driver}', [DriverController::class, 'destroy']);
        Route::middleware('permission:driver.update')->put('/drivers/{driver}/restore', [DriverController::class, 'restore']);

        Route::middleware('permission:sales_pricing.view')
            ->get('/sales-pricings', [SalesPricingController::class, 'index']);
        Route::middleware('permission:sales_pricing.create')
            ->post('/sales-pricings', [SalesPricingController::class, 'store']);
    });
});


use App\Http\Controllers\Api\Access\UserController;
use App\Http\Controllers\Api\Access\RoleController;
use App\Http\Controllers\Api\Access\PermissionController;

Route::middleware('api.auth')
    ->prefix('access')
    ->group(function () {

        /* ================= USERS ================= */
        Route::middleware('permission:user.view')
            ->get('/users', [UserController::class, 'index']);

        Route::middleware('permission:user.create')
            ->post('/users', [UserController::class, 'store']);

        Route::middleware('permission:user.update')
            ->put('/users/{user}', [UserController::class, 'update']);

        Route::middleware('permission:user.delete')
            ->delete('/users/{user}', [UserController::class, 'destroy']);

        Route::middleware('permission:user.update')
            ->put('/users/{user}/roles', [UserController::class, 'syncRoles']);

        Route::middleware('permission:user.update')
            ->patch('/users/{user}/active', [UserController::class, 'toggleActive']);

        Route::middleware('permission:user.update')
            ->patch('/users/{user}/reset-password', [UserController::class, 'resetPassword']);

        /* ================= ROLES ================= */
        Route::middleware('permission:role.view')
            ->get('/roles', [RoleController::class, 'index']);

        Route::middleware('permission:role.create')
            ->post('/roles', [RoleController::class, 'store']);

        Route::middleware('permission:role.update')
            ->put('/roles/{role}', [RoleController::class, 'update']);

        Route::middleware('permission:role.delete')
            ->delete('/roles/{role}', [RoleController::class, 'destroy']);

        Route::middleware('permission:role.update')
            ->put('/roles/{role}/permissions', [RoleController::class, 'syncPermissions']);


        /* =============== PERMISSIONS =============== */
        Route::middleware('permission:permission.view')
            ->get('/permissions', [PermissionController::class, 'index']);

        Route::middleware('permission:permission.create')
            ->post('/permissions', [PermissionController::class, 'store']);

        Route::middleware('permission:permission.update')
            ->put('/permissions/{permission}', [PermissionController::class, 'update']);

        Route::middleware('permission:permission.delete')
            ->delete('/permissions/{permission}', [PermissionController::class, 'destroy']);
    });


/*
|--------------------------------------------------------------------------
| Inventory 
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\Inventory\InventoryController;

Route::middleware('api.auth')->group(function () {
    Route::prefix('inventory')->group(function () {
        Route::middleware('permission:inventory.view')
            ->get('/availability', [InventoryController::class, 'availability']);
        Route::middleware('permission:inventory.view')
            ->get('/movements', [InventoryController::class, 'movements']);
        Route::middleware('permission:inventory.manage')
            ->post('/stock-in', [InventoryController::class, 'stockIn']);
    });
});

/*
|--------------------------------------------------------------------------
| Sales Orders 
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\Sales\SalesOrderController;
use App\Http\Controllers\Api\Sales\BillingController;
use App\Http\Controllers\Api\Sales\CollectionController;
use App\Http\Controllers\Api\Sales\RefundController;

Route::middleware('api.auth')->group(function () {
    Route::prefix('sales')->group(function () {
        Route::get('/orders/collected', [CollectionController::class, 'collected']);
        Route::get('/orders/billable', [SalesOrderController::class, 'billable']);
        Route::get('/orders', [SalesOrderController::class, 'index']);
        Route::get('/orders/{id}', [SalesOrderController::class, 'show']);
        Route::middleware('permission:sales_order.create')
            ->post('/orders', [SalesOrderController::class, 'store']);
        Route::middleware('permission:sales_order.update')
            ->put('/orders/{id}', [SalesOrderController::class, 'update']);
        Route::middleware('permission:sales_order.submit')
            ->post('/orders/{id}/submit', [SalesOrderController::class, 'submit']);
        Route::middleware('permission:sales_order.cancel')
            ->post('/orders/{id}/cancel', [SalesOrderController::class, 'cancel']);
        Route::middleware('permission:billing.create')
            ->post('/billings', [BillingController::class, 'store']);
        Route::middleware('permission:collection.create')
            ->post('/collections', [CollectionController::class, 'store']);
    });
});

/*
|--------------------------------------------------------------------------
 Billing
|--------------------------------------------------------------------------
*/
Route::middleware('api.auth')->prefix('billings')->group(function () {
    Route::get('/', [BillingController::class, 'index']);
    Route::middleware('permission:billing.create')
        ->post('/', [BillingController::class, 'store']);
});

Route::middleware(['api.auth', 'permission:collection.create'])
    ->post('/billings/{billing}/collect', [CollectionController::class, 'store']);
Route::middleware(['api.auth', 'permission:refund.create'])
    ->post('/sales-orders/{salesOrder}/refund/full', [RefundController::class, 'full']);

/*
|--------------------------------------------------------------------------
| Refunds
|--------------------------------------------------------------------------
*/
Route::post(
    '/sales-orders/{id}/refund/full',
    [\App\Http\Controllers\Api\Sales\RefundController::class, 'full']
);


/*|--------------------------------------------------------------------------
| Purchase Orders
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\Purchase\PurchaseOrderController;

Route::middleware('api.auth')->group(function () {
    Route::prefix('purchase')->group(function () {
        Route::middleware('permission:purchase_order.view')
            ->get('/orders', [PurchaseOrderController::class, 'index']);
        Route::middleware('permission:purchase_order.create')
            ->post('/orders', [PurchaseOrderController::class, 'store']);
        Route::middleware('permission:purchase_order.update')
            ->put('/orders/{id}', [PurchaseOrderController::class, 'update']);
        Route::middleware('permission:purchase_order.view')
            ->get('/orders/{id}', [PurchaseOrderController::class, 'show']);
        Route::middleware('permission:purchase_order.submit')
            ->post('/orders/{id}/submit', [PurchaseOrderController::class, 'submit']);
        Route::middleware('permission:purchase_order.cancel')
            ->post('/orders/{id}/cancel', [PurchaseOrderController::class, 'cancel']);
        Route::post('/orders/{id}/receive', [PurchaseOrderController::class, 'receive']);
    });
});


/*|--------------------------------------------------------------------------
| Purchase Billings
|--------------------------------------------------------------------------*/

use App\Http\Controllers\Api\Purchase\PurchaseBillingController;

Route::middleware('api.auth')->prefix('purchase')->group(function () {

    Route::get('/billings', [PurchaseBillingController::class, 'index']);
    Route::post('/billings/from-po/{id}', [PurchaseBillingController::class, 'createFromPo']);
});

/*|--------------------------------------------------------------------------
| Purchase Payments
|--------------------------------------------------------------------------*/

use App\Http\Controllers\Api\Purchase\PurchasePaymentController;

Route::middleware('api.auth')
    ->prefix('purchase')
    ->group(function () {

        Route::get(
            '/payments',
            [PurchasePaymentController::class, 'index']
        );

        Route::post(
            '/payments',
            [PurchasePaymentController::class, 'store']
        );

        Route::get(
            '/payments/{id}',
            [PurchasePaymentController::class, 'show']
        );
    });