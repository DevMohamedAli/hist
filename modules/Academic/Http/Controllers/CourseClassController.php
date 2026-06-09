<?php

namespace Modules\Academic\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\StudyGroup;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Staff\Models\Instructor;

class CourseClassController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Academic/CourseClasses/Index', [
            'classes' => CourseClass::with(['course', 'semester', 'studyGroup.specialization', 'instructor'])
                ->withCount(['enrollments as student_count'])
                ->latest()
                ->get(),
            'studyGroups' => StudyGroup::with(['academicSemester', 'specialization'])
                ->latest()
                ->get(),
            'courses' => Course::orderBy('name')->get(['id', 'code', 'name', 'units']),
            'instructors' => Instructor::where('status', 'Active')
                ->orderBy('name')
                ->get(['id', 'name', 'employee_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $group = StudyGroup::findOrFail($validated['study_group_id']);

        CourseClass::updateOrCreate(
            [
                'course_id' => $validated['course_id'],
                'semester_id' => $group->academic_semester_id,
                'study_group_id' => $group->id,
            ],
            [
                'instructor_id' => $validated['instructor_id'],
                'group_name' => $group->group_name,
            ]
        );

        return back()->with('success', 'تم إسناد المحاضر للشعبة الدراسية بنجاح.');
    }

    public function update(Request $request, CourseClass $courseClass): RedirectResponse
    {
        $validated = $this->validated($request);
        $group = StudyGroup::findOrFail($validated['study_group_id']);

        $courseClass->update([
            'course_id' => $validated['course_id'],
            'semester_id' => $group->academic_semester_id,
            'study_group_id' => $group->id,
            'instructor_id' => $validated['instructor_id'],
            'group_name' => $group->group_name,
        ]);

        return back()->with('success', 'تم تحديث إسناد الشعبة الدراسية بنجاح.');
    }

    public function destroy(CourseClass $courseClass): RedirectResponse
    {
        $courseClass->delete();

        return back()->with('success', 'تم حذف إسناد الشعبة الدراسية بنجاح.');
    }

    /**
     * @return array{study_group_id:int, course_id:int, instructor_id:int}
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'study_group_id' => ['required', 'exists:study_groups,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'instructor_id' => ['required', 'exists:instructors,id'],
        ], [
            'study_group_id.required' => 'يجب اختيار المجموعة الدراسية.',
            'course_id.required' => 'يجب اختيار المقرر الدراسي.',
            'instructor_id.required' => 'يجب اختيار المحاضر.',
        ]);
    }
}
