<?php

namespace App\Repositories;

use Spatie\Permission\Models\Role;

class RoleRepository extends Repository
{
    protected string $model = Role::class;
}
