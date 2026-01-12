<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Master\UnitController;
// nanti nambah:
// use App\Http\Controllers\Api\Master\BrandController;
// use App\Http\Controllers\Api\Master\CategoryController;

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
   

    // Categories
   

});
