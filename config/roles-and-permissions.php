<?php

use App\Enums\Permission;

return [

    /*
    |--------------------------------------------------------------------------
    | Roles and their assigned permissions
    |--------------------------------------------------------------------------
    |
    | Define all roles and permissions here.
    | Each key is a role name, and its value is an array of permissions.
    | This will serve as the single source of truth for seeding and syncing.
    |
    */

    'roles' => [
        'Super Admin' => [],
        'Market User' => [
            Permission::ReadReportJobBookings->value,
            Permission::ReadReportConversionFunnel->value,
            Permission::ExportReportJobBookings->value,
            Permission::ExportReportConversionFunnel->value,
            Permission::MarketsRead->value,
            Permission::MarketsWrite->value,
        ],
    ],

];
