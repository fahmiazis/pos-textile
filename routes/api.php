<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Master\UnitController;
use App\Http\Controllers\Api\Master\BrandController;
use App\Http\Controllers\Api\Master\CategoryController;


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
Route::prefix('master')->group(function () {

    // Units
    Route::apiResource('units', UnitController::class);

    // Brands
    Route::apiResource('brands', BrandController::class);

    // Categories
    Route::apiResource('categories', CategoryController::class);
   

});
