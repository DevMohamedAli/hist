<?php

namespace Modules\Import\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\StudyGroup;
use Modules\Import\Models\ImportJob;
use Modules\Import\Support\GradeWorkbookParser;
use Modules\Staff\Models\Instructor;
use Modules\Student\Actions\RecordCourseGrades;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;

class GradeWorkbookImportService
{
    public function __construct(
        private readonly GradeWorkbookParser $parser = new GradeWorkbookParser,
    ) {}

    public function preview(string $filePath): array
    {
        return $this->parser->parse($filePath);
    }

    public function import(ImportJob $job): array
    {
        $parsed = $this->parser->parse(Storage::path($job->file_path));

        if (! $this->hasUsableMetadata($parsed)) {
            throw new \InvalidArgumentException('تعذر تنفيذ الاستيراد لأن بيانات القسم أو الشعبة أو الفصل غير مكتملة في كل أوراق الملف.');
        }

        $summary = [
            ...$parsed['summary'],
            'departments' => 0,
            'specializations' => 0,
            'semesters' => 0,
            'study_groups' => 0,
            'course_classes' => 0,
            'enrollments' => 0,
            'imported_grades' => 0,
        ];
        $warnings = $parsed['warnings'];
        $total = max(1, (int) $parsed['summary']['grade_cells']);
        $processed = 0;

        DB::transaction(function () use ($job, $parsed, &$summary, &$warnings, &$processed, $total): void {
            $instructor = $this->placeholderInstructor();

            foreach ($parsed['sheets'] as $sheet) {
                $metadata = $sheet['metadata'];
                $department = $this->firstOrCreateDepartment((string) $metadata['department']);
                $specialization = $this->firstOrCreateSpecialization(
                    $department,
                    (string) $metadata['specialization'],
                    max(1, (int) $metadata['semester_level']),
                );
                $semester = AcademicSemester::firstOrCreate(
                    ['code' => $this->semesterCode($metadata)],
                    ['season' => $metadata['season'], 'year' => $metadata['year']]
                );
                $studyGroup = StudyGroup::firstOrCreate(
                    [
                        'specialization_id' => $specialization->id,
                        'academic_semester_id' => $semester->id,
                        'semester_level' => max(1, (int) $metadata['semester_level']),
                        'group_name' => $metadata['specialization'],
                    ],
                    ['capacity' => max(50, count($sheet['students']))]
                );

                $summary['departments']++;
                $summary['specializations']++;
                $summary['semesters']++;
                $summary['study_groups']++;

                $courses = $this->upsertCourses($sheet['courses'], $specialization, max(1, (int) $metadata['semester_level']));

                foreach ($sheet['students'] as $studentRow) {
                    $student = $this->upsertStudent($studentRow, $specialization, max(1, (int) $metadata['semester_level']));

                    foreach ($studentRow['grades'] as $grade) {
                        $course = $courses[$grade['course_code']] ?? null;

                        if (! $course) {
                            continue;
                        }

                        $courseClass = CourseClass::firstOrCreate(
                            [
                                'course_id' => $course->id,
                                'semester_id' => $semester->id,
                                'study_group_id' => $studyGroup->id,
                            ],
                            [
                                'instructor_id' => $instructor->id,
                                'group_name' => $studyGroup->group_name,
                            ]
                        );
                        $enrollment = CourseEnrollment::firstOrCreate(
                            [
                                'student_id' => $student->id,
                                'study_group_id' => $studyGroup->id,
                                'class_id' => $courseClass->id,
                                'course_id' => $course->id,
                            ],
                            ['status' => 'Pending']
                        );

                        app(RecordCourseGrades::class)->execute(
                            $enrollment->id,
                            (float) $grade['semester_work'],
                            (float) $grade['final_exam'],
                        );

                        $summary['course_classes']++;
                        $summary['enrollments']++;
                        $summary['imported_grades']++;
                        $processed++;

                        app(ImportJobService::class)->updateProgress($job->id, $processed, $total);
                    }
                }
            }
        });

        return [
            'summary' => $summary,
            'warnings' => $warnings,
        ];
    }

    private function upsertCourses(array $rows, Specialization $specialization, int $semesterLevel): array
    {
        $courses = [];

        foreach ($rows as $row) {
            $course = Course::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'units' => max(1, (int) $row['units']),
                    'has_practical' => false,
                ]
            );

            $specialization->courses()->syncWithoutDetaching([
                $course->id => ['semester_level' => $semesterLevel],
            ]);

