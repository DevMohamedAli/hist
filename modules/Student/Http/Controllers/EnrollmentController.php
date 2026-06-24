<?php

namespace Modules\Student\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\StudyGroup;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Student\Actions\ActivateStudentRegistrationWorkflow;
use Modules\Student\Actions\CalculateCGPAAction;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use Modules\Student\Services\PrerequisiteService;
use Modules\Student\Support\AcademicRegulation;

class EnrollmentController extends Controller
{
    public function create(int $studentId): InertiaResponse|RedirectResponse
    {
        $student = Student::with(['currentSpecialization.department', 'currentStudyGroup.semester'])->findOrFail($studentId);
        $currentSemester = AcademicSemester::openForRegistration();

        if ($student->status === 'Graduated') {
            return redirect()->route('students.show', $student->id)->withErrors([
                'enrollment' => 'لا يمكن تسجيل أو تنزيل المقررات للطالب المتخرج.',
            ]);
        }

        if ($student->status !== 'Active') {
            return redirect()->route('students.show', $student->id)->withErrors([
                'enrollment' => 'لا يمكن تنزيل المقررات لطالب غير نشط.',
            ]);
        }

        if (! $currentSemester) {
            return redirect()->route('students.show', $student->id)->withErrors([
                'enrollment' => 'تسجيل وتنزيل المقررات غير متاح حالياً. يفتح التسجيل فقط خلال فترة التسجيل المعتمدة في بداية الفصل الدراسي.',
            ]);
        }

        if (! $student->current_study_group_id
            || (int) $student->currentStudyGroup?->academic_semester_id !== (int) $currentSemester->id) {
            app(ActivateStudentRegistrationWorkflow::class)->execute($student);
            $student->refresh()->loadMissing('currentStudyGroup.semester');
        }

        $studyGroups = StudyGroup::with('semester')
            ->where('specialization_id', $student->current_specialization_id)
            ->where('academic_semester_id', $currentSemester->id)
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [(int) $student->current_study_group_id])
            ->orderBy('semester_level')
            ->orderBy('group_name')
            ->get();

        $prerequisites = app(PrerequisiteService::class);

        $formattedGroups = $studyGroups->map(function (StudyGroup $group) use ($student, $prerequisites) {
            $courses = $group->courses()->load('prerequisites');

            return [
                'id' => $group->id,
                'group_name' => $group->group_name,
                'semester_level' => $group->semester_level,
                'semester' => $group->semester,
                'courses' => $courses->map(fn (Course $course): array => $this->courseEnrollmentOption(
                    $student,
                    $course,
                    $prerequisites,
                ))->values(),
            ];
        });

        $carriedEnrollments = CourseEnrollment::where('student_id', $studentId)
            ->where('status', 'Failed')
            ->where('is_carried', false)
            ->with('course')
            ->get()
            ->unique('course_id')
            ->map(fn (CourseEnrollment $enrollment) => [
                'id' => $enrollment->id,
                'course_id' => $enrollment->course_id,
                'name' => $enrollment->course?->name,
                'code' => $enrollment->course?->code,
                'units' => $enrollment->course?->units ?? 0,
            ])
            ->values();

        $cgpa = app(CalculateCGPAAction::class)->execute($student);
        $hasWarning = DB::table('academic_warnings')
            ->where('student_id', $studentId)
            ->where('type', 'Warning')
            ->exists();

