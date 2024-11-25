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
    
});

Route::get('getProducts' , [ProductController::class , 'getProducts']);
Route::get('getProduct/{id}' , [ProductController::class , 'getProduct']);
Route::post('storeProduct' , [ProductController::class , 'storeProduct']);
Route::put('updateProduct/{id}' , [ProductController::class , 'updateProduct']);
Route::delete('deleteProduct/{id}' , [ProductController::class , 'deleteProduct']);

Route::get('getShopProducts/{id}' , [ShopController::class , 'getShopProducts2']);





