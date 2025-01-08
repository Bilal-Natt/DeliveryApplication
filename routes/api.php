<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::controller(UserController::class)->group(function () {

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('logout', 'logout');
        Route::put('updateUser', 'updateUser');
        Route::post('uploadImage', 'uploadImage',);
        Route::get('getImage', 'getImage');

    });
    Route::post('register', 'register');
    Route::post('login', 'login');
});


Route::controller(ProductController::class)->group(function () {

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::get('products/getAllProducts', 'getAllProducts');
        Route::get('product/getCertainProduct/{id}', 'getCertainProduct');
        Route::post('product/storeProduct', 'storeProduct');
        Route::put('product/updateProduct/{id}', 'updateProduct');
        Route::delete('product/deleteProduct', 'deleteProduct');
        Route::get('products/search', 'searchProduct');
    });

});

Route::controller(ShopController::class)->group(function () {

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::get('shop/getShopProducts/{id}', 'getShopProducts');
        Route::get('shop/products/search', 'searchShopProducts');
        Route::get('shops/search', 'searchShop');
    });
});

Route::controller(OrderController::class)->group(function () {

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::get('orders/getOrders', 'getOrders');
        Route::get('order/getOrderProducts', 'getOrderProducts');
        Route::post('order/store', 'storeOrder');
        Route::delete('order/deleteOrders', 'deleteOrders');
        Route::put('order/updateOrder', 'updateOrder');

    });

});




