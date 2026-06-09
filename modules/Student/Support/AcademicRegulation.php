<?php

namespace Modules\Student\Support;

use Illuminate\Support\Collection;

class AcademicRegulation
{
    public const FINAL_EXAM_MAX = 60.0;
    public const SEMESTER_WORK_MAX = 40.0;
    public const PASS_TOTAL = 50.0;
    public const MIN_FINAL_PERCENT = 50.0;
    public const MIN_CUMULATIVE_AVERAGE = 55.0;
    public const MAX_CARRIED_COURSES = 2;
    public const MAX_UNITS_WITH_CARRY = 24;

    public static function evaluationLabel(float $percentage): string
    {
        return match (true) {
            $percentage >= 85.0 => 'ممتاز',
            $percentage >= 75.0 => 'جيد جداً',
            $percentage >= 65.0 => 'جيد',
            $percentage >= 50.0 => 'مقبول',
            $percentage >= 35.0 => 'ضعيف',
            default => 'ضعيف جداً',
        };
    }

    public static function finalExamMeetsMinimum(float $finalExam, float $finalExamMax = self::FINAL_EXAM_MAX): bool
    {
        if ($finalExamMax <= 0.0) {
            return false;
        }

        return ($finalExam / $finalExamMax) * 100 >= self::MIN_FINAL_PERCENT;
    }

    public static function totalGrade(float $semesterWork, float $finalExam): float
    {
        return min(100.0, max(0.0, $semesterWork) + max(0.0, $finalExam));
    }

    public static function courseStatus(float $semesterWork, float $finalExam, float $finalExamMax = self::FINAL_EXAM_MAX): string
    {
        $total = self::totalGrade($semesterWork, $finalExam);

        return $total >= self::PASS_TOTAL && self::finalExamMeetsMinimum($finalExam, $finalExamMax)
            ? 'Passed'
            : 'Failed';
    }

    public static function weightedAverage(iterable $items): float
    {
        $totalUnits = 0.0;
        $weightedGrades = 0.0;

        foreach ($items as $item) {
            $units = (float) data_get($item, 'units', data_get($item, 'course.units', 0));
            $grade = data_get($item, 'grade', data_get($item, 'total_mark'));

            if ($units <= 0.0 || $grade === null || $grade === '') {
                continue;
            }

            $totalUnits += $units;
            $weightedGrades += (float) $grade * $units;
        }

        return $totalUnits > 0.0 ? round($weightedGrades / $totalUnits, 2) : 0.0;
    }

    public static function canCarryFailedCourses(int $failedCourses, float $cumulativeAverage): bool
    {
        return $failedCourses <= self::MAX_CARRIED_COURSES
            && $cumulativeAverage >= self::MIN_CUMULATIVE_AVERAGE;
    }

    public static function carriedUnitsWithinLimit(int $destinationUnits): bool
    {
        return $destinationUnits <= self::MAX_UNITS_WITH_CARRY;
    }

    public static function shouldWarnForSemester(float $semesterAverage, float $cumulativeAverage): bool
    {
        return $semesterAverage < 35.0 || $cumulativeAverage < self::MIN_CUMULATIVE_AVERAGE;
    }

    public static function shouldDismissForConsecutiveLowCumulative(Collection $summaries): bool
    {
        return $summaries
            ->sortBy('semester_id')
            ->pluck('cumulative_gpa')
            ->map(fn ($value): bool => (float) $value < self::MIN_CUMULATIVE_AVERAGE)
            ->reverse()
            ->take(3)
            ->filter()
            ->count() >= 3;
    }

    public static function canRegisterGraduationProject(int $completedSemesterLevels, float $cumulativeAverage): bool
    {
        return $completedSemesterLevels >= 5
            && $cumulativeAverage >= self::MIN_CUMULATIVE_AVERAGE;
    }
}
