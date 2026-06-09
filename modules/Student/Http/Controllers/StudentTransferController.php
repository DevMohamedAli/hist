<?php

namespace Modules\Student\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Student\Models\DepartmentTransfer;
use Modules\Student\Models\Student;

class StudentTransferController extends Controller
{
    public function store(Request $request, int $studentId): RedirectResponse
    {
        $validated = $request->validate([
            'to_specialization_id' => 'required|exists:specializations,id',
            'transfer_date' => 'required|date',
            'approval_reference' => 'required|string',
        ]);

        $student = Student::findOrFail($studentId);

        if ((int) $student->current_semester_level > 2) {
            return redirect()->back()->withErrors([
                'transfer' => 'لا يسمح بنقل الطالب بعد إكمال أكثر من فصلين دراسيين في التخصص الأصلي.',
            ]);
        }

        if ((int) $student->current_specialization_id === (int) $validated['to_specialization_id']) {
            return redirect()->back()->withErrors([
                'to_specialization_id' => 'التخصص الجديد يجب أن يختلف عن التخصص الحالي.',
            ]);
        }

        // المادة (16): يجوز للطالب الانتقال لمرة واحدة فقط طيلة فترة الدراسة بالمعهد
        $hasTransferredBefore = DepartmentTransfer::where('student_id', $student->id)->exists();
        if ($hasTransferredBefore) {
            return redirect()->back()->withErrors([
                'transfer' => 'فشل الطلب: غير مسموح بنقل الطالب، حيث سبق له الانتقال مسبقاً (مادة 16).',
            ]);
        }

        // تسجيل عملية الانتقال وتحديث تخصص الطالب
        $transfer = DepartmentTransfer::create([
            'student_id' => $student->id,
            'from_specialization_id' => $student->current_specialization_id,
            'to_specialization_id' => $validated['to_specialization_id'],
            'transfer_date' => $validated['transfer_date'],
            'approval_reference' => $validated['approval_reference'],
        ]);

        $student->update([
            'current_specialization_id' => $validated['to_specialization_id'],
        ]);

        $fromSpec = \Modules\Academic\Models\Specialization::find($transfer->from_specialization_id);
        $toSpec = \Modules\Academic\Models\Specialization::find($transfer->to_specialization_id);

        activity()
            ->causedBy($request->user())
            ->performedOn($student)
            ->withProperties([
                'from_specialization' => $fromSpec?->name,
                'to_specialization' => $toSpec?->name,
                'student_id' => $student->id,
                'registration_number' => $student->registration_number,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'from_specialization_id' => $transfer->from_specialization_id,
                'to_specialization_id' => $transfer->to_specialization_id,
            ])
            ->log('تم نقل الطالب إلى تخصص آخر');

        return redirect()->back()->with('success', 'تم نقل الطالب إلى التخصص الجديد بنجاح (مادة 16).');
    }
}
