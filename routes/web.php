<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\OrdersController;

// Schedule
Route::get('/', [ScheduleController::class, 'index'])->name('schedule.index');

// Products
Route::prefix('products')->name('products.')->group(function () {
  Route::get('/', [ProductsController::class, 'index'])->name('index');
  Route::get('/data', [ProductsController::class, 'getProducts'])->name('data');
  Route::get('/types/data', [ProductsController::class, 'getProductTypes'])->name('types.data');
});

// Orders
Route::prefix('orders')->name('orders.')->group(function () {
  Route::get('/', [OrdersController::class, 'index'])->name('index');
  Route::get('/data', [OrdersController::class, 'getOrders'])->name('data');
  Route::get('/show/{id}', [OrdersController::class, 'show'])->name('show');
  Route::get('/items/data/{id}', [OrdersController::class, 'getOrderItems'])->name('items.data');
  Route::get('/new', [OrdersController::class, 'create'])->name('create');
  Route::post('/new', [OrdersController::class, 'store'])->name('store');
  Route::delete('/delete/{id}', [OrdersController::class, 'delete'])->name('delete');
});
