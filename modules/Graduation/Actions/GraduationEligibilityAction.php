<?php

namespace Modules\Graduation\Actions;

use Illuminate\Support\Collection;
use Modules\Student\Actions\CalculateCGPAAction;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use Modules\Student\Support\AcademicRegulation;

class GraduationEligibilityAction
{
    public function execute(Student $student): array
    {
        $student->loadMissing('currentSpecialization.department');
        $specialization = $student->currentSpecialization;

        if (! $specialization) {
            return [
                'eligible' => false,
                'cgpa' => 0.0,
                'total_units' => 0,
                'required_courses' => [],
                'passed_courses' => [],
                'missing_courses' => [],
                'failed_courses' => [],
                'reasons' => ['لا يوجد تخصص حالي مرتبط بالطالب.'],
            ];
        }

        $requiredCourses = $specialization->courses()
            ->orderBy('course_specialization.semester_level')
            ->orderBy('courses.name')
            ->get();

        $enrollments = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['Passed', 'Failed'])
            ->whereHas('course')
            ->with(['course', 'studyGroup.academicSemester', 'class.semester'])
            ->get();

        $passedByCourse = $enrollments
            ->where('status', 'Passed')
            ->groupBy('course_id')
            ->map(fn (Collection $rows) => $rows->sortByDesc('total_mark')->first());

        $failedByCourse = $enrollments
            ->where('status', 'Failed')
            ->groupBy('course_id')
            ->map(fn (Collection $rows) => $rows->sortByDesc('total_mark')->first());

        $passedCourses = [];
        $missingCourses = [];
        $failedCourses = [];
        $totalUnits = 0;

        foreach ($requiredCourses as $course) {
            $row = $this->courseRow($course);

            if ($passedByCourse->has($course->id)) {
                $enrollment = $passedByCourse->get($course->id);
                $passedCourses[] = [
                    ...$row,
                    'total_mark' => (float) $enrollment->total_mark,
                    'grade_evaluation' => $enrollment->grade_evaluation,
                ];
                $totalUnits += (int) $course->units;

                continue;
            }

            if ($failedByCourse->has($course->id)) {
                $enrollment = $failedByCourse->get($course->id);
                $failedCourses[] = [
                    ...$row,
                    'total_mark' => (float) $enrollment->total_mark,
                    'grade_evaluation' => $enrollment->grade_evaluation,
                ];
            }

            $missingCourses[] = $row;
        }

        $cgpa = app(CalculateCGPAAction::class)->execute($student);
        $reasons = [];

        if ($requiredCourses->isEmpty()) {
            $reasons[] = 'لا توجد خطة دراسية مرتبطة بتخصص الطالب.';
        }

        if (count($missingCourses) > 0) {
            $reasons[] = 'لم يستكمل الطالب جميع مقررات الخطة الدراسية.';
        }

        if ($cgpa < AcademicRegulation::MIN_CUMULATIVE_AVERAGE) {
            $reasons[] = 'المعدل التراكمي أقل من الحد الأدنى 55%.';
        }

        return [
            'eligible' => $requiredCourses->isNotEmpty() && count($missingCourses) === 0 && $cgpa >= AcademicRegulation::MIN_CUMULATIVE_AVERAGE,
            'cgpa' => $cgpa,
            'total_units' => $totalUnits,
            'required_courses' => $requiredCourses->map(fn ($course) => $this->courseRow($course))->values()->all(),
            'passed_courses' => $passedCourses,
            'missing_courses' => $missingCourses,
            'failed_courses' => $failedCourses,
            'reasons' => $reasons,
        ];
    }

    private function courseRow($course): array
    {
        return [
            'id' => $course->id,
            'code' => $course->code,
            'name' => $course->name,
            'units' => (int) $course->units,
            'semester_level' => (int) ($course->pivot?->semester_level ?? 0),
        ];
    }
}
