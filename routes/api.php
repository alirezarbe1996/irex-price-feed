<?php

use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\PriceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');





Route::get('/exchanges', [ExchangeController::class, 'index']);

Route::controller(PriceController::class)->group(function () {
    Route::get('/prices/daily', 'getDailyPrices');
    Route::get('/prices/weekly', 'getWeeklyPrices');
    Route::get('/prices/monthly', 'getMonthlyPrices');
});
