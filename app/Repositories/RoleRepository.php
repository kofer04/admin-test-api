<?php

namespace App\Repositories;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class RoleRepository extends Repository
{
    protected string $model = Role::class;

    public function get(array $params = []): EloquentCollection
    {
        $userId = data_get($params, 'user_id', null);

        // Create the query ONCE and reuse it
        $query = $this->query($params);

        if($userId){
            // Join the pivot table directly
            $query->whereIn('id', function($subQuery) use ($userId) {
                $subQuery->select('role_id')
                    ->from('model_has_roles')
                    ->where('model_id', $userId)
                    ->where('model_type', User::class);
            });
        }

        return $query->get();
    }
}
