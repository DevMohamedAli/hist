<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Website\Models\WebsiteSetting;

class WebsiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'site_name' => 'Higher Institute',
            'site_description' => 'Official public website for institute news, admissions, and services.',
            'contact_email' => null,
        ] as $key => $value) {
            WebsiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
