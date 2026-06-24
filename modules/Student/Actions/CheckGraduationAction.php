<?php

namespace Modules\Student\Actions;

use Modules\Graduation\Actions\GraduationEligibilityAction;
use Modules\Student\Models\Student;

class CheckGraduationAction
{
    public function execute(Student $student): array
    {
        $approvedRecord = $student->graduationRecord()->first();

        if ($approvedRecord) {
            return [
                'is_graduated' => true,
                'eligible' => true,
                'message' => 'Graduation has already been officially approved.',
                'certificate_number' => $approvedRecord->certificate_number,
                'reasons' => [],
            ];
        }

        $eligibility = app(GraduationEligibilityAction::class)->execute($student);

        return [
            'is_graduated' => false,
            'eligible' => (bool) $eligibility['eligible'],
            'message' => $eligibility['eligible']
                ? 'Student is eligible for graduation and awaits official approval.'
                : 'Student is not eligible for graduation yet.',
            'reasons' => $eligibility['reasons'],
            'cgpa' => $eligibility['cgpa'],
            'total_units' => $eligibility['total_units'],
            'missing_courses_count' => count($eligibility['missing_courses']),
            'failed_courses_count' => count($eligibility['failed_courses']),
        ];
    }
}
