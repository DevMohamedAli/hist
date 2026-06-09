<?php

namespace Modules\Student\Actions;

use Modules\Academic\Models\Course;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use Modules\Student\Support\AcademicRegulation;

class CheckGraduationAction
{
    public function execute(Student $student): array
    {
        $cgpa = app(CalculateCGPAAction::class)->execute($student);
        $specializationId = $student->current_specialization_id;

        $requiredCourseIds = Course::query()
            ->whereHas('specializations', fn ($query) => $query->whereKey($specializationId))
            ->pluck('id');

        $passedCourseIds = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->whereIn('course_id', $requiredCourseIds)
            ->where('status', 'Passed')
            ->distinct()
            ->pluck('course_id');

        $missingCourses = $requiredCourseIds->diff($passedCourseIds)->count();

        if ($requiredCourseIds->isNotEmpty() && $missingCourses === 0 && $cgpa >= AcademicRegulation::MIN_CUMULATIVE_AVERAGE) {
            $student->update(['status' => 'Graduated']);

            return [
                'is_graduated' => true,
                'message' => "استوفى الطالب متطلبات التخرج بمعدل تراكمي {$cgpa}%.",
            ];
        }

        $reasons = [];

        if ($missingCourses > 0) {
            $reasons[] = "لم يكمل الطالب الخطة الدراسية. المتبقي {$missingCourses} مقرر.";
        }

        if ($cgpa < AcademicRegulation::MIN_CUMULATIVE_AVERAGE) {
            $reasons[] = "المعدل التراكمي {$cgpa}% أقل من الحد الأدنى 55%.";
        }

        return [
            'is_graduated' => false,
            'message' => 'الطالب غير مؤهل للتخرج بعد.',
            'reasons' => $reasons,
        ];
    }
}
