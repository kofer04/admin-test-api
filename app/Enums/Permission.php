<?php
namespace App\Enums;

enum Permission: string
{
    /**
     * Reports Permissions
     */
    case ReadReportJobBookings = 'read-report:job-bookings';
    case ExportReportJobBookings = 'export-report:job-bookings';


    case ReadReportConversionFunnel = 'read-report:conversion-funnel';
    case ExportReportConversionFunnel = 'export-report:conversion-funnel';

    /**
     * Resources Permissions
     */
    case MarketsRead = 'markets:read';
    case MarketsWrite = 'markets:write';

    // User permissions
    case UsersRead = 'users:read';
    case UsersWrite = 'users:write';
}
