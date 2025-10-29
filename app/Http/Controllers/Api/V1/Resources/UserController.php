<?php

namespace App\Http\Controllers\Resource;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resource\User\FindUsersRequest;
use App\Http\Requests\Resource\User\RetrieveUsersRequest;
use App\Http\Requests\Resource\User\StoreUserRequest;
use App\Http\Requests\Resource\User\UpdateUsersRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class UserController extends Controller
{

    public function __construct(
        protected UserRepository $user
    ) {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index(RetrieveUsersRequest $request): JsonResource
    {
        $this->authorize('viewAny', User::class);
        $users = $this->user->paginate($request->validated());
        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResource
    {
        $this->authorize('create', User::class);
        $user = $this->user->create($request->validated());
        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(FindUsersRequest $request): JsonResource
    {
        $this->authorize('view', User::class);
        $user = $this->user->find($request->validated());
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUsersRequest $request, User $user): JsonResource
    {
        $this->authorize('update', User::class);
        $user = $this->user->update($user, $request->validated());
        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): Response
    {
        $this->authorize('delete', User::class);
        $this->user->delete($id);
        return response()->noContent();
    }
}
