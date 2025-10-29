<?php

namespace App\Providers;

use App\Repositories\Reports\JobBookingsRepository;
use App\Repositories\Reports\ConversionFunnelRepository;
use App\Services\Reports\JobBookings\JobBookingsService;
use App\Services\Reports\ConversionFunnel\ConversionFunnelService;
use App\Services\Reports\JobBookings\JobBookingsChartAdapter;
use App\Services\Reports\ConversionFunnel\ConversionFunnelChartAdapter;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Reports\JobBookingsRepositoryInterface;
use App\Repositories\Reports\ConversionFunnelRepositoryInterface;
class ReportServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
  public function register(): void
{
    // Repositories
    $this->app->bind(JobBookingsRepositoryInterface::class, JobBookingsRepository::class);
    $this->app->bind(ConversionFunnelRepositoryInterface::class, ConversionFunnelRepository::class);

    // Services
    $this->app->bind(JobBookingsService::class, JobBookingsService::class);
    $this->app->bind(ConversionFunnelService::class, ConversionFunnelService::class);

    // Adapters
    $this->app->bind(JobBookingsChartAdapter::class, JobBookingsChartAdapter::class);
    $this->app->bind(ConversionFunnelChartAdapter::class, ConversionFunnelChartAdapter::class);
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
