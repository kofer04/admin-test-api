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
                MarketsSeeder::class,
                EventNamesSeeder::class,
                LogServiceTitanJobsSeeder::class,
                LogEventsSeeder::class,
                RolesAndPermissionsSeeder::class,
                UserSeeder::class,
                SettingsSeeder::class,
            ]);

        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $duration = round(microtime(true) - $startTime, 2);
        $this->command->newLine();
        $this->command->info("Database seeding completed in {$duration}s");
    }
}
