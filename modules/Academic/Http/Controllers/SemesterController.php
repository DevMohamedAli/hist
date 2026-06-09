<?php

namespace Modules\Academic\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Academic\Models\AcademicSemester;
use Modules\Shared\Http\Controllers\Controller;

class SemesterController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Academic/Semesters/Index', [
            'semesters' => $this->cleanUtf8(AcademicSemester::orderBy('year', 'desc')->get()),
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Academic/Semesters/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSemester($request);
        $semester = AcademicSemester::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($semester)
            ->withProperties([
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'semester_id' => $semester->id,
                'code' => $semester->code,
                'season' => $semester->season,
                'year' => $semester->year,
            ])
            ->log('تم إنشاء فصل دراسي جديد');

        return redirect()->route('academic.semesters.index')
            ->with('success', 'تم إعداد الفصل الدراسي وجدولة المخطط الزمني بنجاح.');
    }

    public function show(int $semesterId): InertiaResponse
    {
        return Inertia::render('Academic/Semesters/Show', [
            'semester' => $this->cleanUtf8(AcademicSemester::findOrFail($semesterId)),
        ]);
    }

    public function edit(int $semesterId): InertiaResponse
    {
        return Inertia::render('Academic/Semesters/Edit', [
            'semester' => $this->cleanUtf8(AcademicSemester::findOrFail($semesterId)),
        ]);
    }

    public function update(Request $request, int $semesterId): RedirectResponse
    {
        $semester = AcademicSemester::findOrFail($semesterId);
        $validated = $this->validateSemester($request, $semesterId);

        $semester->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($semester)
            ->withProperties([
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'semester_id' => $semester->id,
                'code' => $semester->code,
                'changed' => array_keys($validated),
            ])
            ->log('تم تحديث بيانات الفصل الدراسي');

        return redirect()->route('academic.semesters.index')
            ->with('success', 'تم تحديث بيانات الفصل الدراسي والتقويم الزمني بنجاح.');
    }

    public function destroy(Request $request, int $semesterId): RedirectResponse
    {
        $semester = AcademicSemester::findOrFail($semesterId);
        $semesterCode = $semester->code;

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'semester_id' => $semester->id,
                'semester_code' => $semester->code,
                'season' => $semester->season,
                'year' => $semester->year,
            ])
            ->log('تم حذف فصل دراسي');

        $semester->delete();

        return redirect()->route('academic.semesters.index')
            ->with('success', "تم حذف الفصل: {$semesterCode} بنجاح.");
    }

    private function validateSemester(Request $request, ?int $semesterId = null): array
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20|unique:academic_semesters,code' . ($semesterId ? ',' . $semesterId : ''),
            'season' => 'required|in:Spring,Fall',
            'year' => 'required|integer|min:2020|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'registration_start' => 'required|date|after_or_equal:start_date',
            'registration_end' => 'required|date|after_or_equal:registration_start',
            'final_exams_start' => 'required|date|after_or_equal:start_date|before_or_equal:end_date',
        ], [
            'season.in' => 'الفصول الدراسية المعتمدة هي الربيع والخريف فقط.',
            'end_date.required' => 'تاريخ نهاية الفصل مطلوب للتحقق من مدة الفصل.',
            'registration_start.required' => 'تاريخ بداية التسجيل مطلوب.',
            'registration_end.required' => 'تاريخ نهاية التسجيل مطلوب.',
            'final_exams_start.required' => 'تاريخ بداية الامتحانات النهائية مطلوب.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $season = $request->input('season');
            $start = Carbon::parse($request->input('start_date'))->startOfDay();
            $end = Carbon::parse($request->input('end_date'))->startOfDay();
            $registrationStart = Carbon::parse($request->input('registration_start'))->startOfDay();
            $registrationEnd = Carbon::parse($request->input('registration_end'))->startOfDay();
            $finalExamsStart = Carbon::parse($request->input('final_exams_start'))->startOfDay();

            if ($start->diffInWeeks($end) > 20 || $start->copy()->addWeeks(20)->lt($end)) {
                $validator->errors()->add('end_date', 'مدة الفصل الدراسي لا يجوز أن تتجاوز 20 أسبوعا.');
            }

            if (! $registrationStart->equalTo($start) || $registrationEnd->gt($start->copy()->addWeeks(2))) {
                $validator->errors()->add('registration_end', 'يجب أن تكون أول أسبوعين من الفصل مخصصين للتسجيل.');
            }

            if ($finalExamsStart->lt($end->copy()->subWeeks(2))) {
                $validator->errors()->add('final_exams_start', 'يجب أن تبدأ الامتحانات النهائية النظرية في آخر أسبوعين من الفصل.');
            }

            if ($season === 'Fall' && ($start->month !== 9 || $end->month !== 1)) {
                $validator->errors()->add('start_date', 'فصل الخريف يبدأ في سبتمبر وينتهي في يناير.');
            }

            if ($season === 'Spring' && ($start->month !== 2 || $start->day < 8 || $end->month !== 7 || $end->day > 7)) {
                $validator->errors()->add('start_date', 'فصل الربيع يبدأ في الأسبوع الثاني من فبراير وينتهي في الأسبوع الأول من يوليو.');
            }
        });

        return $validator->validate();
    }
}
