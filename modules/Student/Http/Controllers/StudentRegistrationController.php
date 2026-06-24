<?php

namespace Modules\Student\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Inertia\Response as InertiaResponse;
use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Specialization;
use Modules\Platform\Models\Setting;
use Modules\Qualification\Models\Qualification;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Student\Actions\ActivateStudentRegistrationWorkflow;
use Modules\Student\Helpers\RegistrationHelper;
use Modules\Student\Models\Student;
use Modules\Student\Models\StudentSemesterSummary;
use Modules\Student\Support\AcademicRegulation;
use Modules\User\Models\User;

class StudentRegistrationController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $specialization = $request->input('specialization');

        $query = Student::query()->with('currentSpecialization.department');

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($specialization) {
            $query->where('current_specialization_id', $specialization);
        }

        $specializations = Specialization::with('department')->get();
        $students = $query->paginate(15)->withQueryString();

        return Inertia::render('Student/Index', [
            'students' => $this->cleanUtf8($students),
            'specializations' => $this->cleanUtf8($specializations),
            'registrationAvailability' => $this->registrationAvailability(),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'specialization' => $specialization,
            ],
        ]);
    }

    public function show(int $studentId): Response
    {
        $student = Student::with(['currentSpecialization.department', 'currentStudyGroup.semester'])->findOrFail($studentId);
        $isGraduated = $student->status === 'Graduated';

        $enrollments = $student->enrollments()
            ->with(['course', 'studyGroup.semester'])
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'course_code' => $enrollment->course->code ?? 'N/A',
                    'course_name' => $enrollment->course->name ?? 'N/A',
                    'units' => $enrollment->course->units ?? 0,
                    'semester_work_grade' => $enrollment->raw_semester_work,
                    'final_exam_grade' => $enrollment->raw_final_exam,
                    'total_grade' => $enrollment->total_mark,
                    'grade_evaluation' => $enrollment->grade_evaluation,
                    'semester_code' => $enrollment->studyGroup->semester->code ?? 'N/A',
                    'status' => $enrollment->status,
                ];
            });

        $summaries = StudentSemesterSummary::where('student_id', $studentId)
            ->with('semester')
            ->get()
            ->map(function ($summary) {
                return [
                    'id' => $summary->id,
                    'semester_code' => $summary->semester->code ?? 'N/A',
                    'semester_gpa' => $summary->semester_gpa,
                    'cumulative_gpa' => $summary->cumulative_gpa,
                    'total_registered_units' => $summary->total_registered_units,
                    'carried_courses_count' => $summary->carried_courses_count,
                    'result' => $summary->carried_courses_count > 0 ? "مرحل بـ {$summary->carried_courses_count} مواد" : 'ناجح',
                    'evaluation' => AcademicRegulation::evaluationLabel((float) $summary->semester_gpa),
                ];
            });

        $semesters = AcademicSemester::orderBy('year', 'desc')->get();
        $specializations = Specialization::with('department')->get();
        $registrationSemester = AcademicSemester::openForRegistration();
        $gradeEntrySemester = AcademicSemester::currentAcademicSemester();
        $gradeEntryOpen = $gradeEntrySemester?->gradeEntryIsOpen() ?? false;
        $hasTransferredBefore = $student->transfers()->exists();
        $canTransferSpecialization = ! $isGraduated
            && $student->status === 'Active'
            && ! $hasTransferredBefore
            && (int) $student->current_semester_level <= 2;

        return Inertia::render('Student/Show', [
            'student' => $this->cleanUtf8($student),
            'enrollments' => $this->cleanUtf8($enrollments),
            'summaries' => $this->cleanUtf8($summaries),
            'semesters' => $this->cleanUtf8($semesters),
            'specializations' => $this->cleanUtf8($specializations),
            'enrollmentAvailability' => [
                'is_open' => $registrationSemester !== null && ! $isGraduated && $student->status === 'Active',
                'semester' => $registrationSemester ? [
                    'id' => $registrationSemester->id,
                    'code' => $registrationSemester->code,
                    'registration_start' => $registrationSemester->registration_start?->toDateString(),
                    'registration_end' => $registrationSemester->registration_end?->toDateString(),
                ] : null,
                'message' => $isGraduated
                    ? 'الطالب متخرج بالفعل، ولا يمكن تسجيل أو تنزيل المقررات له.'
                    : ($registrationSemester
                    ? 'فترة التسجيل مفتوحة حتى '.$registrationSemester->registration_end?->format('Y-m-d').'.'
                    : 'تسجيل وتنزيل المقررات غير متاح حالياً. يفتح التسجيل فقط خلال فترة التسجيل المعتمدة في بداية الفصل الدراسي.'),
            ],
            'transferEligibility' => [
                'can_transfer' => $canTransferSpecialization,
                'has_transferred_before' => $hasTransferredBefore,
                'message' => match (true) {
                    $isGraduated => 'الطالب متخرج بالفعل، ولا يمكن انتقال التخصص له.',
                    $hasTransferredBefore => 'لا يمكن انتقال التخصص أكثر من مرة واحدة خلال مدة الدراسة.',
                    (int) $student->current_semester_level > 2 => 'لا يسمح بانتقال التخصص بعد إكمال أكثر من فصلين دراسيين في التخصص الأصلي.',
                    default => 'يمكن للطالب انتقال التخصص مرة واحدة فقط وفق الضوابط المعتمدة.',
                },
            ],
            'workflow' => [
                'assignment' => $student->currentStudyGroup ? [
                    'group_name' => $student->currentStudyGroup->group_name,
                    'semester_level' => $student->currentStudyGroup->semester_level,
                    'semester_code' => $student->currentStudyGroup->semester?->code,
                ] : null,
                'activation_message' => $student->currentStudyGroup
                    ? 'تم إسناد الطالب إلى مجموعة دراسية ويمكن متابعة تنزيل المواد وفق الفصل المفتوح.'
                    : 'الطالب محفوظ في النظام، لكن لم يتم إسناده بعد إلى مجموعة دراسية مفتوحة.',
                'grade_entry' => [
                    'is_open' => $gradeEntryOpen,
                    'semester_code' => $gradeEntrySemester?->code,
                    'message' => $gradeEntryOpen
                        ? 'رصد الدرجات مفتوح حالياً للشعب التابعة للفصل الجاري.'
                        : 'رصد الدرجات يبقى مغلقاً حتى بداية فترة الامتحانات النهائية للفصل الجاري.',
                ],
            ],
        ]);
    }

    private function calculateGradeEvaluation(float $totalGrade): string
    {
        if ($totalGrade >= 85) {
            return 'ممتاز';
        }
        if ($totalGrade >= 75) {
            return 'جيد جداً';
        }
        if ($totalGrade >= 65) {
            return 'جيد';
        }
        if ($totalGrade >= 50) {
            return 'مقبول';
        }
        if ($totalGrade >= 35) {
            return 'ضعيف';
        }

        return 'ضعيف جداً';
    }

    public function create(): InertiaResponse|RedirectResponse
    {
        $availability = $this->registrationAvailability();

        if (! $availability['is_open']) {
            return redirect()->route('students.index')
                ->withErrors(['registration' => $availability['message']]);
        }

        $specializations = Specialization::with('department')->get();

        return Inertia::render('Student/Create', [
            'specializations' => $this->cleanUtf8($specializations),
            'qualifications' => $this->cleanUtf8($this->qualificationOptions()),
            'availableUsers' => $this->cleanUtf8($this->availableStudentUsers()),
            'registrationAvailability' => $availability,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $availability = $this->registrationAvailability();

        if (! $availability['is_open']) {
            return redirect()->route('students.index')
                ->withErrors(['registration' => $availability['message']]);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'national_id' => 'required|string|size:12|unique:students,national_id',
            'gender' => 'required|in:Male,Female',
            'nationality' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'mobile' => 'nullable|string|max:20',
            'admission_date' => 'required|date',
            'qualification' => 'nullable|string|max:100',
            'qualification_mode' => 'nullable|in:none,existing,new',
            'qualification_id' => 'nullable|required_if:qualification_mode,existing|exists:qualifications,id',
            'new_qualification_degree_name' => 'nullable|required_if:qualification_mode,new|string|max:255',
            'new_qualification_institution' => 'nullable|required_if:qualification_mode,new|string|max:255',
            'current_specialization_id' => 'required|exists:specializations,id',
            'current_semester_level' => 'nullable|integer|min:1',
            'status' => 'nullable|in:Active,Suspended,Transferred_Out,Withdrawn,Dismissed,Graduated',
            'account_mode' => 'nullable|in:none,existing,create',
            'user_id' => 'nullable|required_if:account_mode,existing|exists:users,id',
            'user_email' => 'nullable|required_if:account_mode,create|email|max:255|unique:users,email',
            'user_password' => 'nullable|required_if:account_mode,create|string|min:8',
        ]);

        if (($validated['account_mode'] ?? null) === 'existing'
            && Student::query()->where('user_id', $validated['user_id'] ?? null)->exists()) {
            throw ValidationException::withMessages([
                'user_id' => 'هذا المستخدم مرتبط بطالب آخر بالفعل.',
            ]);
        }

        $workflow = null;

        $student = DB::transaction(function () use ($validated, &$workflow): Student {
            $validated['status'] = $validated['status'] ?? 'Active';
            $validated['current_semester_level'] = 1;
            $this->applyQualificationSelection($validated);
            $validated['registration_number'] = $this->generateUniqueRegistrationNumber($validated['admission_date']);

            $student = Student::create(collect($validated)->except([
                'qualification_mode',
                'new_qualification_degree_name',
                'new_qualification_institution',
                'account_mode',
                'user_id',
                'user_email',
                'user_password',
            ])->all());

            $this->linkStudentUser($student, $validated);
            $workflow = app(ActivateStudentRegistrationWorkflow::class)->execute($student);

            return $student;
        });

        activity()
            ->causedBy($request->user())
            ->performedOn($student)
            ->withProperties([
                'attributes' => $student->toArray(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'workflow' => [
                    'activated' => $workflow['activated'] ?? false,
                    'semester_id' => $workflow['semester']->id ?? null,
                    'study_group_id' => $workflow['study_group']->id ?? null,
                ],
            ])
            ->log('تم تسجيل طالب جديد');

        return redirect()->route('students.show', $student->id)
            ->with('success', trim('تم تسجيل الطالب الجديد بنجاح وإصدار رقم القيد: '.$student->registration_number.'. '.($workflow['message'] ?? '')));
    }

    public function edit(int $studentId): InertiaResponse
    {
        $student = Student::findOrFail($studentId);
        $specializations = Specialization::with('department')->get();

        return Inertia::render('Student/Edit', [
            'student' => $this->cleanUtf8($student),
            'specializations' => $this->cleanUtf8($specializations),
            'qualifications' => $this->cleanUtf8($this->qualificationOptions()),
            'calculatedSemesterLevel' => $this->calculateSemesterLevel($student),
        ]);
    }

    public function update(Request $request, int $studentId): RedirectResponse
    {
        $student = Student::findOrFail($studentId);

        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'gender' => 'required|in:Male,Female',
            'nationality' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'mobile' => 'nullable|string|max:20',
            'qualification' => 'nullable|string|max:100',
            'qualification_id' => 'nullable|exists:qualifications,id',
            'current_specialization_id' => 'required|exists:specializations,id',
            'status' => 'nullable|in:Active,Suspended,Transferred_Out,Withdrawn,Dismissed,Graduated',
        ]);

        $this->applyQualificationSelection($validated);
        $validated['current_semester_level'] = $this->calculateSemesterLevel(
            $student,
            (int) $validated['current_specialization_id'],
        );

        $old = $student->toArray();
        $student->update($validated);
        $new = $student->fresh()->toArray();

        activity()
            ->causedBy($request->user())
            ->performedOn($student)
            ->withProperties([
                'old' => $old,
                'attributes' => $new,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ])
            ->log('تم تحديث بيانات الطالب');

        return redirect()->route('students.show', $student->id)
            ->with('success', 'تم تحديث بيانات الطالب بنجاح.');
    }

    public function destroy(Request $request, int $studentId): RedirectResponse
    {
        $student = Student::findOrFail($studentId);
        $studentName = $student->full_name;
        $old = $student->toArray();

        $student->delete();

        activity()
            ->causedBy($request->user())
            ->performedOn($student)
            ->withProperties([
                'old' => $old,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ])
            ->log('تم حذف الطالب من النظام');

        return redirect()->route('students.index')
            ->with('success', "تم حذف الطالب: {$studentName} بنجاح.");
    }

    public function reactivate(Request $request, int $studentId): RedirectResponse
    {
        $student = Student::findOrFail($studentId);

        if ($student->status !== 'Suspended') {
            return redirect()->back()->withErrors([
                'status' => 'لا يمكن إعادة تفعيل طالب غير موقوف.',
            ]);
        }

        $student->update(['status' => 'Active']);

        activity()
            ->causedBy($request->user())
            ->performedOn($student)
            ->withProperties([
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'student_id' => $student->id,
                'registration_number' => $student->registration_number,
                'old_status' => 'Suspended',
                'new_status' => 'Active',
            ])
            ->log('تم إعادة تفعيل الطالب');

        return redirect()->back()
            ->with('success', 'تم إعادة تفعيل الطالب بنجاح.');
    }

    private function generateUniqueRegistrationNumber(string $admissionDate): string
    {
        $adminCode = (int) Setting::getValue('admin_code', 0);
        $instituteCode = (int) Setting::getValue('institute_code', 0);
        $timestamp = strtotime($admissionDate);
        $year = (int) date('y', $timestamp);
        $semester = $this->semesterCodeForAdmissionDate($admissionDate);

        $prefix = substr(RegistrationHelper::generateStudentId($adminCode, $instituteCode, $year, $semester, 0), 0, 6);
        $lastRegistrationNumber = Student::query()
            ->where('registration_number', 'like', $prefix.'%')
            ->orderByDesc('registration_number')
            ->value('registration_number');
        $nextSequence = $lastRegistrationNumber ? ((int) substr($lastRegistrationNumber, -3)) + 1 : 1;

        while ($nextSequence <= 999) {
            $registrationNumber = RegistrationHelper::generateStudentId($adminCode, $instituteCode, $year, $semester, $nextSequence);

            if (! Student::where('registration_number', $registrationNumber)->exists()) {
                return $registrationNumber;
            }

            $nextSequence++;
        }

        throw new \RuntimeException('لا يمكن إصدار رقم قيد جديد: تم استنفاد التسلسل لهذا الفصل.');
    }

    private function semesterCodeForAdmissionDate(string $admissionDate): int
    {
        $month = (int) date('n', strtotime($admissionDate));

        return $month >= 2 && $month <= 7 ? 1 : 2;
    }

    private function qualificationOptions()
    {
        return Qualification::query()
            ->orderBy('degree_name')
            ->orderBy('institution')
            ->get(['id', 'degree_name', 'institution'])
            ->unique(fn (Qualification $qualification): string => Qualification::textKey($qualification->degree_name.'|'.$qualification->institution))
            ->values()
            ->map(fn (Qualification $qualification): array => [
                'id' => $qualification->id,
                'degree_name' => $qualification->degree_name,
                'institution' => $qualification->institution,
                'label' => trim($qualification->degree_name.' - '.$qualification->institution, ' -'),
            ]);
    }

    private function applyQualificationSelection(array &$validated): void
    {
        if (($validated['qualification_mode'] ?? null) === 'new') {
            $qualification = Qualification::firstOrCreateByText(
                $validated['new_qualification_degree_name'],
                $validated['new_qualification_institution'],
            );

            $validated['qualification_id'] = $qualification->id;
            $validated['qualification'] = $qualification->degree_name;

            return;
        }

        if (empty($validated['qualification_id'])) {
            $validated['qualification_id'] = null;
            $validated['qualification'] = null;

            return;
        }

        $qualification = Qualification::find($validated['qualification_id']);

        if ($qualification) {
            $validated['qualification'] = $qualification->degree_name;
        }
    }

    private function availableStudentUsers()
    {
        return User::query()
            ->whereDoesntHave('student')
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'label' => "{$user->name} ({$user->email})",
            ]);
    }

    private function linkStudentUser(Student $student, array $validated): void
    {
        $mode = $validated['account_mode'] ?? 'none';

        if ($mode === 'existing' && ! empty($validated['user_id'])) {
            $user = User::query()
                ->whereKey($validated['user_id'])
                ->whereDoesntHave('student')
                ->first();
        } elseif ($mode === 'create') {
            $user = User::create([
                'name' => $student->full_name,
                'email' => $validated['user_email'],
                'password' => Hash::make($validated['user_password']),
            ]);
        } else {
            return;
        }

        if (! $user) {
            return;
        }

        if (! $user->hasRole('student')) {
            $user->assignRole('student');
        }

        $student->forceFill(['user_id' => $user->id])->save();
    }

    private function calculateSemesterLevel(Student $student, ?int $specializationId = null): int
    {
        $maxSemester = (int) (
            $specializationId
                ? Specialization::query()->whereKey($specializationId)->value('semesters_count')
                : $student->currentSpecialization?->semesters_count
        );
        $maxSemester = $maxSemester > 0 ? $maxSemester : 9;
        $maxSemester = max(1, $maxSemester);

        $completedLevels = $student->enrollments()
            ->with('studyGroup:id,semester_level')
            ->get()
            ->filter(fn ($enrollment): bool => $enrollment->studyGroup !== null)
            ->groupBy(fn ($enrollment): int => (int) $enrollment->studyGroup->semester_level)
            ->filter(function ($enrollments): bool {
                return $enrollments->isNotEmpty()
                    && $enrollments->every(fn ($enrollment): bool => $enrollment->status === 'Passed');
            })
            ->keys()
            ->map(fn ($level): int => (int) $level);

        $nextLevel = $completedLevels->isEmpty() ? 1 : ((int) $completedLevels->max()) + 1;

        return min($maxSemester, max(1, $nextLevel));
    }

    private function registrationAvailability(): array
    {
        $semester = AcademicSemester::openForRegistration();

        if (! $semester) {
            $currentSemester = AcademicSemester::currentAcademicSemester();
            $currentCode = $currentSemester?->code;

            return [
                'is_open' => false,
                'semester' => $currentSemester ? [
                    'id' => $currentSemester->id,
                    'code' => $currentSemester->code,
                    'registration_start' => $currentSemester->registration_start?->toDateString(),
                    'registration_end' => $currentSemester->registration_end?->toDateString(),
                ] : null,
                'message' => $currentCode
                    ? 'التسجيل مغلق حالياً. الفصل '.$currentCode.' كانت نافذة تسجيله من '.$currentSemester->registration_start?->format('Y-m-d').' إلى '.$currentSemester->registration_end?->format('Y-m-d').'.'
                    : 'التسجيل مغلق حالياً لعدم وجود فصل دراسي مفتوح للتسجيل.',
            ];
        }

        return [
            'is_open' => true,
            'semester' => [
                'id' => $semester->id,
                'code' => $semester->code,
                'registration_start' => $semester->registration_start?->toDateString(),
                'registration_end' => $semester->registration_end?->toDateString(),
            ],
            'message' => 'التسجيل مفتوح حالياً حتى '.$semester->registration_end?->format('Y-m-d').'.',
        ];
    }
}
