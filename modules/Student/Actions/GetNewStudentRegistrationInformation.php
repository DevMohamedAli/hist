<?php

namespace Modules\Student\Actions;

use Modules\Academic\Models\Specialization;

class GetNewStudentRegistrationInformation
{
    public function execute(): array
    {
        return [
            'requirements' => [
                'الرقم الوطني',
                'المؤهل السابق المعتمد',
                'بيانات التواصل الحالية',
                'اختيار التخصص',
            ],
            'specializations' => Specialization::query()
                ->with('department:id,name')
                ->orderBy('name')
                ->get(['id', 'department_id', 'name', 'code', 'semesters_count'])
                ->map(fn (Specialization $specialization): array => [
                    'id' => $specialization->id,
                    'name' => $specialization->name,
                    'code' => $specialization->code,
                    'semesters_count' => $specialization->semesters_count,
                    'department' => $specialization->department ? [
                        'id' => $specialization->department->id,
                        'name' => $specialization->department->name,
                    ] : null,
                ])
                ->values(),
        ];
    }
}
