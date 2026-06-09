<?php

namespace Modules\Student\Actions;

use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use Modules\Student\Models\StudentSemesterSummary;
use Modules\Student\Support\AcademicRegulation;

class RecalculateSemesterSummary
{
    public function execute(int $studentId, int $semesterId): StudentSemesterSummary
    {
        $enrollments = CourseEnrollment::query()
            ->where('student_id', $studentId)
            ->whereHas('studyGroup', fn ($query) => $query->where('academic_semester_id', $semesterId))
            ->with('course')
            ->get();

        $totalUnits = $enrollments->sum(fn (CourseEnrollment $enrollment): int => (int) ($enrollment->course?->units ?? 0));
        $failedCoursesCount = $enrollments
            ->filter(fn (CourseEnrollment $enrollment): bool => $enrollment->status === 'Failed')
            ->count();

        $student = Student::findOrFail($studentId);

        return StudentSemesterSummary::updateOrCreate(
            ['student_id' => $studentId, 'semester_id' => $semesterId],
            [
                'semester_gpa' => AcademicRegulation::weightedAverage($enrollments),
                'cumulative_gpa' => app(CalculateCGPAAction::class)->execute($student),
                'total_registered_units' => $totalUnits,
                'carried_courses_count' => $failedCoursesCount,
            ]
        );
    }
}
