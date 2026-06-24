<?php

namespace Modules\Staff\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Academic\Models\CourseClass;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Staff\Models\Instructor;
use Modules\Student\Actions\RecordCourseGrades;
use Modules\Student\Models\CourseEnrollment;

class GradeController extends Controller
{
    public function edit(CourseClass $courseClass): InertiaResponse
    {
        $instructor = $this->currentInstructor();
        $this->authorizeClass($courseClass, $instructor);

        $courseClass->load(['course', 'semester', 'studyGroup.specialization']);

        $students = $this->classEnrollments($courseClass)
            ->with('student')
            ->orderBy('student_id')
            ->get()
            ->map(fn (CourseEnrollment $enrollment) => [
                'enrollment_id' => $enrollment->id,
                'student_name' => $enrollment->student?->full_name ?? '-',
                'registration_number' => $enrollment->student?->registration_number ?? '-',
                'semester_work_grade' => $enrollment->raw_semester_work,
                'final_exam_grade' => $enrollment->raw_final_exam,
                'total_grade' => $enrollment->total_mark,
                'evaluation' => $enrollment->grade_evaluation,
            ]);

        return Inertia::render('Teacher/ClassGrades', [
            'courseClass' => $courseClass,
            'students' => $students,
        ]);
    }

    public function update(Request $request, CourseClass $courseClass): RedirectResponse
    {
        $instructor = $this->currentInstructor();
        $this->authorizeClass($courseClass, $instructor);

        $validated = $request->validate([
            'grades' => ['required', 'array'],
            'grades.*.semester_work_grade' => ['required', 'numeric', 'min:0', 'max:40'],
            'grades.*.final_exam_grade' => ['required', 'numeric', 'min:0', 'max:60'],
        ], [
            'grades.required' => 'يجب إدخال درجات الطلاب.',
            'grades.*.semester_work_grade.required' => 'درجة أعمال الفصل مطلوبة.',
            'grades.*.semester_work_grade.numeric' => 'درجة أعمال الفصل يجب أن تكون رقما.',
            'grades.*.semester_work_grade.min' => 'درجة أعمال الفصل لا يمكن أن تكون أقل من صفر.',
            'grades.*.semester_work_grade.max' => 'درجة أعمال الفصل لا يمكن أن تتجاوز 40.',
            'grades.*.final_exam_grade.required' => 'درجة الامتحان النهائي مطلوبة.',
            'grades.*.final_exam_grade.numeric' => 'درجة الامتحان النهائي يجب أن تكون رقما.',
            'grades.*.final_exam_grade.min' => 'درجة الامتحان النهائي لا يمكن أن تكون أقل من صفر.',
            'grades.*.final_exam_grade.max' => 'درجة الامتحان النهائي لا يمكن أن تتجاوز 60.',
        ]);

        $classEnrollmentIds = $this->classEnrollments($courseClass)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        DB::transaction(function () use ($validated, $classEnrollmentIds) {
            $recorder = app(RecordCourseGrades::class);

            foreach ($validated['grades'] as $enrollmentId => $grade) {
                if (! in_array((string) $enrollmentId, $classEnrollmentIds, true)) {
                    continue;
                }

                $recorder->execute(
                    (int) $enrollmentId,
                    (float) $grade['semester_work_grade'],
                    (float) $grade['final_exam_grade'],
                );
            }
        });

        $courseClass->loadMissing('course');

        activity()
            ->causedBy($request->user())
            ->performedOn($courseClass)
            ->withProperties([
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'class_id' => $courseClass->id,
                'course_id' => $courseClass->course_id,
                'course_name' => $courseClass->course?->name,
                'updated_enrollments' => count($validated['grades']),
            ])
            ->log('تم تحديث درجات الطلاب للمادة '.$courseClass->course?->name);

        return back()->with('success', 'تم حفظ جميع الدرجات بنجاح.');
    }

    private function currentInstructor(): Instructor
    {
        return Instructor::where('user_id', auth()->id())->firstOrFail();
    }

    private function authorizeClass(CourseClass $courseClass, Instructor $instructor): void
    {
        abort_unless(
            (int) $courseClass->instructor_id === (int) $instructor->id,
            403,
            'غير مصرح لك برصد درجات هذه الشعبة لأنها غير مسندة لك.'
        );
    }

    private function classEnrollments(CourseClass $courseClass)
    {
        return CourseEnrollment::query()
            ->where('class_id', $courseClass->id)
            ->when($courseClass->study_group_id, fn ($query) => $query->where('study_group_id', $courseClass->study_group_id));
    }
}
