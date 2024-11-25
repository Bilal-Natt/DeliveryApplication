<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::controller(UserController::class)->group(function () {

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('logout', 'logout');

    });
    Route::post('register', 'register');
    Route::post('login', 'login');
});
