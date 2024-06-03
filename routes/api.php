<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Open Routes
Route::post("register", [AuthController::class, "register"]);
Route::post("login", [AuthController::class, "login"]);

Route::get("product", [ProductController::class, "index"]);
Route::get("product/{uuid}", [ProductController::class, "show"]);

Route::group([
    'middleware' => 'auth:api',
    // 'prefix' => 'auth'
], function ($router) {
    // AUTH
    Route::post('logout', [AuthController::class, "logout"]);
    Route::post('refresh', [AuthController::class, "refresh"]);
    Route::post('me', [AuthController::class, "me"]);
    // END AUTH

    // USER
    Route::resource('user', UserController::class);
    // END USER

    // PRODUCT
    Route::post("product", [ProductController::class, "store"]);
    Route::patch("product/{uuid}", [ProductController::class, "update"]);
    Route::delete("product/{uuid}", [ProductController::class, "destroy"]);
    // END PRODUCT

    // SHIPPING
    Route::resource('shipping', ShippingController::class);
    // END SHIPPING

    // PAYMENT
    Route::resource('payment', PaymentController::class);
    // END PAYMENT

    // ORDER
    Route::get('order', [OrderController::class, "index"]);
    Route::get('order/{uuid}', [OrderController::class, "show"]);
    Route::post('order', [OrderController::class, "store"]);
    Route::patch('order/cancel/{uuid}', [OrderController::class, "cancel"]);
    Route::patch('order/confirm/{uuid}', [OrderController::class, "confirm"]);
    Route::patch('order/delivery/{uuid}', [OrderController::class, "delivery"]);
    Route::patch('order/received/{uuid}', [OrderController::class, "received"]);
    // END ORDER
});