        return Inertia::render('Student/Enrollment', [
            'student' => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'registration_number' => $student->registration_number,
                'current_specialization' => $student->currentSpecialization,
                'current_study_group' => $student->currentStudyGroup ? [
                    'id' => $student->currentStudyGroup->id,
                    'group_name' => $student->currentStudyGroup->group_name,
                    'semester_level' => $student->currentStudyGroup->semester_level,
                    'semester_code' => $student->currentStudyGroup->semester?->code,
                ] : null,
                'status' => $student->status,
                'cgpa' => $cgpa,
                'has_warning' => $hasWarning,
            ],
            'studyGroups' => $formattedGroups,
            'carriedEnrollments' => $carriedEnrollments,
            'currentSemester' => $currentSemester,
            'assignedStudyGroupId' => $student->current_study_group_id,
            'workflowMessage' => $student->currentStudyGroup
                ? 'تم إسناد الطالب تلقائياً إلى مجموعته الدراسية لهذا الفصل، وسيتم تنزيل المواد على هذه المجموعة.'
                : 'لم يتم العثور على مجموعة مرتبطة بالطالب بعد، ويمكن اختيار المجموعة يدوياً.',
        ]);
    }

    public function store(Request $request, int $studentId): RedirectResponse
    {
        $currentSemester = AcademicSemester::openForRegistration();

        if (! $currentSemester) {
            return redirect()->route('students.show', $studentId)->withErrors([
                'enrollment' => 'لا يمكن حفظ تسجيل المقررات خارج فترة التسجيل المعتمدة للفصل الدراسي.',
            ]);
        }

        $validated = $request->validate([
            'study_group_id' => 'required|exists:study_groups,id',
            'selected_course_ids' => 'required|array',
            'selected_course_ids.*' => 'exists:courses,id',
            'selected_carried_ids' => 'nullable|array',
            'selected_carried_ids.*' => 'nullable|exists:courses,id',
        ]);

        $student = Student::with(['currentSpecialization', 'currentStudyGroup'])->findOrFail($studentId);
        $group = StudyGroup::findOrFail($validated['study_group_id']);

        if ($student->status === 'Graduated') {
            return redirect()->route('students.show', $student->id)->withErrors([
                'enrollment' => 'لا يمكن تسجيل أو تنزيل المقررات للطالب المتخرج.',
            ]);
        }

        if ($student->status !== 'Active') {
            return redirect()->route('students.show', $student->id)->withErrors([
                'enrollment' => 'لا يمكن تنزيل المقررات لطالب غير نشط.',
            ]);
        }

        if ((int) $group->academic_semester_id !== (int) $currentSemester->id) {
            return redirect()->back()->withErrors([
                'study_group_id' => 'الشعبة المختارة لا تتبع الفصل المفتوح حالياً للتسجيل.',
            ]);
        }

        if ($student->current_study_group_id && (int) $student->current_study_group_id !== (int) $group->id) {
            return redirect()->back()->withErrors([
                'study_group_id' => 'هذا الطالب مرتبط بمجموعة دراسية أخرى لهذا الفصل، ولا يمكن تنزيل المواد على مجموعة مختلفة.',
            ]);
        }

        $groupCourses = $group->courses();
        $groupCourseIds = $groupCourses->pluck('id')->toArray();
        $invalidBase = array_diff($validated['selected_course_ids'], $groupCourseIds);

        if (! empty($invalidBase)) {
            return redirect()->back()->withErrors([
                'selected_course_ids' => 'أحد المقررات المختارة لا ينتمي لهذه المجموعة الدراسية.',
            ]);
        }

        $carriedIds = array_values(array_unique(array_filter($validated['selected_carried_ids'] ?? [])));
        $cgpa = app(CalculateCGPAAction::class)->execute($student);

        if (! empty($carriedIds) && ! AcademicRegulation::canCarryFailedCourses(count($carriedIds), (float) $cgpa)) {
            return redirect()->back()->withErrors([
                'selected_carried_ids' => 'لا يسمح بحمل أكثر من مقررين، ولا يسمح بحمل مقررات إذا كان المعدل التراكمي أقل من 55%.',
            ]);
        }

        $allowedCarriedIds = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->where('status', 'Failed')
            ->where('is_carried', false)
            ->pluck('course_id')
            ->unique()
            ->all();

        $invalidCarried = array_diff($carriedIds, $allowedCarriedIds);
        if (! empty($invalidCarried)) {
            return redirect()->back()->withErrors([
                'selected_carried_ids' => 'لا يمكن حمل إلا المقررات الراسبة غير المحمولة سابقاً.',
            ]);
        }

        $baseCourses = Course::whereIn('id', $validated['selected_course_ids'])
            ->with('prerequisites')
            ->get();

        $blockedCourses = $baseCourses
            ->mapWithKeys(function (Course $course) use ($student): array {
                $missing = app(PrerequisiteService::class)->missingPrerequisites($student, $course);

                return $missing->isEmpty()
                    ? []
                    : [$course->id => [
                        'course' => $course,
                        'missing' => $missing,
                    ]];
            });

        if ($blockedCourses->isNotEmpty()) {
            $messages = $blockedCourses
                ->map(fn (array $item): string => $item['course']->name.' حتى النجاح في المتطلب السابق: '.$item['missing']->pluck('name')->join('، '))
                ->values()
                ->join('؛ ');

            return redirect()->back()->withErrors([
                'selected_course_ids' => $messages,
            ]);
        }

        $carriedCourses = Course::whereIn('id', $carriedIds)->with('prerequisites')->get();
        $allCourses = $baseCourses->merge($carriedCourses);

        $dependentCarriedCourses = $carriedCourses->filter(function (Course $course) use ($carriedIds) {
            return $course->prerequisites->pluck('id')->intersect($carriedIds)->isNotEmpty();
        });

        if ($dependentCarriedCourses->isNotEmpty()) {
            return redirect()->back()->withErrors([
                'selected_carried_ids' => 'لا يسمح بحمل مقررات يعتمد بعضها على بعض كمتطلبات سابقة: '.$dependentCarriedCourses->pluck('name')->join('، '),
            ]);
        }

        $classesByCourse = CourseClass::where('study_group_id', $group->id)
            ->whereIn('course_id', $allCourses->pluck('id'))
            ->get()
            ->keyBy('course_id');

        $missingClassCourses = $allCourses->reject(fn (Course $course) => $classesByCourse->has($course->id));
        if ($missingClassCourses->isNotEmpty()) {
            return redirect()->back()->withErrors([
                'selected_course_ids' => 'لا يمكن تنزيل المواد قبل إسناد محاضر لكل مقرر داخل هذه المجموعة: '.$missingClassCourses->pluck('name')->join('، '),
            ]);
        }

        $totalUnits = (int) $baseCourses->sum('units') + (int) $carriedCourses->sum('units');
        $hasWarning = DB::table('academic_warnings')
            ->where('student_id', $studentId)
            ->where('type', 'Warning')
            ->exists();

        $maxAllowed = AcademicRegulation::maxUnitsForRegistration(
            cumulativeAverage: (float) $cgpa,
            hasWarning: $hasWarning,
            hasCarriedCourses: count($carriedIds) > 0,
        );

        if ($totalUnits > $maxAllowed) {
            return redirect()->back()->withErrors([
                'study_group_id' => "فشل التسجيل: إجمالي الوحدات ({$totalUnits}) يتجاوز الحد الأقصى ({$maxAllowed}).",
            ]);
        }

        if ($totalUnits < AcademicRegulation::MIN_NORMAL_UNITS && (int) $group->semester_level !== 6) {
            return redirect()->back()->withErrors([
                'study_group_id' => 'فشل التسجيل: لا يمكن التسجيل بأقل من 12 وحدة في الفصل العادي.',
            ]);
        }

        DB::transaction(function () use ($student, $group, $baseCourses, $carriedCourses, $classesByCourse) {
            $student->forceFill(['current_study_group_id' => $group->id])->save();

            foreach ($baseCourses as $course) {
                CourseEnrollment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'course_id' => $course->id,
                        'study_group_id' => $group->id,
                    ],
                    [
                        'class_id' => $classesByCourse[$course->id]->id,
                        'is_carried' => false,
                        'status' => 'Pending',
                    ]
                );
            }

            foreach ($carriedCourses as $course) {
                CourseEnrollment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'course_id' => $course->id,
                        'study_group_id' => $group->id,
                    ],
                    [
                        'class_id' => $classesByCourse[$course->id]->id,
                        'is_carried' => true,
                        'status' => 'Pending',
                    ]
                );
            }

            if ($carriedCourses->isNotEmpty()) {
                CourseEnrollment::query()
                    ->where('student_id', $student->id)
                    ->whereIn('course_id', $carriedCourses->pluck('id'))
                    ->where('status', 'Failed')
                    ->where('is_carried', false)
                    ->update(['is_carried' => true]);
            }
        });

        activity()
            ->causedBy($request->user())
            ->performedOn($student)
            ->withProperties([
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'student_id' => $student->id,
                'registration_number' => $student->registration_number,
                'study_group_id' => $group->id,
                'base_courses_count' => count($validated['selected_course_ids']),
                'carried_courses_count' => count($carriedIds),
                'total_units' => $totalUnits,
            ])
            ->log('تم تنزيل المقررات للطالب');

        return redirect()->route('students.show', $student->id)
            ->with('success', 'تم تنزيل المقررات وتجديد القيد بنجاح.');
    }

    private function courseEnrollmentOption(Student $student, Course $course, PrerequisiteService $prerequisites): array
    {
        $missingPrerequisites = $prerequisites->missingPrerequisites($student, $course);

        return [
            'id' => $course->id,
            'code' => $course->code,
            'name' => $course->name,
            'units' => $course->units,
            'is_eligible' => $missingPrerequisites->isEmpty(),
            'missing_prerequisites' => $missingPrerequisites
                ->map(fn (Course $prerequisite): array => [
                    'id' => $prerequisite->id,
                    'code' => $prerequisite->code,
                    'name' => $prerequisite->name,
                ])
                ->values(),
            'prerequisite_message' => $missingPrerequisites->isEmpty()
                ? null
                : 'لا يمكن تسجيل هذا المقرر حتى النجاح في المتطلب السابق: '.$missingPrerequisites->pluck('name')->join('، '),
        ];
    }
}
