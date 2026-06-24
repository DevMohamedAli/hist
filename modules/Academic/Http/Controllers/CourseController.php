<?php

namespace Modules\Academic\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Shared\Http\Controllers\Controller;

class CourseController extends Controller
{
    /**
     * Display all courses with filters and pagination.
     */
    public function index(Request $request): InertiaResponse
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'department' => 'nullable|exists:departments,id',
            'specialization' => 'nullable|exists:specializations,id',
            'semester_level' => 'nullable|integer|min:1|max:12',
            'units' => 'nullable|integer|min:1|max:10',
            'has_practical' => 'nullable|boolean',
            'prerequisite_status' => 'nullable|in:any,with,without',
            'curriculum_status' => 'nullable|in:any,assigned,unassigned',
            'sort' => 'nullable|in:code,name,units,specializations,created_at',
            'direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|in:10,25,50,100',
        ]);

        $perPage = $filters['per_page'] ?? 25;
        $sort = $filters['sort'] ?? 'code';
        $direction = $filters['direction'] ?? 'asc';

        $query = Course::query()
            ->with(['specializations.department', 'prerequisites'])
            ->withCount(['specializations', 'prerequisites']);

        // Search by name or code
        if (! empty($filters['search'])) {
            $search = '%'.addcslashes($filters['search'], '%_').'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('code', 'like', $search);
            });
        }

        if (! empty($filters['department']) || ! empty($filters['specialization']) || ! empty($filters['semester_level'])) {
            $query->whereHas('specializations', function ($q) use ($filters) {
                if (! empty($filters['department'])) {
                    $q->whereHas('department', function ($departmentQuery) use ($filters) {
                        $departmentQuery->where('departments.id', $filters['department']);
                    });
                }

                if (! empty($filters['specialization'])) {
                    $q->where('specializations.id', $filters['specialization']);
                }

                if (! empty($filters['semester_level'])) {
                    $q->where('course_specialization.semester_level', (int) $filters['semester_level']);
                }
            });
        }

        // Filter by units
        if (! empty($filters['units'])) {
            $query->where('units', $filters['units']);
        }

        // Filter by has_practical
        if (isset($filters['has_practical'])) {
            $query->where('has_practical', (bool) $filters['has_practical']);
        }

        if (($filters['prerequisite_status'] ?? 'any') === 'with') {
            $query->has('prerequisites');
        } elseif (($filters['prerequisite_status'] ?? 'any') === 'without') {
            $query->doesntHave('prerequisites');
        }

        if (($filters['curriculum_status'] ?? 'any') === 'assigned') {
            $query->has('specializations');
        } elseif (($filters['curriculum_status'] ?? 'any') === 'unassigned') {
            $query->doesntHave('specializations');
        }

        match ($sort) {
            'name' => $query->orderBy('name', $direction),
            'units' => $query->orderBy('units', $direction)->orderBy('code'),
            'specializations' => $query->orderBy('specializations_count', $direction)->orderBy('code'),
            'created_at' => $query->orderBy('created_at', $direction),
            default => $query->orderBy('code', $direction),
        };

        $courses = $query
            ->paginate($perPage)
            ->withQueryString();

        // Transform for frontend (optional)
        $courses->getCollection()->transform(function ($course) {
            return $this->cleanUtf8($course);
        });

        $departments = Department::query()
            ->orderBy('name')
            ->get(['id', 'name']);
        $specializations = Specialization::query()
            ->with('department:id,name')
            ->orderBy('name')
            ->get(['id', 'department_id', 'name']);
        $unitOptions = Course::query()
            ->select('units')
            ->distinct()
            ->orderBy('units')
            ->pluck('units')
            ->values();
        $semesterLevels = Course::query()
            ->join('course_specialization', 'courses.id', '=', 'course_specialization.course_id')
            ->select('course_specialization.semester_level')
            ->distinct()
            ->orderBy('course_specialization.semester_level')
            ->pluck('course_specialization.semester_level')
            ->values();

        return Inertia::render('Academic/Courses/Index', [
            'courses' => $courses,
            'filters' => $filters,
            'departments' => $this->cleanUtf8($departments),
            'specializations' => $this->cleanUtf8($specializations),
            'unitOptions' => $unitOptions,
            'semesterLevels' => $semesterLevels,
        ]);
    }

    /**
     * Show the form for creating a new course.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('Academic/Courses/Create', [
            'specializations' => $this->cleanUtf8(Specialization::all(['id', 'name'])),
            'prerequisites' => $this->cleanUtf8(Course::all(['id', 'code', 'name'])),
        ]);
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:courses,code',
            'name' => 'required|string|max:100',
            'units' => 'required|integer|min:1|max:4',
            'has_practical' => 'required|boolean',
            'description' => 'nullable|string|max:500',
            'prerequisite_ids' => 'nullable|array',
            'prerequisite_ids.*' => 'exists:courses,id',
            'specializations' => 'required|array|min:1',
            'specializations.*.id' => 'required|exists:specializations,id',
            'specializations.*.semester_level' => 'required|integer|min:1|max:12',
        ]);

        $course = Course::create(Arr::except($validated, ['specializations', 'prerequisite_ids']));

        // Sync specializations (pivot)
        $syncData = [];
        foreach ($request->specializations as $spec) {
            $syncData[$spec['id']] = ['semester_level' => $spec['semester_level']];
        }
        $course->specializations()->sync($syncData);

        // Sync prerequisites (many-to-many)
        if (! empty($validated['prerequisite_ids'])) {
            $course->prerequisites()->sync($validated['prerequisite_ids']);
        }

        activity()
            ->causedBy($request->user())
            ->performedOn($course)
            ->withProperties([
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'course_id' => $course->id,
                'course_code' => $course->code,
                'specialization_ids' => collect($request->specializations)->pluck('id')->values()->all(),
            ])
            ->log('تم إنشاء مقرر دراسي');

        return redirect()->route('academic.courses.index')
            ->with('success', 'تم إضافة المقرر الدراسي بنجاح.');
    }

    /**
     * Display the specified course.
     */
    public function show(int $courseId): InertiaResponse
    {
        $course = Course::with(['specializations.department', 'prerequisites'])->findOrFail($courseId);

        return Inertia::render('Academic/Courses/Show', [
            'course' => $course,
        ]);
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(int $courseId): InertiaResponse
    {
        $course = Course::with(['specializations.department', 'prerequisites'])->findOrFail($courseId);

        // Add prerequisite_ids array to the course object for the frontend
        $course->prerequisite_ids = $course->prerequisites->pluck('id')->toArray();

        $specializations = Specialization::all(['id', 'name']);
        $prerequisites = Course::where('id', '!=', $courseId)->get(['id', 'name', 'code']);

        return Inertia::render('Academic/Courses/Edit', [
            'course' => $this->cleanUtf8($course),
            'specializations' => $this->cleanUtf8($specializations),
            'prerequisites' => $this->cleanUtf8($prerequisites),
        ]);
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, int $courseId): RedirectResponse
    {
        $course = Course::findOrFail($courseId);

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:courses,code,'.$courseId,
            'name' => 'required|string|max:100',
            'units' => 'required|integer|min:1|max:4',
            'has_practical' => 'required|boolean',
            'description' => 'nullable|string|max:500',
            'prerequisite_ids' => 'nullable|array',
            'prerequisite_ids.*' => 'exists:courses,id|not_in:'.$courseId,
            'specializations' => 'required|array|min:1',
            'specializations.*.id' => 'required|exists:specializations,id',
            'specializations.*.semester_level' => 'required|integer|min:1|max:12',
        ]);

        $old = $course->toArray();
        $course->update(Arr::except($validated, ['specializations', 'prerequisite_ids']));
        $new = $course->fresh()->toArray();

        // Sync specializations
        $syncData = [];
        foreach ($request->specializations as $spec) {
            $syncData[$spec['id']] = ['semester_level' => $spec['semester_level']];
        }
        $course->specializations()->sync($syncData);

        // Sync prerequisites (many-to-many)
        $course->prerequisites()->sync($validated['prerequisite_ids'] ?? []);

        activity()
            ->causedBy($request->user())
            ->performedOn($course)
            ->withProperties([
                'old' => $old,
                'attributes' => $new,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ])
            ->log('تم تحديث مقرر دراسي');

        return redirect()->route('academic.courses.index')
            ->with('success', 'تم تحديث بيانات المقرر الدراسي بنجاح.');
    }

    /**
     * Delete the specified course.
     */
    public function destroy(int $courseId): RedirectResponse
    {
        $course = Course::findOrFail($courseId);
        $courseName = $course->name;
        $old = $course->toArray();

        $course->delete();

        activity()
            ->causedBy(request()->user())
            ->performedOn($course)
            ->withProperties([
                'old' => $old,
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
            ])
            ->log('تم حذف مقرر دراسي');

        return redirect()->route('academic.courses.index')
            ->with('success', "تم حذف المقرر: {$courseName} بنجاح.");
    }
}
