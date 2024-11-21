<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\OrdersController;

Route::get('/', [ScheduleController::class, 'index'])->name('schedule.index');

Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
Route::get('/products/data', [ProductsController::class, 'getProducts'])->name('products.data');
Route::get('/product/types/data', [ProductsController::class, 'getProductTypes'])->name('product.types.data');

Route::get('/orders', [OrdersController::class, 'index'])->name('orders.index');
