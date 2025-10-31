<?php

use App\Http\Controllers\Api\V1\Actions\AuthenticatedUserController;
use App\Http\Controllers\Api\V1\MarketController;
use App\Http\Controllers\Api\V1\Reports\ConversionFunnelController;
use App\Http\Controllers\Api\V1\Reports\JobBookingsController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', AuthenticatedUserController::class);

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
        Route::get('markets', [MarketController::class, 'index']) ->name('markets.index');
        Route::get('markets/export', [MarketController::class, 'export']) ->name('markets.export');
        Route::get('users', [UserController::class, 'index']) ->name('users.index');
        Route::get('users/export', [UserController::class, 'export']) ->name('users.export');

        /**
         * User Settings
         */
        Route::get('user/settings', [SettingController::class, 'index']) ->name('user.settings.index');
        Route::put('user/settings', [SettingController::class, 'update']) ->name('user.settings.update');

    });
});
