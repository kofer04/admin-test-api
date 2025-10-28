<?php

namespace App\Providers;

use App\Services\Reports\ReportService;
use Illuminate\Support\ServiceProvider;

class ReportServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ReportService as a singleton to ensure only one instance exists
        $this->app->singleton(ReportService::class, function ($app) {
            return new ReportService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
