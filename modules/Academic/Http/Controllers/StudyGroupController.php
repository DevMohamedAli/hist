<?php

namespace Modules\Academic\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\StudyGroup;
use Modules\Shared\Http\Controllers\Controller;

class StudyGroupController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'specialization_id' => 'nullable|integer|exists:specializations,id',
            'academic_semester_id' => 'nullable|integer|exists:academic_semesters,id',
            'semester_level' => 'nullable|integer|min:1|max:12',
            'per_page' => 'nullable|integer|in:10,25,50,100',
        ]);

        $perPage = $filters['per_page'] ?? 10;

        $query = StudyGroup::query()
            ->with(['specialization.department', 'academicSemester'])
            ->withCount('enrollments');

        if (! empty($filters['search'])) {
            $search = '%'.addcslashes($filters['search'], '%_').'%';

            $query->where(function ($query) use ($search): void {
                $query->where('group_name', 'like', $search)
                    ->orWhereHas('specialization', fn ($specializationQuery) => $specializationQuery->where('name', 'like', $search))
                    ->orWhereHas('academicSemester', fn ($semesterQuery) => $semesterQuery->where('code', 'like', $search));
            });
        }

        if (! empty($filters['specialization_id'])) {
            $query->where('specialization_id', $filters['specialization_id']);
        }

        if (! empty($filters['academic_semester_id'])) {
            $query->where('academic_semester_id', $filters['academic_semester_id']);
        }

        if (! empty($filters['semester_level'])) {
            $query->where('semester_level', $filters['semester_level']);
        }

        $studyGroups = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Academic/StudyGroups/Index', [
            'studyGroups' => $this->cleanUtf8($studyGroups),
            'specializations' => $this->cleanUtf8(
                Specialization::with('department')
                    ->orderBy('name')
                    ->get(['id', 'name', 'department_id'])
            ),
            'semesters' => $this->cleanUtf8(
                AcademicSemester::orderByDesc('year')
                    ->orderByDesc('start_date')
                    ->get(['id', 'code'])
            ),
            'filters' => [
                'search' => $filters['search'] ?? null,
                'specialization_id' => isset($filters['specialization_id']) ? (string) $filters['specialization_id'] : null,
                'academic_semester_id' => isset($filters['academic_semester_id']) ? (string) $filters['academic_semester_id'] : null,
                'semester_level' => isset($filters['semester_level']) ? (string) $filters['semester_level'] : null,
                'per_page' => (string) $perPage,
            ],
            'creationAvailability' => $this->studyGroupCreationAvailability(),
        ]);
    }

    public function create(): InertiaResponse|RedirectResponse
    {
        $availability = $this->studyGroupCreationAvailability();

        if (! $availability['is_open']) {
            return redirect()->route('academic.study-groups.index')
                ->withErrors(['study_group_creation' => $availability['message']]);
        }

        return Inertia::render('Academic/StudyGroups/Create', [
            'specializations' => $this->cleanUtf8(
                Specialization::with('department')
                    ->orderBy('name')
                    ->get(['id', 'name', 'department_id'])
            ),
            'semesters' => $this->cleanUtf8([
                [
                    'id' => $availability['semester']['id'],
                    'code' => $availability['semester']['code'],
                ],
            ]),
            'creationAvailability' => $availability,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $availability = $this->studyGroupCreationAvailability();

        if (! $availability['is_open']) {
            return redirect()->route('academic.study-groups.index')
                ->withErrors(['study_group_creation' => $availability['message']]);
        }

        $validated = $request->validate([
            'specialization_id' => 'required|exists:specializations,id',
            'academic_semester_id' => [
                'required',
                'integer',
                Rule::in([(int) $availability['semester']['id']]),
            ],
            'semester_level' => 'required|integer|min:1|max:12',
            'group_name' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:200',
        ], [
            'academic_semester_id.in' => 'لا يمكن إنشاء مجموعة إلا على الفصل النشط المفتوح للتسجيل وبفارق ثلاثة أيام على الأقل قبل الإغلاق.',
        ]);

        StudyGroup::create($validated);

        return redirect()->route('academic.study-groups.index')
            ->with('success', 'تم إنشاء المجموعة التدريسية بنجاح.');
    }

    public function show(int $id): InertiaResponse
    {
        $group = StudyGroup::with([
            'specialization.department',
            'academicSemester',
            'students',
        ])->findOrFail($id);

        $uniqueStudents = $group->students->unique('id')->values();

        $uniqueStudents->load(['enrollments' => function ($query) use ($id) {
            $query->where('study_group_id', $id)->with('course');
        }]);

        $group->setRelation('students', $uniqueStudents);
        $group->actual_students_count = $uniqueStudents->count();

        return Inertia::render('Academic/StudyGroups/Show', [
            'studyGroup' => $this->cleanUtf8($group),
        ]);
    }

    public function edit(int $id): InertiaResponse
    {
        $group = StudyGroup::findOrFail($id);

        return Inertia::render('Academic/StudyGroups/Edit', [
            'studyGroup' => $this->cleanUtf8($group),
            'specializations' => $this->cleanUtf8(
                Specialization::with('department')
                    ->orderBy('name')
                    ->get(['id', 'name', 'department_id'])
            ),
            'semesters' => $this->cleanUtf8(
                AcademicSemester::orderByDesc('year')
                    ->orderByDesc('start_date')
                    ->get(['id', 'code'])
            ),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $group = StudyGroup::findOrFail($id);

        $validated = $request->validate([
            'specialization_id' => 'required|exists:specializations,id',
            'academic_semester_id' => 'required|exists:academic_semesters,id',
            'semester_level' => 'required|integer|min:1|max:12',
            'group_name' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:200',
        ]);

        $group->update($validated);

        return redirect()->route('academic.study-groups.index')
            ->with('success', 'تم تحديث المجموعة التدريسية بنجاح.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $group = StudyGroup::findOrFail($id);
        $group->delete();

        return redirect()->route('academic.study-groups.index')
            ->with('success', 'تم حذف المجموعة التدريسية بنجاح.');
    }

    private function studyGroupCreationAvailability(): array
    {
        $today = Carbon::today();
        $activeSemester = AcademicSemester::query()
            ->whereDate('start_date', '<=', $today->toDateString())
            ->whereDate('end_date', '>=', $today->toDateString())
            ->orderByDesc('year')
            ->orderByDesc('start_date')
            ->first();

        if (! $activeSemester) {
            return [
                'is_open' => false,
                'semester' => null,
                'days_remaining' => null,
                'message' => 'لا يمكن إنشاء مجموعة جديدة لأنه لا يوجد فصل دراسي نشط في تاريخ اليوم.',
            ];
        }

        $semesterData = [
            'id' => $activeSemester->id,
            'code' => $activeSemester->code,
            'registration_start' => $activeSemester->registration_start?->toDateString(),
            'registration_end' => $activeSemester->registration_end?->toDateString(),
        ];

        if (! $activeSemester->registrationIsOpen($today)) {
            return [
                'is_open' => false,
                'semester' => $semesterData,
                'days_remaining' => null,
                'message' => 'الفصل النشط الحالي ليس ضمن نافذة التسجيل الآن، لذلك تم إيقاف إنشاء المجموعات الجديدة.',
            ];
        }

        $daysRemaining = $today->diffInDays($activeSemester->registration_end, false);

        if ($daysRemaining < 3) {
            return [
                'is_open' => false,
                'semester' => $semesterData,
                'days_remaining' => max($daysRemaining, 0),
                'message' => 'إنشاء المجموعات الجديدة يتطلب أن تبقى ثلاثة أيام على الأقل قبل إغلاق التسجيل. المدة المتبقية حالياً أقل من ذلك.',
            ];
        }

        return [
            'is_open' => true,
            'semester' => $semesterData,
            'days_remaining' => $daysRemaining,
            'message' => 'يمكن إنشاء مجموعات جديدة لهذا الفصل لأن التسجيل مفتوح وما يزال متبقياً '.$daysRemaining.' أيام قبل الإغلاق.',
        ];
    }
}