            $courses[$row['code']] = $course;
        }

        return $courses;
    }

    private function firstOrCreateDepartment(string $name): Department
    {
        $department = Department::query()
            ->where('name', $name)
            ->orWhere('code', $this->codeFor($name, 'DEP'))
            ->first();

        if ($department) {
            return $department;
        }

        return Department::create([
            'name' => $name,
            'code' => $this->availableCode(Department::class, $name, 'DEP'),
        ]);
    }

    private function firstOrCreateSpecialization(Department $department, string $name, int $semesterLevel): Specialization
    {
        $specialization = Specialization::query()
            ->where('department_id', $department->id)
            ->where('name', $name)
            ->first();

        if ($specialization) {
            if ((int) $specialization->semesters_count < $semesterLevel) {
                $specialization->update(['semesters_count' => $semesterLevel]);
            }

            return $specialization;
        }

        return Specialization::create([
            'department_id' => $department->id,
            'name' => $name,
            'code' => $this->availableCode(Specialization::class, $name, 'SPC'),
            'description' => null,
            'semesters_count' => max(6, $semesterLevel),
        ]);
    }

    private function upsertStudent(array $row, Specialization $specialization, int $semesterLevel): Student
    {
        $registrationNumber = trim((string) $row['registration_number']);
        $student = Student::firstOrNew(['registration_number' => $registrationNumber]);

        $student->fill([
            'full_name' => $row['name'],
            'current_specialization_id' => $specialization->id,
            'current_semester_level' => $semesterLevel,
            'status' => $student->status ?: 'Active',
        ]);

        if (! $student->exists) {
            $student->fill([
                'national_id' => $this->placeholderNationalId($registrationNumber),
                'gender' => 'Male',
                'nationality' => 'ليبي',
                'birth_date' => '1900-01-01',
                'admission_date' => now()->toDateString(),
            ]);
        }

        $student->save();

        return $student;
    }

    private function placeholderInstructor(): Instructor
    {
        return Instructor::firstOrCreate(
            ['email' => 'imported-grades-placeholder@example.local'],
            [
                'name' => 'Imported Grades Placeholder',
                'employee_id' => 'IMPORT-GRADES',
                'academic_rank' => 'Imported',
                'status' => 'Active',
            ],
        );
    }

    private function semesterCode(array $metadata): string
    {
        return strtoupper($metadata['season']).'-'.$metadata['year'];
    }

    private function codeFor(string $value, string $prefix): string
    {
        return $prefix.substr(strtoupper(hash('crc32b', $value)), 0, 8);
    }

    private function availableCode(string $modelClass, string $value, string $prefix): string
    {
        $base = $this->codeFor($value, $prefix);

        if (! $modelClass::query()->where('code', $base)->exists()) {
            return $base;
        }

        for ($index = 1; $index <= 99; $index++) {
            $candidate = substr($base, 0, 17).str_pad((string) $index, 3, '0', STR_PAD_LEFT);

            if (! $modelClass::query()->where('code', $candidate)->exists()) {
                return $candidate;
            }
        }

        return substr($base, 0, 12).substr(strtoupper(hash('crc32b', $value.microtime(true))), 0, 8);
    }

    private function hasUsableMetadata(array $parsed): bool
    {
        foreach ($parsed['sheets'] as $sheet) {
            if (
                ($sheet['metadata']['metadata_complete'] ?? false)
                || ! str_starts_with((string) $sheet['metadata']['department'], 'Imported ')
                || ! str_starts_with((string) $sheet['metadata']['specialization'], 'Imported ')
            ) {
                return true;
            }
        }

        return false;
    }

    private function placeholderNationalId(string $registrationNumber): string
    {
        $digits = preg_replace('/\D+/', '', $registrationNumber) ?: '0';
        $base = '9'.substr(str_pad($digits, 11, '0', STR_PAD_LEFT), -11);

        if (! Student::query()->where('national_id', $base)->exists()) {
            return $base;
        }

        $hash = sprintf('%011u', crc32($registrationNumber));
        $candidate = '8'.substr($hash, -11);

        if (! Student::query()->where('national_id', $candidate)->exists()) {
            return $candidate;
        }

        for ($index = 1; $index <= 999; $index++) {
            $candidate = '8'.substr(sprintf('%08u', crc32($registrationNumber.$index)), -8).str_pad((string) $index, 3, '0', STR_PAD_LEFT);

            if (! Student::query()->where('national_id', $candidate)->exists()) {
                return $candidate;
            }
        }

        return '8'.substr(str_replace('.', '', (string) microtime(true)), -11);
    }
}
