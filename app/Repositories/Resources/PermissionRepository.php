<?php

namespace App\Repositories;

use Spatie\Permission\Models\Permission;

class PermissionRepository extends Repository
{
    protected string $model = Permission::class;
}
