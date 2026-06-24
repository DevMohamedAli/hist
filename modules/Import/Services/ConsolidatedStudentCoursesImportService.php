<?php

namespace Modules\Import\Services;

use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\StudyGroup;
use Modules\Import\Support\ConsolidatedStudentCoursesParser;
use Modules\Staff\Models\Instructor;
use Modules\Student\Actions\RecalculateSemesterSummary;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use Modules\Student\Support\AcademicRegulation;

class ConsolidatedStudentCoursesImportService
{
    public function __construct(
        private readonly ConsolidatedStudentCoursesParser $parser = new ConsolidatedStudentCoursesParser,
    ) {}

    public function dryRun(string $filePath): array
    {
        return $this->parser->parse($filePath, includeRows: false);
    }

    public function import(string $filePath): array
    {
        $parsed = $this->parser->parse($filePath, includeRows: true);
        $summary = [
            ...$parsed['summary'],
            'departments_upserted' => 0,
            'specializations_upserted' => 0,
            'courses_upserted' => 0,
            'semesters_upserted' => 0,
            'study_groups_upserted' => 0,
            'course_classes_upserted' => 0,
            'students_upserted' => 0,
            'enrollments_upserted' => 0,
            'grades_recorded' => 0,
        ];
        $warnings = $parsed['warnings'];
        $touchedSummaries = [];

        DB::disableQueryLog();

        DB::transaction(function () use ($parsed, &$summary, &$touchedSummaries): void {
            $instructor = $this->placeholderInstructor();
            $specializationCache = [];
            $courseCache = [];
            $semesterCache = [];
            $studyGroupCache = [];
            $courseClassCache = [];
            $studentCache = [];

            foreach ($parsed['reference_courses'] as $row) {
                $specialization = $this->specialization($row['department'], $row['specialization'], $row['semester_level'], $specializationCache);
                $course = $this->course($row['course_code'], $row['course_name'], $row['units'], $courseCache);
                $specialization->courses()->syncWithoutDetaching([
                    $course->id => ['semester_level' => max(1, (int) $row['semester_level'])],
                ]);
                $summary['specializations_upserted']++;
                $summary['courses_upserted']++;
            }

            foreach ($parsed['detail_rows'] as $row) {
                $specialization = $this->specialization($row['department'], $row['specialization'], $row['semester_level'], $specializationCache);
                $course = $this->course($row['course_code'], $row['course_name'], $row['units'], $courseCache);
                $specialization->courses()->syncWithoutDetaching([
                    $course->id => ['semester_level' => max(1, (int) $row['semester_level'])],
                ]);
                $semester = $this->semester($row, $semesterCache);
                $studyGroup = $this->studyGroup($specialization, $semester, $row, $studyGroupCache);
                $student = $this->student($row, $specialization, $studentCache);
                $courseClass = $this->courseClass($course, $semester, $studyGroup, $instructor, $courseClassCache);
                $semesterWork = (float) $row['semester_work'];
                $finalExam = (float) $row['final_exam'];
                $totalMark = AcademicRegulation::totalGrade($semesterWork, $finalExam);

                $enrollment = CourseEnrollment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'study_group_id' => $studyGroup->id,
                        'class_id' => $courseClass->id,
                        'course_id' => $course->id,
                    ],
                    [
                        'raw_semester_work' => $semesterWork,
                        'raw_final_exam' => $finalExam,
                        'total_mark' => $totalMark,
                        'grade_evaluation' => AcademicRegulation::evaluationLabel($totalMark),
                        'status' => AcademicRegulation::courseStatus($semesterWork, $finalExam),
                    ]
                );

                $summary['enrollments_upserted']++;
                $summary['grades_recorded']++;
                $touchedSummaries[$student->id][$semester->id] = true;
            }

            $summary['departments_upserted'] = Department::count();
            $summary['semesters_upserted'] = AcademicSemester::count();
            $summary['study_groups_upserted'] = StudyGroup::count();
            $summary['course_classes_upserted'] = CourseClass::count();
            $summary['students_upserted'] = Student::count();
        });

        $this->recalculateTouchedSummaries($touchedSummaries);

        return [
            'summary' => $summary,
            'warnings' => $warnings,
        ];
    }

    private function specialization(string $departmentName, string $specializationName, int $semesterLevel, array &$cache): Specialization
    {
        $key = $departmentName.'|'.$specializationName;

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $department = Department::firstOrCreate(
            ['name' => $departmentName],
            ['code' => $this->availableCode(Department::class, $departmentName, 'DEP')]
        );
        $specialization = Specialization::query()
            ->where('department_id', $department->id)
            ->where('name', $specializationName)
            ->first();

        if (! $specialization) {
            $specialization = Specialization::create([
                'department_id' => $department->id,
                'name' => $specializationName,
                'code' => $this->availableCode(Specialization::class, $specializationName, 'SPC'),
                'semesters_count' => max(6, $semesterLevel),
            ]);
        } elseif ((int) $specialization->semesters_count < $semesterLevel) {
            $specialization->update(['semesters_count' => $semesterLevel]);
        }

        return $cache[$key] = $specialization;
    }

    private function course(string $code, string $name, int $units, array &$cache): Course
    {
        if (isset($cache[$code])) {
            return $cache[$code];
        }

        return $cache[$code] = Course::updateOrCreate(
            ['code' => $code],
            ['name' => $name, 'units' => max(1, $units), 'has_practical' => false]
        );
    }

    private function semester(array $row, array &$cache): AcademicSemester
    {
        $code = $row['semester_code'];

        if (isset($cache[$code])) {
            return $cache[$code];
        }

        return $cache[$code] = AcademicSemester::firstOrCreate(
            ['code' => $code],
            ['season' => $row['season'], 'year' => $row['year']]
        );
    }

    private function studyGroup(Specialization $specialization, AcademicSemester $semester, array $row, array &$cache): StudyGroup
    {
        $key = $specialization->id.'|'.$semester->id.'|'.$row['semester_level'];

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        return $cache[$key] = StudyGroup::firstOrCreate(
            [
                'specialization_id' => $specialization->id,
                'academic_semester_id' => $semester->id,
                'semester_level' => max(1, (int) $row['semester_level']),
                'group_name' => $specialization->name,
            ],
            ['capacity' => 500]
        );
    }

    private function courseClass(Course $course, AcademicSemester $semester, StudyGroup $studyGroup, Instructor $instructor, array &$cache): CourseClass
    {
        $key = $course->id.'|'.$semester->id.'|'.$studyGroup->id;

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        return $cache[$key] = CourseClass::firstOrCreate(
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
    }

    private function student(array $row, Specialization $specialization, array &$cache): Student
    {
        $registration = $row['registration_number'] !== '' ? $row['registration_number'] : $row['student_key'];

        if (isset($cache[$registration])) {
            return $cache[$registration];
        }

        $student = Student::firstOrNew(['registration_number' => $registration]);
        $student->fill([
            'full_name' => $row['student_name'],
            'current_specialization_id' => $specialization->id,
            'current_semester_level' => max((int) $student->current_semester_level, (int) $row['semester_level']),
            'status' => $student->status ?: 'Active',
        ]);

        if (! $student->exists) {
            $student->fill([
                'national_id' => $this->placeholderNationalId($registration),
                'gender' => 'Male',
                'nationality' => 'ليبي',
                'birth_date' => '1900-01-01',
                'admission_date' => now()->toDateString(),
            ]);
        }

        $student->save();

        return $cache[$registration] = $student;
    }

    private function placeholderInstructor(): Instructor
    {
        return Instructor::firstOrCreate(
            ['email' => 'consolidated-import-placeholder@example.local'],
            [
                'name' => 'Consolidated Import Placeholder',
                'employee_id' => 'CONSOLIDATED-IMPORT',
                'academic_rank' => 'Imported',
                'status' => 'Active',
            ]
        );
    }

    private function availableCode(string $modelClass, string $value, string $prefix): string
    {
        $base = $prefix.substr(strtoupper(hash('crc32b', $value)), 0, 8);

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

    private function placeholderNationalId(string $registrationNumber): string
    {
        $digits = preg_replace('/\D+/', '', $registrationNumber) ?: (string) crc32($registrationNumber);
        $base = '9'.substr(str_pad($digits, 11, '0', STR_PAD_LEFT), -11);

        if (! Student::query()->where('national_id', $base)->exists()) {
            return $base;
        }

        for ($index = 1; $index <= 999; $index++) {
            $candidate = '8'.substr(sprintf('%08u', crc32($registrationNumber.$index)), -8).str_pad((string) $index, 3, '0', STR_PAD_LEFT);

            if (! Student::query()->where('national_id', $candidate)->exists()) {
                return $candidate;
            }
        }

        return '8'.substr(str_replace('.', '', (string) microtime(true)), -11);
    }

    /**
     * Recalculate each touched student/semester summary once after the bulk import.
     *
     * @param array<int, array<int, bool>> $touchedSummaries
     */
    private function recalculateTouchedSummaries(array $touchedSummaries): void
    {
        $recalculate = app(RecalculateSemesterSummary::class);

        foreach ($touchedSummaries as $studentId => $semesters) {
            foreach (array_keys($semesters) as $semesterId) {
                $recalculate->execute((int) $studentId, (int) $semesterId);
            }
        }
    }
}
