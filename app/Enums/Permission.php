<?php
namespace App\Enums;

enum Permission: string
{
    // Report permissions
    case ReadReportJobBookings = 'read-report:job-bookings';
    case ReadReportConversionFunnel = 'read-report:conversion-funnel';

    // Market permissions
    case MarketsRead = 'markets:read';
    case MarketsWrite = 'markets:write';

    // User permissions
    case UsersRead = 'users:read';
    case UsersWrite = 'users:write';
}
