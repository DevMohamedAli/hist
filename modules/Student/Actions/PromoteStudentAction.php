<?php

namespace Modules\Student\Actions;

use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use Modules\Student\Support\AcademicRegulation;

class PromoteStudentAction
{
    public function execute(Student $student, int $currentSemesterId): array
    {
        $enrollments = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->whereHas('studyGroup', fn ($query) => $query->where('academic_semester_id', $currentSemesterId))
            ->with('course')
            ->get();

        $failedCoursesCount = $enrollments
            ->filter(fn (CourseEnrollment $enrollment): bool => $enrollment->status === 'Failed')
            ->count();
        $semesterGpa = AcademicRegulation::weightedAverage($enrollments);
        $cgpa = app(CalculateCGPAAction::class)->execute($student);

        $oldSemesterLevel = (int) $student->current_semester_level;
        $newSemesterLevel = $oldSemesterLevel;

        if ($enrollments->isNotEmpty() && ($failedCoursesCount === 0 || AcademicRegulation::canCarryFailedCourses($failedCoursesCount, $cgpa))) {
            $newSemesterLevel++;
        }

        $student->update([
            'current_semester_level' => $newSemesterLevel,
        ]);

        return [
            'status' => $newSemesterLevel > $oldSemesterLevel ? 'منقول' : 'باق للإعادة',
            'gpa' => $semesterGpa,
            'cgpa' => $cgpa,
            'failed_count' => $failedCoursesCount,
            'new_level' => $newSemesterLevel,
        ];
    }
}
