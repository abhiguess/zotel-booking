<?php

use App\Http\Controllers\Api\DiscountController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

Route::post('/search', SearchController::class);
Route::get('/inventory', InventoryController::class);
Route::get('/discounts', DiscountController::class);
