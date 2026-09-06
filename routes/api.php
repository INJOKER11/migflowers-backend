<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\MonobankWebhookController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PromoCodeController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/status/{order_number}', [OrderController::class, 'getOrderStatus']);
Route::post('/monobank/webhook', MonobankWebhookController::class)->name('orders.payment.webhook');

Route::get('/reviews', [ReviewController::class, 'index']);

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{slug}', [PostController::class, 'show']);

Route::get('/districts', [DistrictController::class, 'index']);

Route::post('/questions', [QuestionController::class, 'store']);

Route::post('/promo-codes/first-order', [PromoCodeController::class, 'store'])
    ->middleware('throttle:5,1');
Route::post('/promo-codes/validate', [PromoCodeController::class, 'check'])
    ->middleware('throttle:20,1');
