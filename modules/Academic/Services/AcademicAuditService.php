<?php

namespace Modules\Academic\Services;

use Illuminate\Support\Carbon;
use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\StudyGroup;
use Modules\Graduation\Models\GraduationRecord;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;

class AcademicAuditService
{
    public function report(): array
    {
        $checks = [
            'specializations_with_no_courses' => Specialization::query()
                ->doesntHave('courses')
                ->get(['id', 'name', 'code'])
                ->toArray(),
            'courses_not_attached_to_specialization' => Course::query()
                ->doesntHave('specializations')
                ->get(['id', 'name', 'code'])
                ->toArray(),
            'study_groups_without_course_classes' => StudyGroup::query()
                ->doesntHave('courseClasses')
                ->with('specialization:id,name')
                ->get(['id', 'specialization_id', 'academic_semester_id', 'semester_level', 'group_name'])
                ->toArray(),
            'course_classes_without_instructor_or_study_group' => CourseClass::query()
                ->where(fn ($query) => $query
                    ->whereNull('instructor_id')
                    ->orWhereNull('study_group_id'))
                ->get(['id', 'course_id', 'semester_id', 'study_group_id', 'instructor_id', 'group_name'])
                ->toArray(),
            'students_with_invalid_status_for_enrollments' => CourseEnrollment::query()
                ->whereHas('student', fn ($query) => $query->whereNotIn('status', ['Active', 'Graduated']))
                ->with('student:id,registration_number,full_name,status')
                ->get(['id', 'student_id', 'course_id', 'study_group_id', 'status'])
                ->toArray(),
            'pending_grades_in_old_semesters' => CourseEnrollment::query()
                ->where('status', 'Pending')
                ->whereHas('studyGroup.academicSemester', function ($query) {
                    $query->whereDate('end_date', '<', Carbon::today())
                        ->orWhereDate('registration_end', '<', Carbon::today()->subMonths(1));
                })
                ->with([
                    'student:id,registration_number,full_name',
                    'course:id,code,name',
                    'studyGroup:id,academic_semester_id,semester_level,group_name',
                ])
                ->get(['id', 'student_id', 'course_id', 'study_group_id', 'status'])
                ->toArray(),
            'graduated_students_without_graduation_records' => Student::query()
                ->where('status', 'Graduated')
                ->doesntHave('graduationRecord')
                ->get(['id', 'registration_number', 'full_name', 'status'])
                ->toArray(),
        ];

        $seedSmoke = [
            'departments' => Department::count(),
            'specializations' => Specialization::count(),
            'semesters' => AcademicSemester::count(),
            'courses' => Course::count(),
            'students' => Student::count(),
            'enrollments' => CourseEnrollment::count(),
            'graduation_records' => GraduationRecord::count(),
            'has_enrollment_fixture' => CourseEnrollment::query()->exists(),
            'has_grade_fixture' => CourseEnrollment::query()->whereNotNull('total_mark')->exists(),
            'has_curriculum_fixture' => Specialization::query()->has('courses')->exists(),
            'has_class_fixture' => CourseClass::query()
                ->whereNotNull('study_group_id')
                ->whereNotNull('instructor_id')
                ->exists(),
        ];

        $seedSmoke['full_lifecycle_possible'] = $seedSmoke['has_enrollment_fixture']
            && $seedSmoke['has_grade_fixture']
            && $seedSmoke['has_curriculum_fixture']
            && $seedSmoke['has_class_fixture'];

        return [
            'generated_at' => now()->toIso8601String(),
            'seed_smoke' => $seedSmoke,
            'checks' => $checks,
            'has_issues' => collect($checks)->contains(fn (array $items): bool => count($items) > 0),
        ];
    }
}
