<?php

namespace Modules\Academic\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Academic\Models\Department;
use Modules\Shared\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    /**
     * Display all departments
     */
    public function index(): InertiaResponse
    {
        return Inertia::render('Academic/Departments/Index', [
            'departments' => $this->cleanUtf8(Department::with('specializations')->get()),
        ]);
    }

    /**
     * Show the create form for a new department
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('Academic/Departments/Create');
    }

    /**
     * Store a newly created department
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:departments,name',
            'code' => 'nullable|string|max:20|unique:departments,code',
            'description' => 'nullable|string|max:500',
        ]);

        $department = Department::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($department)
            ->withProperties([
                'attributes' => $department->toArray(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ])
            ->log('تم إنشاء قسم علمي جديد');

        return redirect()->route('academic.departments.index')->with('success', 'تم إنشاء القسم العلمي بنجاح.');
    }

    /**
     * Display the specified department
     */
    public function show(int $departmentId): InertiaResponse
    {
        $department = Department::with('specializations')->findOrFail($departmentId);

        return Inertia::render('Academic/Departments/Show', [
            'department' => $this->cleanUtf8($department),
        ]);
    }

    /**
     * Show the form for editing the specified department
     */
    public function edit(int $departmentId): InertiaResponse
    {
        $department = Department::findOrFail($departmentId);

        return Inertia::render('Academic/Departments/Edit', [
            'department' => $this->cleanUtf8($department),
        ]);
    }

    /**
     * Update the specified department
     */
    public function update(Request $request, int $departmentId): RedirectResponse
    {
        $department = Department::findOrFail($departmentId);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:departments,name,' . $departmentId,
            'code' => 'nullable|string|max:20|unique:departments,code,' . $departmentId,
            'description' => 'nullable|string|max:500',
        ]);

        $old = $department->toArray();
        $department->update($validated);
        $new = $department->fresh()->toArray();

        activity()
            ->causedBy($request->user())
            ->performedOn($department)
            ->withProperties([
                'old' => $old,
                'attributes' => $new,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ])
            ->log('تم تحديث بيانات القسم العلمي');

        return redirect()->route('academic.departments.index')->with('success', 'تم تحديث بيانات القسم العلمي بنجاح.');
    }

    /**
     * Delete the specified department
     */
    public function destroy(Request $request, int $departmentId): RedirectResponse
    {
        $department = Department::findOrFail($departmentId);
        $departmentName = $department->name;
        $old = $department->toArray();

        $department->delete();

        activity()
            ->causedBy($request->user())
            ->performedOn($department)
            ->withProperties([
                'old' => $old,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ])
            ->log('تم حذف قسم علمي');

        return redirect()->route('academic.departments.index')->with('success', "تم حذف القسم: {$departmentName} بنجاح.");
    }
}
