<?php

namespace Modules\Academic\Models;

use Database\Factories\AcademicSemesterFactory;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSemester extends Model
{
    /** @use HasFactory<AcademicSemesterFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'season',
        'year',
        'start_date',
        'end_date',
        'registration_start',
        'registration_end',
        'final_exams_start',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_start' => 'date',
        'registration_end' => 'date',
        'final_exams_start' => 'date',
    ];

    public function registrationIsOpen(?CarbonInterface $date = null): bool
    {
        if (! $this->registration_start || ! $this->registration_end) {
            return false;
        }

        $date ??= now();

        return $date->betweenIncluded(
            $this->registration_start->copy()->startOfDay(),
            $this->registration_end->copy()->endOfDay(),
        );
    }

    public static function openForRegistration(?CarbonInterface $date = null): ?self
    {
        $date ??= now();

        return self::query()
            ->whereDate('registration_start', '<=', $date->toDateString())
            ->whereDate('registration_end', '>=', $date->toDateString())
            ->orderByDesc('year')
            ->orderByDesc('registration_start')
            ->first();
    }

    public function classes(): HasMany
    {
        return $this->hasMany(CourseClass::class, 'semester_id');
    }

    public function studyGroups(): HasMany
    {
        return $this->hasMany(StudyGroup::class, 'academic_semester_id');
    }
}
