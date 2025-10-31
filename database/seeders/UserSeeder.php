<?php

namespace Database\Seeders;

use App\Models\Market;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->command->info('Creating admin user...');
        $this->command->newLine();

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $admin->assignRole('Super Admin');

        $marketIds = Market::get()->take(10)->pluck('id')->toArray();
        // Skip attaching to markets, admin has access to all markets
        $admin->setSetting('selected_markets', $marketIds);
        $admin->setSetting('default_range_filter', [
            'start' => now()->subDays(30)->format('Y-m-d'),
            'end' => now()->format('Y-m-d'),
        ]);

        // admin users has access to all markets

        $this->command->info('✓ Created admin user');
        $this->command->info('Admin email: admin@example.com');
        $this->command->info('Admin password: password');
        $this->command->newLine();

        $this->command->info('Creating market user...');
        $this->command->newLine();

        // Create market users
        $marketUser = User::factory()->create([
            'name' => 'Market User',
            'email' => 'market@example.com',
        ]);

        $marketUser->assignRole('Market User');

        $marketIds = Market::get()->take(10)->pluck('id')->toArray();
        $marketUser->markets()->attach($marketIds);
        $marketUser->setSetting('selected_markets', collect($marketIds)->take(5)->toArray());
        $marketUser->setSetting('default_range_filter', [
            'start' => now()->subDays(30)->format('Y-m-d'),
            'end' => now()->format('Y-m-d'),
        ]);

        $this->command->info('✓ Created market user');
        $this->command->info('Market user is assigned to ' . count($marketIds) . ' random markets');
        $this->command->info('Market email: market@example.com');
        $this->command->info('Market password: password');
        $this->command->newLine();

        $this->command->info('Creating dummy users...');
        $this->command->newLine();

        // Create 10 dummy users
        $dummyUsers = User::factory()->count(10)->create();

        foreach ($dummyUsers as $dummyUser) {
            $dummyUser->assignRole('Market User');
            $randomMarketIds = Market::get()->random(10)->pluck('id')->toArray();
            $dummyUser->markets()->attach($randomMarketIds);
            $dummyUser->setSetting('selected_markets', collect($randomMarketIds)->take(5)->toArray());
            $dummyUser->setSetting('default_range_filter', [
                'start' => now()->subDays(30)->format('Y-m-d'),
                'end' => now()->format('Y-m-d'),
            ]);
        }

        $this->command->info('✓ Created 10 dummy users');
        $this->command->info('Dummy users are assigned to ' . count($randomMarketIds) . ' random markets');
        $this->command->newLine();
    }
}
