<?php

namespace Modules\Student\Actions;

use Modules\Academic\Models\AcademicSemester;

class GetPublicRegistrationStatus
{
    public function execute(): array
    {
        $semester = AcademicSemester::openForRegistration();

        return [
            'is_open' => $semester !== null,
            'registration_url' => route('students.create'),
            'semester' => $semester ? [
                'id' => $semester->id,
                'code' => $semester->code,
                'registration_start' => $semester->registration_start?->toDateString(),
                'registration_end' => $semester->registration_end?->toDateString(),
            ] : null,
        ];
    }
}
