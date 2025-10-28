<?php

namespace Database\Seeders;

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

        $this->command->info('✓ Created admin user');
        $this->command->info('Admin email: admin@example.com');
        $this->command->info('Admin password: password');
        $this->command->newLine();

        $this->command->info('Creating market user...');
        $this->command->newLine();

        // Create market user
        $marketUser = User::factory()->create([
            'name' => 'Market User',
            'email' => 'market@example.com',
        ]);

        $marketUser->assignRole('Market User');

        $this->command->info('✓ Created market user');
        $this->command->info('Market email: market@example.com');
        $this->command->info('Market password: password');
        $this->command->newLine();
    }
}
