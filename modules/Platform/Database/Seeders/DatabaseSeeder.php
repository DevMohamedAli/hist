<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Correspondence\Database\Seeders\CorrespondenceCategorySeeder;
use Modules\Website\Database\Seeders\WebsiteSettingSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            settingsTableSeeder::class,
            PortalSampleUsersSeeder::class,
            PharmacyCurriculumSeeder::class,
            WebsiteSettingSeeder::class,
            CorrespondenceCategorySeeder::class,
        ]);
    }
}
