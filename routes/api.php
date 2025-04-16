<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| مسیرهای API
|--------------------------------------------------------------------------
|
| در اینجا می‌توانید مسیرهای API برنامه خود را ثبت کنید. این مسیرها
| توسط RouteServiceProvider بارگذاری می‌شوند و همگی آنها
| به گروه میان‌افزار "api" اختصاص داده می‌شوند.
|
*/

// مسیرهای احراز هویت
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// مسیرهای محافظت شده
Route::middleware('auth:sanctum')->group(function () {
    // مسیرهای کاربر
    Route::get('/user', [UserController::class, 'show']);
    Route::get('/user/balance', [UserController::class, 'balance']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // مسیرهای سفارش
    Route::apiResource('/orders', OrderController::class)->except(['show', 'update']);

    // مسیرهای تراکنش
    Route::get('/transactions', [TransactionController::class, 'index']);
});
