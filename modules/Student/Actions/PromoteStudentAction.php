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
            ->with(['course', 'studyGroup'])
            ->get();

        $oldSemesterLevel = (int) $student->current_semester_level;

        if ($enrollments->isEmpty()) {
            return [
                'status' => 'blocked',
                'promoted' => false,
                'reasons' => ['No enrollments found for this semester.'],
                'gpa' => 0.0,
                'cgpa' => app(CalculateCGPAAction::class)->execute($student),
                'failed_count' => 0,
                'pending_count' => 0,
                'new_level' => $oldSemesterLevel,
            ];
        }

        $semesterGpa = AcademicRegulation::weightedAverage($enrollments);
        $cgpa = app(CalculateCGPAAction::class)->execute($student);
        $pendingCoursesCount = $enrollments
            ->filter(fn (CourseEnrollment $enrollment): bool => $enrollment->status === 'Pending')
            ->count();

        if ($pendingCoursesCount > 0) {
            return [
                'status' => 'blocked',
                'promoted' => false,
                'reasons' => ["Promotion is blocked until {$pendingCoursesCount} pending grade(s) are completed."],
                'gpa' => $semesterGpa,
                'cgpa' => $cgpa,
                'failed_count' => 0,
                'pending_count' => $pendingCoursesCount,
                'new_level' => $oldSemesterLevel,
            ];
        }

        app(RecalculateSemesterSummary::class)->execute($student->id, $currentSemesterId);

        $failedCoursesCount = $enrollments
            ->filter(fn (CourseEnrollment $enrollment): bool => $enrollment->status === 'Failed')
            ->count();
        $completedSemesterLevel = (int) $enrollments
            ->max(fn (CourseEnrollment $enrollment): int => (int) ($enrollment->studyGroup?->semester_level ?? 0));
        $targetSemesterLevel = $completedSemesterLevel > 0 ? $completedSemesterLevel + 1 : $oldSemesterLevel + 1;
        $newSemesterLevel = $oldSemesterLevel;
        $reasons = [];

        if ($oldSemesterLevel >= $targetSemesterLevel) {
            $reasons[] = 'Student has already been promoted for this semester.';
        } elseif ($failedCoursesCount === 0 || AcademicRegulation::canCarryFailedCourses($failedCoursesCount, $cgpa)) {
            $newSemesterLevel = $targetSemesterLevel;
        } else {
            $reasons[] = 'Failed courses or cumulative average do not meet carry/promotion rules.';
        }

        if ($newSemesterLevel !== $oldSemesterLevel) {
            $student->update([
                'current_semester_level' => $newSemesterLevel,
            ]);
        }

        return [
            'status' => $newSemesterLevel > $oldSemesterLevel ? 'promoted' : 'blocked',
            'promoted' => $newSemesterLevel > $oldSemesterLevel,
            'reasons' => $reasons,
            'gpa' => $semesterGpa,
            'cgpa' => $cgpa,
            'failed_count' => $failedCoursesCount,
            'pending_count' => $pendingCoursesCount,
            'new_level' => $newSemesterLevel,
        ];
    }
}
