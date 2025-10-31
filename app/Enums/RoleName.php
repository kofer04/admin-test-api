<?php
namespace App\Enums;

enum RoleName: string
{
    /**
     * Reports Permissions
     */
    case SuperAdmin = 'Super Admin';
    case MarketUser = 'Market User';
}
