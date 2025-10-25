<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $startTime = microtime(true);
        $this->command->info('Starting database seeding...');
        $this->command->newLine();

        // Disable foreign key checks for better performance
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // Seed CSV data in dependency order
            $this->call([
                MarketsSeeder::class,        // First - no dependencies
                EventNamesSeeder::class,     // Second - no dependencies
                LogServiceTitanJobsSeeder::class, // Third - depends on markets
                LogEventsSeeder::class,      // Fourth - depends on markets and event_names
            ]);

            $this->command->newLine();

            // Create test user
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'admin',
            ]);

            $this->command->info('✓ Created test admin user');

        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $duration = round(microtime(true) - $startTime, 2);
        $this->command->newLine();
        $this->command->info("Database seeding completed in {$duration}s");
    }
}
