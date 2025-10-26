<?php

namespace App\Http\Controllers\Resource;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RetrieveRolesRequest;
use App\Http\Resources\PermissionResource;
use App\Repositories\PermissionRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(private PermissionRepository $repository)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(RetrieveRolesRequest $request): JsonResource
    {
        // $this->authorize('viewAny', Permission::class);

        $permissions = $this->repository->paginate($request->validated());
        return PermissionResource::collection($permissions);
    }

}
