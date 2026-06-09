<?php

namespace Modules\Academic\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Shared\Http\Controllers\Controller;

class SpecializationController extends Controller
{
    /**
     * Display all specializations
     */
    public function index(): InertiaResponse
    {
        return Inertia::render('Academic/Specializations/Index', [
            'specializations' => $this->cleanUtf8(Specialization::with('department')->get()),
            'departments' => $this->cleanUtf8(Department::all()),
        ]);
    }

    /**
     * Show the create form for a new specialization
     */
    public function create(): InertiaResponse
    {
        $departments = Department::all();

        return Inertia::render('Academic/Specializations/Create', [
            'departments' => $this->cleanUtf8($departments),
        ]);
    }

    /**
     * Store a newly created specialization
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20|unique:specializations,code',
            'description' => 'nullable|string|max:500',
            'semesters_count' => 'required|integer|min:1|max:12',
        ]);

        $specialization = Specialization::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($specialization)
            ->withProperties([
                'attributes' => $specialization->toArray(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ])
            ->log('تم إنشاء شعبة دراسية جديدة');

        return redirect()->route('academic.specializations.index')->with('success', 'تمت إضافة الشعبة بنجاح.');
    }

    /**
     * Display the specified specialization
     */
    public function show(int $specializationId): InertiaResponse
    {
        $specialization = Specialization::with('department')->findOrFail($specializationId);

        return Inertia::render('Academic/Specializations/Show', [
            'specialization' => $this->cleanUtf8($specialization),
        ]);
    }

    /**
     * Show the form for editing the specified specialization
     */
    public function edit(int $specializationId): InertiaResponse
    {
        $specialization = Specialization::findOrFail($specializationId);
        $departments = Department::all();

        return Inertia::render('Academic/Specializations/Edit', [
            'specialization' => $this->cleanUtf8($specialization),
            'departments' => $this->cleanUtf8($departments),
        ]);
    }

    /**
     * Update the specified specialization
     */
    public function update(Request $request, int $specializationId): RedirectResponse
    {
        $specialization = Specialization::findOrFail($specializationId);

        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20|unique:specializations,code,' . $specializationId,
            'description' => 'nullable|string|max:500',
            'semesters_count' => 'required|integer|min:1|max:12',
        ]);

        $old = $specialization->toArray();
        $specialization->update($validated);
        $new = $specialization->fresh()->toArray();

        activity()
            ->causedBy($request->user())
            ->performedOn($specialization)
            ->withProperties([
                'old' => $old,
                'attributes' => $new,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ])
            ->log('تم تحديث بيانات الشعبة الدراسية');

        return redirect()->route('academic.specializations.index')->with('success', 'تم تحديث بيانات الشعبة بنجاح.');
    }

    /**
     * Delete the specified specialization
     */
    public function destroy(Request $request, int $specializationId): RedirectResponse
    {
        $specialization = Specialization::findOrFail($specializationId);
        $specializationName = $specialization->name;
        $old = $specialization->toArray();

        $specialization->delete();

        activity()
            ->causedBy($request->user())
            ->performedOn($specialization)
            ->withProperties([
                'old' => $old,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ])
            ->log('تم حذف شعبة دراسية');

        return redirect()->route('academic.specializations.index')->with('success', "تم حذف الشعبة: {$specializationName} بنجاح.");
    }
}
