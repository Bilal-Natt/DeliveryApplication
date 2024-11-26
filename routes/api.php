<?php

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
<<<<<<< HEAD
    Route::post('setAddress/{id}', 'setAddress');
    Route::post('setImage/{id}', 'setImage');

    
});

Route::get('getProducts' , [ProductController::class , 'getProducts']);
Route::get('getProduct/{id}' , [ProductController::class , 'getProduct']);
Route::post('storeProduct' , [ProductController::class , 'storeProduct']);
Route::put('updateProduct/{id}' , [ProductController::class , 'updateProduct']);
Route::delete('deleteProduct/{id}/{number}' , [ProductController::class , 'deleteProduct']);
=======

});

Route::controller(ProductController::class)->group(function () {
>>>>>>> e050dd854822e6e400c536dc1bd078f778cc7215

    Route::middleware(['auth:Sanctum'])->group(function () {

        Route::get('getProducts', 'getProducts');
        Route::get('getProduct/{id}', 'getProduct');
        Route::post('storeProduct', 'storeProduct');
        Route::put('updateProduct/{id}', 'updateProduct');
        Route::delete('deleteProduct/{id}', 'deleteProduct');
    });

});

Route::controller(ShopController::class)->group(function () {

    Route::middleware(['auth:Sanctum'])->group(function () {

        Route::get('getShopProducts/{id}', [ShopController::class, 'getShopProducts2']);

    });
});





