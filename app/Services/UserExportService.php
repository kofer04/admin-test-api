<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Services\Reports\Shared\CsvExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserExportService
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly CsvExportService $csvExporter,
    ) {}

    /**
     * Export users to CSV
     * Exports all users based on applied filters
     */
    public function exportCsv(array $params = []): StreamedResponse
    {
        // Get all users (with filters applied from repository)
        $users = $this->repository->query($params)->get();

        // Transform to CSV rows
        $rows = $this->transformToCsvRows($users);

        // Generate CSV file
        return $this->csvExporter->export(
            filename: 'users-' . now()->format('Y-m-d') . '.csv',
            headers: ['ID', 'Name', 'Email', 'Role', 'Markets Count', 'Created At'],
            rows: $rows
        );
    }

    /**
     * Transform users to CSV rows
     */
    private function transformToCsvRows($users): \Generator
    {
        foreach ($users as $user) {
            $roleName = $user->roles->first()?->name ?? 'No Role';
            
            yield [
                $user->id,
                $user->name,
                $user->email,
                $roleName,
                $user->markets()->count(),
                $user->created_at?->format('Y-m-d H:i:s') ?? '',
            ];
        }
    }
}

