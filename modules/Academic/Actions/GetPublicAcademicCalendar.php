<?php

namespace Modules\Academic\Actions;

use Illuminate\Support\Collection;
use Modules\Academic\Models\AcademicSemester;

class GetPublicAcademicCalendar
{
    public function execute(): Collection
    {
        return AcademicSemester::query()
            ->orderByDesc('year')
            ->orderByDesc('start_date')
            ->limit(6)
            ->get()
            ->map(fn (AcademicSemester $semester): array => [
                'id' => $semester->id,
                'code' => $semester->code,
                'season' => $semester->season,
                'year' => $semester->year,
                'start_date' => $semester->start_date?->toDateString(),
                'end_date' => $semester->end_date?->toDateString(),
                'registration_start' => $semester->registration_start?->toDateString(),
                'registration_end' => $semester->registration_end?->toDateString(),
                'final_exams_start' => $semester->final_exams_start?->toDateString(),
            ]);
    }
}
