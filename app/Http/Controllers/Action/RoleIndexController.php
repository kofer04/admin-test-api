<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RetrieveRolesRequest;
use App\Repositories\RoleRepository;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleIndexController extends Controller
{
    public function __construct(private RoleRepository $repository)
    {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(RetrieveRolesRequest $request): JsonResource
    {
        $roles = $this->repository->get($request->validated());
        return RoleResource::collection($roles);
    }
}
