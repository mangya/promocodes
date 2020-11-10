<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PromocodeController;

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

Route::post('/get-access-token', [AuthController::class, 'requestToken'])->name('requestToken');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/promocodes/generate', [PromocodeController::class, 'generatePromocode'])->name('generatePromocode');

	Route::post('/promocodes/get', [PromocodeController::class, 'getPromocodes'])->name('getPromocodes');

	Route::post('/promocodes/validate', [PromocodeController::class, 'validatePromocode'])->name('validatePromocode');
});