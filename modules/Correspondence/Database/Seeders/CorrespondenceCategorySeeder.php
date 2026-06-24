<?php

namespace Modules\Correspondence\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Correspondence\Models\CorrespondenceCategory;

class CorrespondenceCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Official Letter', 'code' => 'OFFICIAL', 'is_student_available' => false],
            ['name' => 'Student Record Correction', 'code' => 'STUDENT_RECORD_CORRECTION', 'is_student_available' => true],
            ['name' => 'Document Request', 'code' => 'DOCUMENT_REQUEST', 'is_student_available' => true],
            ['name' => 'Official Complaint', 'code' => 'OFFICIAL_COMPLAINT', 'is_student_available' => true, 'requires_approval' => true],
        ] as $category) {
            CorrespondenceCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'is_student_available' => $category['is_student_available'],
                    'requires_approval' => $category['requires_approval'] ?? false,
                    'is_active' => true,
                ],
            );
        }
    }
}
