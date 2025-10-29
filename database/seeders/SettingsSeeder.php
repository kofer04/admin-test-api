<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding settings...');

        $settings = [
            [
                'key' => 'conversion_funnel_step_1',
                'value' => '2',
                'type' => 'integer',
                'description' => 'Job Type & Zip Completed'
            ],
            [
                'key' => 'conversion_funnel_step_2',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Appointment Date/Time Selected'
            ],
            [
                'key' => 'conversion_funnel_step_3',
                'value' => '7',
                'type' => 'integer',
                'description' => 'New / Repeat Customer Selection'
            ],
            [
                'key' => 'conversion_funnel_step_4',
                'value' => '623',
                'type' => 'integer',
                'description' => 'Terms of Service Loaded'
            ],
            [
                'key' => 'conversion_funnel_step_5',
                'value' => '8',
                'type' => 'integer',
                'description' => 'Appointment Confirmed'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✓ Settings seeded successfully');
        $this->command->newLine();
    }
}

