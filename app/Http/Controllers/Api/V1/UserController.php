<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\RetrieveUsersRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserExportService;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{

    public function __construct(
        protected UserRepository $repository,
        protected UserExportService $exportService
    ) {
        //
    }

    /**
     * Display a listing of the resource.
     * Returns all users (admin only endpoint)
     */
    public function index(RetrieveUsersRequest $request): JsonResource
    {
        $this->authorize('viewAny', User::class);

        $users = $this->repository->paginate($request->validated());

        return UserResource::collection($users);
    }

    /**
     * Export users as CSV
     * Exports all users based on applied filters
     */
    public function export(RetrieveUsersRequest $request): StreamedResponse
    {
        $this->authorize(Permission::UsersExport->value);

        return $this->exportService->exportCsv($request->validated());
    }
}

