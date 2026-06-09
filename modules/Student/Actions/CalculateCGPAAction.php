<?php

namespace Modules\Student\Actions;

use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use Modules\Student\Support\AcademicRegulation;

class CalculateCGPAAction
{
    public function execute(Student $student): float
    {
        $enrollments = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['Passed', 'Failed'])
            ->whereHas('course')
            ->with('course')
            ->get();

        return AcademicRegulation::weightedAverage($enrollments);
    }
}
