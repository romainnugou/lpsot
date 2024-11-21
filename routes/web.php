<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\OrdersController;

Route::get('/', [ScheduleController::class, 'index'])->name('schedule.index');
Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
Route::get('/orders', [OrdersController::class, 'index'])->name('orders.index');
