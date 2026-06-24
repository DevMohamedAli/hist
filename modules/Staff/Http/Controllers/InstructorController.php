<?php

namespace Modules\Staff\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Academic\Models\Department;
use Modules\Qualification\Models\Qualification;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Staff\Models\Instructor;
use Modules\User\Models\User;

class InstructorController extends Controller
{
    public function index(): InertiaResponse
    {
        $instructors = Instructor::with(['department', 'qualifications'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Staff/Instructors/Index', [
            'instructors' => $instructors,
            'departments' => Department::all(['id', 'name']),
            'qualifications' => $this->qualificationOptions(),
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Staff/Instructors/Create', [
            'departments' => Department::all(['id', 'name']),
            'qualifications' => $this->qualificationOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'national_id' => 'required|string|size:12|unique:instructors,national_id',
            'email' => 'nullable|email|max:100|unique:instructors,email',
            'phone' => 'nullable|string|max:30',
            'academic_rank' => 'required|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'status' => 'required|in:Active,On_Leave,Suspended',
            'qualifications' => 'nullable|array',
            'qualifications.*.id' => 'nullable|integer|exists:qualifications,id',
            'qualifications.*.degree_name' => 'required|string|max:255',
            'qualifications.*.institution' => 'required|string|max:255',
        ]);

        $instructor = DB::transaction(function () use ($validated) {
            $instructor = Instructor::create(Arr::except($validated, 'qualifications'));
            $this->ensureInstructorUser($instructor);
            $qualificationIds = $this->resolveQualificationIds($validated['qualifications'] ?? []);

            $instructor->qualifications()->sync($qualificationIds);

            return $instructor->load('qualifications');
        });

        activity()
            ->causedBy($request->user())
            ->performedOn($instructor)
            ->withProperties([
                'attributes' => $instructor->toArray(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ])
            ->log('تم تسجيل عضو هيئة تدريس جديد');

        return redirect()->route('instructors.index')
            ->with('success', 'تمت إضافة عضو هيئة التدريس بنجاح.');
    }

    public function show(int $instructorId): InertiaResponse
    {
        $instructor = Instructor::with(['department', 'qualifications'])->findOrFail($instructorId);

        return Inertia::render('Staff/Instructors/Show', [
            'instructor' => $instructor,
        ]);
    }

    public function edit(int $instructorId): InertiaResponse
    {
        $instructor = Instructor::with('qualifications')->findOrFail($instructorId);

        return Inertia::render('Staff/Instructors/Edit', [
            'instructor' => $instructor,
            'departments' => Department::all(['id', 'name']),
        ]);
    }

    public function update(Request $request, int $instructorId): RedirectResponse
    {
        $instructor = Instructor::findOrFail($instructorId);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'national_id' => 'required|string|size:12|unique:instructors,national_id,'.$instructorId,
            'email' => 'nullable|email|max:100|unique:instructors,email,'.$instructorId,
            'phone' => 'nullable|string|max:30',
            'academic_rank' => 'required|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'status' => 'required|in:Active,On_Leave,Suspended',
            'qualifications' => 'nullable|array',
            'qualifications.*.id' => 'nullable|integer|exists:qualifications,id',
            'qualifications.*.degree_name' => 'required|string|max:255',
            'qualifications.*.institution' => 'required|string|max:255',
        ]);

        $old = $instructor->load('qualifications')->toArray();

        DB::transaction(function () use ($instructor, $validated) {
            $instructor->update(Arr::except($validated, 'qualifications'));
            $this->ensureInstructorUser($instructor);
            $qualificationIds = $this->resolveQualificationIds($validated['qualifications'] ?? []);

            $instructor->qualifications()->sync($qualificationIds);
        });

        $new = $instructor->fresh(['qualifications'])->toArray();

        activity()
            ->causedBy($request->user())
            ->performedOn($instructor)
            ->withProperties([
                'old' => $old,
                'attributes' => $new,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ])
            ->log('تم تحديث بيانات عضو هيئة التدريس');

        return redirect()->route('instructors.index')
            ->with('success', 'تم تحديث بيانات المحاضر بنجاح.');
    }

    public function destroy(int $instructorId): RedirectResponse
    {
        $instructor = Instructor::findOrFail($instructorId);
        $instructorName = $instructor->name;
        $old = $instructor->toArray();

        $instructor->delete();

        activity()
            ->causedBy(request()->user())
            ->performedOn($instructor)
            ->withProperties([
                'old' => $old,
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
            ])
            ->log('تم حذف عضو هيئة تدريس');

        return redirect()->route('instructors.index')
            ->with('success', "تم حذف المحاضر: {$instructorName} بنجاح.");
    }

    private function qualificationOptions(): Collection
    {
        return Qualification::query()
            ->orderBy('degree_name')
            ->orderBy('institution')
            ->get(['id', 'degree_name', 'institution'])
            ->unique(fn (Qualification $qualification): string => mb_strtolower(
                trim($qualification->degree_name).'|'.trim($qualification->institution),
            ))
            ->values();
    }

    private function resolveQualificationIds(array $qualifications): array
    {
        return collect($qualifications)
            ->map(function (array $qualification): int {
                if (! empty($qualification['id'])) {
                    return (int) $qualification['id'];
                }

                return Qualification::firstOrCreateByText(
                    $qualification['degree_name'],
                    $qualification['institution'],
                )->id;
            })
            ->unique()
            ->values()
            ->all();
    }

    private function ensureInstructorUser(Instructor $instructor): void
    {
        if ($instructor->user_id || ! $instructor->email) {
            return;
        }

        $user = User::query()->firstOrCreate(
            ['email' => $instructor->email],
            [
                'name' => $instructor->name,
                'password' => Hash::make(Str::random(32)),
            ],
        );

        if (! $user->hasRole('teacher')) {
            $user->assignRole('teacher');
        }

        $instructor->forceFill(['user_id' => $user->id])->save();
    }
}
