<?php

namespace Modules\Student\Actions;

use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Support\AcademicRegulation;

class RecordCourseGrades
{
    public function execute(int $enrollmentId, float $semesterWork, float $finalExam): CourseEnrollment
    {
        $enrollment = CourseEnrollment::findOrFail($enrollmentId);
        $totalGrade = AcademicRegulation::totalGrade($semesterWork, $finalExam);

        $enrollment->update([
            'raw_semester_work' => $semesterWork,
            'raw_final_exam' => $finalExam,
            'total_mark' => $totalGrade,
            'grade_evaluation' => AcademicRegulation::evaluationLabel($totalGrade),
            'status' => AcademicRegulation::courseStatus($semesterWork, $finalExam),
        ]);

        return $enrollment;
    }
}
