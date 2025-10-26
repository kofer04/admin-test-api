<?php

use App\Http\Controllers\Action\RoleIndexController;
use App\Http\Controllers\Resource\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['middleware' => 'auth:sanctum'], function () {

    // Resource routes
    Route::resource('users', UserController::class);

    // Action routes
    Route::get('roles', RoleIndexController::class);
});
