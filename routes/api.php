<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:Sanctum');


Route::controller(UserController::class)->group(function () {

    Route::middleware(['auth:Sanctum'])->group(function () {

        Route::post('logout', 'logout');

    });
    Route::post('register', 'register');
    Route::post('login', 'login');
});


Route::controller(ProductController::class)->group(function () {

    Route::middleware(['auth:Sanctum'])->group(function () {

        Route::get('getProducts', 'getProducts');
        Route::get('getProduct/{id}', 'getProduct');
        Route::post('storeProduct', 'storeProduct');
        Route::put('updateProduct/{id}', 'updateProduct');
        Route::delete('deleteProduct/{id}', 'deleteProduct');
        Route::post('setAddress/{id}', 'setAddress');
        Route::post('setImage/{id}', 'setImage');

    });
    Route::get('products/search', 'searchProduct');

});

Route::controller(ShopController::class)->group(function () {

    Route::middleware(['auth:Sanctum'])->group(function () {

        Route::get('getShopProducts/{id}', 'getShopProducts2');
        Route::get('shop/products/search', 'searchShopProducts');
        Route::get('shops/search', 'searchShop');

    });
});

Route::controller(OrderController::class)->group(function () {
    Route::middleware(['auth:Sanctum'])->group(function () {

        Route::get('getPurchasedOrders' ,  'getPurchasedOrders');
        Route::get('getOrderProducts/{id}' ,  'getOrderProducts');

    });
});
Route::get('setTotal/{id}' , [OrderController::class, 'setTotal']);






