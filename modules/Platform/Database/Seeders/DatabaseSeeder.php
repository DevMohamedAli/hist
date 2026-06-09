<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Database\Seeders\UserSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            settingsTableSeeder::class,
            PortalSampleUsersSeeder::class,
        ]);
    }
}
