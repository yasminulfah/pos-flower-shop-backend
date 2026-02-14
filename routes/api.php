<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PackagingController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\OrderController;

// Auth (Login & Register)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Produk & Kategori (Bisa dilihat siapa saja)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);

// Data Shipping & Packaging (Untuk ditampilkan di form Checkout)
Route::get('/packagings', [PackagingController::class, 'index']);
Route::get('/shippings', [ShippingController::class, 'index']);

// Image
Route::get('/images/search', [App\Http\Controllers\Api\ImageController::class, 'search']);

Route::middleware('auth:sanctum')->group(function () {

    // --- User & Profile ---
    Route::get('/user', [UserController::class, 'profile']);
    Route::post('/user/update', [UserController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard-stats', [DashboardController::class, 'index']);

    // --- Admin Routes (Manajemen Produk & Kategori) ---
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']); 
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // --- Admin Routes (Manajemen Shipping & Packaging) ---
    Route::post('/packagings', [PackagingController::class, 'store']);
    Route::put('/packagings/{id}', [PackagingController::class, 'update']);
    Route::delete('/packagings/{id}', [PackagingController::class, 'destroy']);

    Route::post('/shippings', [ShippingController::class, 'store']);
    Route::put('/shippings/{id}', [ShippingController::class, 'update']);
    Route::delete('/shippings/{id}', [ShippingController::class, 'destroy']);

    // --- Order Routes (Checkout & Histori) ---
    Route::post('/order', [OrderController::class, 'checkout']);
    Route::get('/order/history', [OrderController::class, 'index']);
    Route::get('/order/{id}', [OrderController::class, 'show']);
    Route::put('/order/{id}/status', [OrderController::class, 'updateStatus']); 
    Route::post('/orders/hold', [OrderController::class, 'holdOrder']);
    Route::get('/orders/pending', [OrderController::class, 'getPendingOrders']);
});
