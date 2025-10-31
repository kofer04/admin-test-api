<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Permission;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed all permissions that might be used in tests
        $this->seedTestPermissions();
    }

    /**
     * Seed all permissions defined in the Permission enum
     */
    protected function seedTestPermissions(): void
    {
        $permissions = [
            'read-report:job-bookings',
            'export-report:job-bookings',
            'read-report:conversion-funnel',
            'export-report:conversion-funnel',
            'markets:read',
            'markets:write',
            'markets:export',
            'users:read',
            'users:write',
            'users:export',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
