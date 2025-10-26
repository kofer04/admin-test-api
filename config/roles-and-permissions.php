<?php

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
        'Admin' => [
            'report:job-bookings:read',
            'report:conversion-funnel:read',
            'markets:read',
            'markets:write',
            'users:read',
            'users:write',
        ],
        'Market User' => [
            'report:job-bookings:read',
            'report:conversion-funnel:read',
            'markets:read',
        ],
    ],

];
