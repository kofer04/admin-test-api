<?php

use App\Http\Controllers\Api\V1\Reports\ConversionFunnelController;
use App\Http\Controllers\Api\V1\Reports\JobBookingsController;
use App\Http\Controllers\Resource\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());

    Route::prefix('/v1')->name('api.v1.')->group(function () {
        /**
         * Reports
         */
        Route::get('job-bookings', [JobBookingsController::class, 'index']) ->name('job-bookings.index');
        Route::get('job-bookings/export', [JobBookingsController::class, 'export']) ->name('job-bookings.export');

        Route::get('conversion-funnel', [ConversionFunnelController::class, 'index']) ->name('conversion-funnel.index');
        Route::get('conversion-funnel/export', [ConversionFunnelController::class, 'export']) ->name('conversion-funnel.export');

        /**
         * Resources
         */
        Route::get('roles', [RoleController::class, 'index']) ->name('roles.index');

    });
});
