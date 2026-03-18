<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'search'])->name('search');
Route::get('/inventory', [PageController::class, 'inventory'])->name('inventory');
Route::get('/discounts', [PageController::class, 'discounts'])->name('discounts');
