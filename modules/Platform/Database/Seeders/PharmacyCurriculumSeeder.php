<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\Department;

class PharmacyCurriculumSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure department and specialization exist
        $department = Department::firstOrCreate(['name' => 'المهن الطبية']);
        $specialization = Specialization::firstOrCreate(
            ['department_id' => $department->id, 'name' => 'الصيدلة'],
            ['code' => 'PHARM', 'semesters_count' => 6]
        );

        // 2. Fetch the hardcoded curriculum data
        $semesterCourses = $this->getCurriculumData();

        // 3. Create or update courses and attach to specialization with semester level
        $allCourses = [];
        foreach ($semesterCourses as $semester => $courses) {
            foreach ($courses as $name => $units) {
                $course = Course::firstOrCreate(
                    ['name' => $name],
                    [
                        'code' => $this->generateCourseCode($name),
                        'units' => $units,
                        'has_practical' => str_contains($name, 'عملي') || str_contains($name, 'I'),
                    ]
                );

                $specialization->courses()->syncWithoutDetaching([
                    $course->id => ['semester_level' => $semester]
                ]);

                $allCourses[$name] = $course;
            }
        }

        $this->command->info('Courses created/updated: ' . count($allCourses));

        // 4. Define prerequisites (logical flow)
        $prerequisiteMap = $this->prerequisiteMapping();

        foreach ($prerequisiteMap as $courseName => $prereqNames) {
            $course = $allCourses[$courseName] ?? Course::where('name', $courseName)->first();

            if (!$course) {
                $this->command->warn("Course '{$courseName}' not found for prerequisites");
                continue;
            }

            $prereqIds = [];
            foreach ($prereqNames as $prereqName) {
                $prereq = $allCourses[$prereqName] ?? Course::where('name', $prereqName)->first();
                if ($prereq) {
                    $prereqIds[] = $prereq->id;
                } else {
                    $this->command->warn("Prerequisite '{$prereqName}' not found for '{$courseName}'");
                }
            }

            if (!empty($prereqIds)) {
                $course->prerequisites()->syncWithoutDetaching($prereqIds);
            }
        }

        $this->command->info('Prerequisite links applied successfully.');
    }

    /**
     * Define the curriculum structure manually.
     * Format: [ SemesterNumber => [ 'Course Name' => Units ] ]
     */
    private function getCurriculumData(): array
    {
        return [
            1 => [
                'الأحياء عامة' => 3,
                'الكيمياء العامة' => 3,
                'اللغة الإنجليزية I' => 3,
                'علم التشريح ووظائف الأعضاء' => 3,
                // TODO: Add missing semester 1 courses
            ],
            2 => [
                'علم أحياء دقيقة العامة' => 3,
                'اللغة الإنجليزية II' => 3,
                'علم الصيدلانيات I' => 3,
                'الكيمياء العضوية I' => 3,
                'الكيمياء التحليلية I' => 3,
                // TODO: Verify units and add missing semester 2 courses
            ],
            3 => [
                'علم الادوية I' => 3,
                'الكيمياء العضوية II' => 3,
                'علم الصيدلانيات II' => 3,
                'الكيمياء الطبيية I' => 3,
                'الكيمياء التحليلية II' => 3,
                'علم الامراض' => 3,
                'كيمياء العقاقير' => 3,
                'الأحياء الدقيقة عامة' => 3,
                'عقاقير طبية I' => 3
            ],
            4 => [
                'علم الادوية II' => 3,
                'كيمياء حيوية' => 3,
                'علم السموم' => 3,
                'تحليل آلي' => 3,
                'الكيمياء الطبيية II' => 3,
                'تقنية صيدلانية' => 3,
                'عقاقير تطبيقية' => 3,
                'تشريح ووظائف أعضاء' => 3,
                'كيمياء عضوية 2' => 3,
            ],
            5 => [
                'علاجيات' => 3,
                'صيدلة تطبيقية' => 3,
                'كيمياء حيوية سريرية' => 3,
                'صيدلة مستشفيات' => 3,
                'صيدلة صناعية' => 3,
                'علم الصيدلانياتية II' => 3,
                'تحليل الي' => 3,
                'كيمياء طبية II' => 3,
                'علم الأمراض' => 3,
                'علم الادوية2' => 3,
            ],
            6 => [
                'مشروع التخرج' => 4,
                // TODO: Add all semester 6 courses
            ]
        ];
    }

    private function generateCourseCode(string $name): string
    {
        $clean = preg_replace('/[^\p{Arabic}]/u', '', $name);
        $translit = function_exists('arabic_to_latin') ? arabic_to_latin($clean) : 'CRS';
        $code = strtoupper(substr($translit, 0, 8));

        if (empty($code)) $code = 'CRS';

        $original = $code;
        $counter = 1;

        while (Course::where('code', $code)->exists()) {
            $code = $original . $counter;
            $counter++;
        }

        return $code;
    }

    private function prerequisiteMapping(): array
    {
        return [
            // Semester 2 dependencies (prerequisites from semester 1)
            'علم أحياء دقيقة العامة' => ['الأحياء عامة'],
            'اللغة الإنجليزية II' => ['اللغة الإنجليزية I'],
            'علم الصيدلانيات I' => ['الكيمياء العامة'],
            'الكيمياء العضوية I' => ['الكيمياء العامة'],
            'الكيمياء التحليلية I' => ['الكيمياء العامة'],

            // Semester 3
            'علم الادوية I' => ['علم الصيدلانيات I', 'الكيمياء العضوية I'],
            'الكيمياء العضوية II' => ['الكيمياء العضوية I'],
            'علم الصيدلانيات II' => ['علم الصيدلانيات I'],
            'الكيمياء الطبيية I' => ['الكيمياء العضوية I'],
            'الكيمياء التحليلية II' => ['الكيمياء التحليلية I'],
            'علم الامراض' => ['علم التشريح ووظائف الأعضاء'],
            'كيمياء العقاقير' => ['الكيمياء العضوية II'],
            'الأحياء الدقيقة عامة' => ['علم أحياء دقيقة العامة'],

            // Semester 4
            'علم الادوية II' => ['علم الادوية I'],
            'كيمياء حيوية' => ['الكيمياء العضوية I'],
            'علم السموم' => ['علم الادوية I'],
            'تحليل آلي' => ['الكيمياء التحليلية II'],
            'الكيمياء الطبيية II' => ['الكيمياء الطبيية I'],
            'تقنية صيدلانية' => ['علم الصيدلانيات II'],
            'عقاقير تطبيقية' => ['عقاقير طبية I', 'علم الادوية I'],
            'تشريح ووظائف أعضاء' => ['علم التشريح ووظائف الأعضاء'],
            'كيمياء عضوية 2' => ['الكيمياء العضوية II'],

            // Semester 5
            'علاجيات' => ['علم الادوية II'],
            'صيدلة تطبيقية' => ['تقنية صيدلانية'],
            'كيمياء حيوية سريرية' => ['كيمياء حيوية'],
            'صيدلة مستشفيات' => ['عقاقير تطبيقية'],
            'صيدلة صناعية' => ['تقنية صيدلانية'],
            'علم الصيدلانياتية II' => ['علم الصيدلانيات II'],
            'تحليل الي' => ['تحليل آلي'],
            'كيمياء طبية II' => ['الكيمياء الطبيية II'],
            'علم الأمراض' => ['علم الامراض'],
            'علم الادوية2' => ['علم الادوية II'],
        ];
    }
}
