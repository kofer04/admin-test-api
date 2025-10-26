<?php

namespace App\Console\Commands;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InitRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed or update roles and permissions';


    public function handle(): int
    {
        $this->info('🔄 Initializing roles and permissions...');

        Artisan::call('db:seed', [
            '--class' => RolesAndPermissionsSeeder::class,
        ]);

        $this->info('✅ Roles and permissions updated successfully.');

        return self::SUCCESS;
    }
}
