<?php

namespace Modules\Graduation\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Graduation\Models\GraduationRecord;
use Modules\Student\Models\Student;
use Modules\User\Models\User;

class ApproveGraduationAction
{
    public function execute(Student $student, User $approver, ?string $notes = null): GraduationRecord
    {
        return DB::transaction(function () use ($student, $approver, $notes): GraduationRecord {
            $student->refresh();
            $existing = GraduationRecord::query()->where('student_id', $student->id)->first();

            if ($existing) {
                return $existing;
            }

            $eligibility = app(GraduationEligibilityAction::class)->execute($student);

            if (! $eligibility['eligible']) {
                throw ValidationException::withMessages([
                    'student' => $eligibility['reasons'] ?: ['الطالب غير مؤهل للتخرج.'],
                ]);
            }

            $record = GraduationRecord::create([
                'student_id' => $student->id,
                'specialization_id' => $student->current_specialization_id,
                'approved_by' => $approver->id,
                'certificate_number' => $this->nextCertificateNumber(),
                'graduation_date' => now()->toDateString(),
                'cgpa' => $eligibility['cgpa'],
                'total_units' => $eligibility['total_units'],
                'status' => 'approved',
                'notes' => $notes,
            ]);

            $student->update(['status' => 'Graduated']);

            activity()
                ->causedBy($approver)
                ->performedOn($student)
                ->withProperties([
                    'graduation_record_id' => $record->id,
                    'certificate_number' => $record->certificate_number,
                ])
                ->log('تم اعتماد تخرج الطالب');

            return $record;
        });
    }

    private function nextCertificateNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "GR-{$year}-";
        $latest = GraduationRecord::query()
            ->where('certificate_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('certificate_number')
            ->value('certificate_number');
        $next = $latest ? ((int) substr($latest, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
