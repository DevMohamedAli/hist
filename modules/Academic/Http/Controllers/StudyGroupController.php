<?php

namespace Modules\Academic\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Academic\Models\StudyGroup;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\AcademicSemester;

class StudyGroupController extends Controller
{
    /**
     * عرض قائمة المجموعات التدريسية.
     */
    public function index(): InertiaResponse
    {
        $studyGroups = StudyGroup::with([
            'specialization.department',   // التخصص مع القسم
            'academicSemester',            // الفصل الدراسي (مثلاً ربيع 2025)
        ])
            ->withCount('enrollments')         // عدد الطلاب المسجلين
            ->latest()
            ->get();

        return Inertia::render('Academic/StudyGroups/Index', [
            'studyGroups' => $this->cleanUtf8($studyGroups),
            'specializations' => $this->cleanUtf8(Specialization::with('department')->get(['id', 'name', 'department_id'])),
            'semesters' => $this->cleanUtf8(AcademicSemester::orderByDesc('year')->orderByDesc('id')->get(['id', 'code'])),
        ]);
    }

    /**
     * عرض نموذج إنشاء مجموعة تدريسية جديدة.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('Academic/StudyGroups/Create', [
            'specializations' => $this->cleanUtf8(Specialization::with('department')->get(['id', 'name', 'department_id'])),
            'semesters'       => $this->cleanUtf8(AcademicSemester::orderBy('year', 'desc')->get(['id', 'code'])),
        ]);
    }

    /**
     * تخزين مجموعة تدريسية جديدة.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'specialization_id'    => 'required|exists:specializations,id',
            'academic_semester_id' => 'required|exists:academic_semesters,id',
            'semester_level'       => 'required|integer|min:1|max:12',
            'group_name'           => 'required|string|max:50',
            'capacity'             => 'required|integer|min:1|max:200',
        ]);

        StudyGroup::create($validated);

        return redirect()->route('academic.study-groups.index')
            ->with('success', 'تم إنشاء المجموعة التدريسية بنجاح.');
    }

    /**
     * عرض تفاصيل مجموعة تدريسية مع الطلاب المسجلين.
     */
    public function show(int $id): InertiaResponse
    {
        $group = StudyGroup::with([
            'specialization.department',
            'academicSemester',
            'students',
        ])->findOrFail($id);

        $uniqueStudents = $group->students->unique('id')->values();

        // 🚨 الإضافة الجديدة هنا: جلب المواد التي نزلها الطالب داخل هذه المجموعة فقط
        $uniqueStudents->load(['enrollments' => function ($query) use ($id) {
            $query->where('study_group_id', $id)->with('course');
        }]);

        $group->setRelation('students', $uniqueStudents);
        $group->actual_students_count = $uniqueStudents->count();

        return Inertia::render('Academic/StudyGroups/Show', [
            'studyGroup' => $this->cleanUtf8($group),
        ]);
    }

    /**
     * عرض نموذج تعديل مجموعة تدريسية.
     */
    public function edit(int $id): InertiaResponse
    {
        $group = StudyGroup::findOrFail($id);

        return Inertia::render('Academic/StudyGroups/Edit', [
            'studyGroup'      => $this->cleanUtf8($group),
            'specializations' => $this->cleanUtf8(Specialization::with('department')->get(['id', 'name', 'department_id'])),
            'semesters'       => $this->cleanUtf8(AcademicSemester::orderBy('year', 'desc')->get(['id', 'code'])),
        ]);
    }

    /**
     * تحديث بيانات مجموعة تدريسية.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $group = StudyGroup::findOrFail($id);

        $validated = $request->validate([
            'specialization_id'    => 'required|exists:specializations,id',
            'academic_semester_id' => 'required|exists:academic_semesters,id',
            'semester_level'       => 'required|integer|min:1|max:12',
            'group_name'           => 'required|string|max:50',
            'capacity'             => 'required|integer|min:1|max:200',
        ]);

        $group->update($validated);

        return redirect()->route('academic.study-groups.index')
            ->with('success', 'تم تحديث المجموعة التدريسية بنجاح.');
    }

    /**
     * حذف مجموعة تدريسية.
     */
    public function destroy(int $id): RedirectResponse
    {
        $group = StudyGroup::findOrFail($id);
        $group->delete();

        return redirect()->route('academic.study-groups.index')
            ->with('success', 'تم حذف المجموعة التدريسية بنجاح.');
    }
}
