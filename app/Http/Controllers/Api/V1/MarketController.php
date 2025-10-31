<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Market\RetrieveMarketsRequest;
use App\Http\Resources\MarketResource;
use App\Models\Market;
use App\Repositories\MarketRepository;
use App\Services\MarketExportService;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketController extends Controller
{

    public function __construct(
        protected MarketRepository $repository,
        protected MarketExportService $exportService
    ) {
        //
    }

    /**
     * Display a listing of the resource.
     * Returns markets filtered by user access level.
     *
     * SECURITY: User filtering is handled automatically by the repository
     * using auth()->user(), so no need to pass user_id here.
     */
    public function index(RetrieveMarketsRequest $request): JsonResource
    {
        $this->authorize('viewAny', Market::class);

        $markets = $this->repository->paginate($request->validated());

        return MarketResource::collection($markets);
    }

    /**
     * Export markets as CSV
     * Exports all markets based on user permissions and applied filters
     */
    public function export(RetrieveMarketsRequest $request): StreamedResponse
    {
        $this->authorize(Permission::MarketsExport->value);

        return $this->exportService->exportCsv($request->validated());
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
