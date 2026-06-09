<?php

namespace Modules\Auth\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Specialization;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Shared\Services\MinistryNewsService;
use Modules\Staff\Models\Instructor;
use Modules\Student\Models\DepartmentTransfer;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use Modules\Student\Models\StudentSemesterSummary;

class PortalDashboardController extends Controller
{
    public function student(): InertiaResponse
    {
        $student = Student::with('currentSpecialization.department')
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $summaries = StudentSemesterSummary::with('semester')
            ->where('student_id', $student->id)
            ->latest()
            ->take(4)
            ->get();

        $enrollments = CourseEnrollment::with(['class.course', 'class.semester'])
            ->where('student_id', $student->id)
            ->latest()
            ->take(8)
            ->get()
            ->map(function (CourseEnrollment $enrollment) {
                return [
                    'id' => $enrollment->id,
                    'total_grade' => $enrollment->total_mark,
                    'grade_evaluation' => $enrollment->grade_evaluation,
                    'class' => $enrollment->class,
                ];
            });

        return Inertia::render('Student/Dashboard', [
            'student' => $student,
            'summaries' => $summaries,
            'enrollments' => $enrollments,
            'ministryNews' => $this->ministryNews(),
        ]);
    }

    public function studentProfile(): InertiaResponse
    {
        $student = Student::with('currentSpecialization.department')
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $enrollments = CourseEnrollment::with(['class.course', 'class.semester'])
            ->where('student_id', $student->id)
            ->get()
            ->map(function (CourseEnrollment $enrollment) {
                return [
                    'id' => $enrollment->id,
                    'course_code' => $enrollment->class?->course?->code ?? $enrollment->course?->code,
                    'course_name' => $enrollment->class?->course?->name ?? $enrollment->course?->name,
                    'units' => $enrollment->class?->course?->units ?? $enrollment->course?->units,
                    'semester_work_grade' => $enrollment->raw_semester_work,
                    'final_exam_grade' => $enrollment->raw_final_exam,
                    'total_grade' => $enrollment->total_mark,
                    'grade_evaluation' => $enrollment->grade_evaluation,
                    'semester_code' => $enrollment->class?->semester?->code ?? $enrollment->studyGroup?->semester?->code,
                ];
            });

        $summaries = StudentSemesterSummary::with('semester')
            ->where('student_id', $student->id)
            ->get()
            ->map(function (StudentSemesterSummary $summary) {
                return [
                    'id' => $summary->id,
                    'semester_code' => $summary->semester->code,
                    'semester_gpa' => $summary->semester_gpa,
                    'cumulative_gpa' => $summary->cumulative_gpa,
                    'total_registered_units' => $summary->total_registered_units,
                    'carried_courses_count' => $summary->carried_courses_count,
                    'result' => $summary->carried_courses_count > 0
                        ? "مرحل بـ {$summary->carried_courses_count} مواد"
                        : 'ناجح',
                    'evaluation' => $this->evaluation((float) $summary->semester_gpa),
                ];
            });

        return Inertia::render('Student/Search/Show', [
            'student' => $student,
            'enrollments' => $enrollments,
            'summaries' => $summaries,
            'semesters' => AcademicSemester::query()->select(['id', 'code'])->latest()->get(),
            'specializations' => Specialization::query()
                ->with('department:id,name')
                ->select(['id', 'department_id', 'name'])
                ->get(),
        ]);
    }

    public function teacher(): InertiaResponse
    {
        $instructor = Instructor::where('user_id', auth()->id())->firstOrFail();

        $classes = CourseClass::with(['course', 'semester', 'studyGroup.specialization'])
            ->withCount(['enrollments as student_count' => function ($query) {
                $query->whereColumn('course_enrollments.study_group_id', 'course_classes.study_group_id');
            }])
            ->where('instructor_id', $instructor->id)
            ->whereNotNull('study_group_id')
            ->latest()
            ->get();

        return Inertia::render('Teacher/Dashboard', [
            'instructor' => $instructor,
            'classes' => $classes,
            'ministryNews' => $this->ministryNews(),
        ]);
    }

    public function employee(): InertiaResponse
    {
        return Inertia::render('Employee/Dashboard', [
            'stats' => [
                'students' => Student::count(),
                'active_students' => Student::where('status', 'Active')->count(),
                'courses' => Course::count(),
                'semesters' => AcademicSemester::count(),
                'pending_transfers' => DepartmentTransfer::count(),
                'teachers' => Instructor::count(),
            ],
            'ministryNews' => $this->ministryNews(),
        ]);
    }

    /**
     * @return array<int, array{title: string, link: string, author: ?string, published_at: ?string, image_url: ?string}>
     */
    private function ministryNews(): array
    {
        return app(MinistryNewsService::class)->fetch();
    }

    private function evaluation(float $grade): string
    {
        return match (true) {
            $grade >= 85 => 'ممتاز',
            $grade >= 75 => 'جيد جداً',
            $grade >= 65 => 'جيد',
            $grade >= 50 => 'مقبول',
            $grade >= 35 => 'ضعيف',
            default => 'ضعيف جداً',
        };
    }
}
