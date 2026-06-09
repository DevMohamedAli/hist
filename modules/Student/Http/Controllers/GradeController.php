<?php

namespace Modules\Student\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Academic\Models\CourseClass;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Student\Actions\RecordCourseGrades;
use Modules\Student\Models\CourseEnrollment;
use Modules\User\Models\User;

class GradeController extends Controller
{
    public function index(): InertiaResponse
    {
        $user = auth()->user();

        $classes = CourseClass::query()
            ->with(['course', 'semester', 'instructor', 'studyGroup.specialization'])
            ->withCount([
                'enrollments as student_count',
                'enrollments as graded_count' => fn ($query) => $query->whereIn('status', ['Passed', 'Failed']),
                'enrollments as pending_count' => fn ($query) => $query->where('status', 'Pending'),
            ])
            ->when($this->isTeacherOnly($user), function ($query) use ($user) {
                $query->whereHas('instructor', fn ($instructorQuery) => $instructorQuery->where('user_id', $user->id));
            })
            ->latest()
            ->get();

        return Inertia::render('Student/Grades/Index', [
            'classes' => $this->cleanUtf8($classes),
        ]);
    }

    public function showClassGrades(int $classId): InertiaResponse
    {
        $class = CourseClass::with(['course', 'semester', 'instructor', 'studyGroup.specialization'])->findOrFail($classId);
        $this->authorizeClassAccess($class);

        $enrollments = CourseEnrollment::where('class_id', $classId)
            ->with('student')
            ->orderBy('student_id')
            ->get()
            ->map(fn (CourseEnrollment $enrollment) => [
                'id' => $enrollment->id,
                'registration_number' => $enrollment->student?->registration_number ?? '-',
                'student_name' => $enrollment->student?->full_name ?? '-',
                'semester_work_mark' => $enrollment->raw_semester_work,
                'final_exam_mark' => $enrollment->raw_final_exam,
                'total_mark' => $enrollment->total_mark,
                'grade_evaluation' => $enrollment->grade_evaluation,
                'status' => $enrollment->status,
            ]);

        return Inertia::render('Student/Grades/ShowClass', [
            'courseClass' => $this->cleanUtf8($class),
            'enrollments' => $this->cleanUtf8($enrollments),
        ]);
    }

    public function store(Request $request, RecordCourseGrades $recordCourseGrades): RedirectResponse
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:course_enrollments,id',
            'semester_work' => 'required|numeric|min:0|max:40',
            'final_exam' => 'required|numeric|min:0|max:60',
        ], [
            'semester_work.max' => 'درجة أعمال الفصل لا يمكن أن تتجاوز 40.',
            'final_exam.max' => 'درجة الامتحان النهائي لا يمكن أن تتجاوز 60.',
        ]);

        $enrollment = CourseEnrollment::with('class')->findOrFail($validated['enrollment_id']);
        $this->authorizeClassAccess($enrollment->class);

        $enrollment = $recordCourseGrades->execute(
            (int) $validated['enrollment_id'],
            (float) $validated['semester_work'],
            (float) $validated['final_exam']
        );

        $enrollment->loadMissing(['student', 'course']);

        activity()
            ->causedBy($request->user())
            ->performedOn($enrollment)
            ->withProperties([
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'registration_number' => $enrollment->student?->registration_number,
                'raw_semester_work' => $enrollment->raw_semester_work,
                'raw_final_exam' => $enrollment->raw_final_exam,
                'total_mark' => $enrollment->total_mark,
                'status' => $enrollment->status,
            ])
            ->log('تم تحديث درجات الطالب');

        return redirect()->back()->with([
            'success' => 'تم حفظ الدرجة بنجاح.',
            'message' => 'تم حفظ الدرجة بنجاح.',
            'enrollment_id' => $enrollment->id,
            'total_mark' => $enrollment->total_mark,
            'grade_evaluation' => $enrollment->grade_evaluation,
            'status' => $enrollment->status,
        ]);
    }

    private function authorizeClassAccess(?CourseClass $class): void
    {
        abort_unless($class, 404);

        $user = auth()->user();

        if ($this->isTeacherOnly($user)) {
            abort_unless(
                (int) $class->instructor?->user_id === (int) $user->id,
                403,
                'غير مصرح لك برصد درجات هذه الشعبة.'
            );
        }
    }

    private function isTeacherOnly(?User $user): bool
    {
        return $user?->hasRole('teacher') && ! $user->hasAnyRole(['employee', 'super_admin']);
    }
}
