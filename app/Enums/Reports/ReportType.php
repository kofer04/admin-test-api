<?php

namespace App\Services\Reports\Enums;

enum ReportType: string
{
    case JobBookings = 'job-bookings';
    case ConversionFunnel = 'conversion-funnel';
}
