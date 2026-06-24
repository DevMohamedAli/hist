<?php

namespace Modules\Graduation\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\Redirect;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Graduation\Actions\ApproveGraduationAction;
use Modules\Graduation\Actions\GraduationEligibilityAction;
use Modules\Graduation\Models\GraduationRecord;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Student\Models\Student;

class GraduationController extends Controller
{
    public function index(Request $request, GraduationEligibilityAction $eligibilityAction): InertiaResponse
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:eligible,blocked,approved',
            'department_id' => 'nullable|integer|exists:departments,id',
            'specialization_id' => 'nullable|integer|exists:specializations,id',
        ]);

        $baseQuery = $this->filteredStudentsQuery($filters);
        $query = clone $baseQuery;
        $status = $filters['status'] ?? null;

        $query
            ->when($status === 'approved', fn (Builder $query) => $query->whereHas('graduationRecord'))
            ->orderBy('full_name');

        $students = $query->paginate(20)->withQueryString();

        $students->getCollection()->transform(function (Student $student) use ($eligibilityAction): array {
            $record = $student->graduationRecord;
            $eligibility = $eligibilityAction->execute($student);

            return [
                'id' => $student->id,
                'registration_number' => $student->registration_number,
                'full_name' => $student->full_name,
                'status' => $student->status,
                'current_semester_level' => $student->current_semester_level,
                'current_specialization' => $student->currentSpecialization,
                'eligibility' => [
                    'eligible' => $eligibility['eligible'],
                    'cgpa' => $eligibility['cgpa'],
                    'total_units' => $eligibility['total_units'],
                    'required_count' => count($eligibility['required_courses']),
                    'passed_count' => count($eligibility['passed_courses']),
                    'missing_count' => count($eligibility['missing_courses']),
                    'reasons' => $eligibility['reasons'],
                ],
                'graduation_record' => $record ? [
                    'id' => $record->id,
                    'certificate_number' => $record->certificate_number,
                    'graduation_date' => $record->graduation_date?->toDateString(),
                ] : null,
            ];
        });

        if (in_array($status, ['eligible', 'blocked'], true)) {
            $students->setCollection($students->getCollection()->filter(
                fn (array $student): bool => ($status === 'eligible') === (bool) $student['eligibility']['eligible']
                    && $student['graduation_record'] === null
            )->values());
        }

        $counts = $this->graduationCounts((clone $baseQuery)->get(), $eligibilityAction);

        return Inertia::render('Graduation/Index', [
            'students' => $students,
            'filters' => $filters,
            'counts' => $counts,
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'specializations' => Specialization::query()->with('department:id,name')->orderBy('name')->get(['id', 'department_id', 'name']),
        ]);
    }

    public function show(Student $student, GraduationEligibilityAction $eligibilityAction): InertiaResponse
    {
        $student->load(['currentSpecialization.department', 'graduationRecord']);

        return Inertia::render('Graduation/Show', [
            'student' => $student,
            'eligibility' => $eligibilityAction->execute($student),
            'graduationRecord' => $student->graduationRecord,
            'enrollments' => $this->enrollmentRows($student),
        ]);
    }

    public function approve(Request $request, Student $student, ApproveGraduationAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $record = $action->execute($student, $request->user(), $validated['notes'] ?? null);

        return Redirect::route('graduations.show', $student)
            ->with('success', 'تم اعتماد التخرج وإصدار رقم الشهادة: '.$record->certificate_number);
    }

    private function enrollmentRows(Student $student): array
    {
        return $student->enrollments()
            ->with(['course', 'studyGroup.academicSemester', 'class.semester'])
            ->get()
            ->sortBy([
                fn ($a, $b) => strcmp((string) ($a->studyGroup?->academicSemester?->code ?? $a->class?->semester?->code ?? ''), (string) ($b->studyGroup?->academicSemester?->code ?? $b->class?->semester?->code ?? '')),
                fn ($a, $b) => strcmp((string) $a->course?->name, (string) $b->course?->name),
            ])
            ->values()
            ->map(fn ($enrollment): array => [
                'id' => $enrollment->id,
                'semester' => $enrollment->studyGroup?->academicSemester?->code ?? $enrollment->class?->semester?->code ?? '-',
                'course_code' => $enrollment->course?->code,
                'course_name' => $enrollment->course?->name,
                'units' => $enrollment->course?->units,
                'semester_work' => $enrollment->raw_semester_work,
                'final_exam' => $enrollment->raw_final_exam,
                'total_mark' => $enrollment->total_mark,
                'grade_evaluation' => $enrollment->grade_evaluation,
                'status' => $enrollment->status,
            ])
            ->all();
    }

    private function filteredStudentsQuery(array $filters): Builder
    {
        return Student::query()
            ->with(['currentSpecialization.department', 'graduationRecord'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                });
            })
            ->when($filters['department_id'] ?? null, function (Builder $query, int $departmentId): void {
                $query->whereHas('currentSpecialization', fn (Builder $query) => $query->where('department_id', $departmentId));
            })
            ->when($filters['specialization_id'] ?? null, fn (Builder $query, int $id) => $query->where('current_specialization_id', $id));
    }

    private function graduationCounts(Collection $students, GraduationEligibilityAction $eligibilityAction): array
    {
        $approved = 0;
        $eligible = 0;
        $blocked = 0;

        foreach ($students as $student) {
            if ($student->graduationRecord) {
                $approved++;

                continue;
            }

            $eligibility = $eligibilityAction->execute($student);

            if ($eligibility['eligible']) {
                $eligible++;
            } else {
                $blocked++;
            }
        }

        return [
            'approved' => $approved,
            'eligible' => $eligible,
            'blocked' => $blocked,
            'total' => $approved + $eligible + $blocked,
        ];
    }
}
