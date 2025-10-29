<?php

namespace App\Http\Controllers\Resource;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resource\Role\RetrieveRolesRequest;
use App\Http\Resources\RoleResource;
use App\Repositories\RoleRepository;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleController extends Controller
{

    public function __construct(
        protected RoleRepository $repository
    ) {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index(RetrieveRolesRequest $request): JsonResource
    {
        // $this->authorize('viewAny', arguments: User::class);

        $roles = $this->repository->get($request->validated());
        return RoleResource::collection($roles);
    }

//     /**
//      * Store a newly created resource in storage.
//      */
//     public function store(StoreUserRequest $request): JsonResource
//     {
//         $this->authorize('create', User::class);
//         $role = $this->repository->create($request->validated());
//         return new RoleResource($role);
//     }

//     /**
//      * Display the specified resource.
//      */
//     public function show(FindUsersRequest $request): JsonResource
//     {
//         $this->authorize('view', User::class);
//         $role = $this->repository->find($request->validated());
//         return new RoleResource($role);
//     }

//     /**
//      * Update the specified resource in storage.
//      */
//     public function update(UpdateUsersRequest $request, User $user): JsonResource
//     {
//         $this->authorize('update', User::class);
//         $role = $this->repository->update($role, $request->validated());
//         return new RoleResource($role);
//     }

//     /**
//      * Remove the specified resource from storage.
//      */
//     public function destroy(string $id): Response
//     {
//         $this->authorize('delete', User::class);
//         $this->user->delete($id);
//         return response()->noContent();
//     }
}
