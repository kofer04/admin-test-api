<?php

use App\Http\Controllers\Reports\ExportReportController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Resource\RoleController;
use App\Http\Controllers\Resource\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['middleware' => 'auth:sanctum'], function () {
    // Resource routes
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
        Route::get('/', ReportController::class)->name('index');
        Route::get('export', ExportReportController::class)->name('export');
    });
});
