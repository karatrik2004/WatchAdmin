<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\ProductController as V1ProductController;
use App\Http\Controllers\Api\V1\DealController as V1DealController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** API Version 1 */
Route::prefix('v1')->group(function () {
    Route::apiResource('products', V1ProductController::class);
    Route::apiResource('deals', V1DealController::class);
});
