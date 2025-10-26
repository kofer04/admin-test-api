<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends Repository
{
    protected string $model = User::class;
}
