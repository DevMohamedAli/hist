<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Platform\Models\Setting;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'admin_code' => '2',
            'institute_code' => '10',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
