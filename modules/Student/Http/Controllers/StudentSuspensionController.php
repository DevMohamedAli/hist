<?php

namespace Modules\Student\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Student\Models\RegistrationSuspension;
use Modules\Student\Models\Student;

class StudentSuspensionController extends Controller
{
    public function store(Request $request, int $studentId): RedirectResponse
    {
        $validated = $request->validate([
            'semester_id' => 'required|exists:academic_semesters,id',
            'suspension_reason' => 'required|string',
            'approval_date' => 'required|date',
        ]);

        $student = Student::findOrFail($studentId);

        if (RegistrationSuspension::where('student_id', $student->id)->exists()) {
            return redirect()->back()->withErrors([
                'semester_id' => 'لا يسمح بإيقاف القيد لأكثر من فصل دراسي واحد خلال مدة الدراسة.',
            ]);
        }

        if ($student->status === 'Suspended') {
            return redirect()->back()->withErrors([
                'status' => 'الطالب موقوف القيد بالفعل.',
            ]);
        }

        // تسجيل الإيقاف وتحديث حالة الطالب
        $suspension = RegistrationSuspension::create([
            'student_id' => $student->id,
            'semester_id' => $validated['semester_id'],
            'suspension_reason' => $validated['suspension_reason'],
            'approval_date' => $validated['approval_date'],
        ]);

        $student->update(['status' => 'Suspended']);

        $semester = \Modules\Academic\Models\AcademicSemester::find($validated['semester_id']);

        activity()
            ->causedBy($request->user())
            ->performedOn($student)
            ->withProperties([
                'semester' => $semester?->code,
                'student_id' => $student->id,
                'registration_number' => $student->registration_number,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'suspension_id' => $suspension->id,
                'semester_id' => $validated['semester_id'],
            ])
            ->log('تم إيقاف قيد الطالب');

        return redirect()->back()->with('success', 'تم إيقاف قيد الطالب فصلياً بنجاح (مادة 13).');
    }
}
