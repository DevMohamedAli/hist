<?php

namespace Modules\Student\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Academic\Models\StudyGroup;
use Modules\Academic\Models\Specialization;
use Modules\Graduation\Models\GraduationRecord;
use Modules\Qualification\Models\Qualification;
use Modules\Student\Database\Factories\StudentFactory;
use Modules\User\Models\User;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property string $registration_number
 * @property string $full_name
 * @property string $status
 * @property int|null $current_semester_level
 * @property int|null $current_specialization_id
 * @property-read Specialization|null $currentSpecialization
 * @property-read GraduationRecord|null $graduationRecord
 * @method static \Illuminate\Database\Eloquent\Builder query()
 * @mixin \Eloquent
 */
class Student extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    use HasFactory;

    protected $fillable = [
        'registration_number',
        'user_id',
        'full_name',
        'national_id',
        'gender',
        'nationality',
        'birth_date',
        'mobile',
        'admission_date',
        'qualification',
        'qualification_id',
        'current_specialization_id',
        'current_study_group_id',
        'current_semester_level',
        'status',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function classEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(StudentSemesterSummary::class);
    }

    public function warnings(): HasMany
    {
        return $this->hasMany(AcademicWarning::class);
    }

    public function graduationRecord(): HasOne
    {
        return $this->hasOne(GraduationRecord::class);
    }

    public function suspensions(): HasMany
    {
        return $this->hasMany(RegistrationSuspension::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(DepartmentTransfer::class);
    }

    public function currentSpecialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class, 'current_specialization_id');
    }

    public function currentStudyGroup(): BelongsTo
    {
        return $this->belongsTo(StudyGroup::class, 'current_study_group_id');
    }

    public function selectedQualification(): BelongsTo
    {
        return $this->belongsTo(Qualification::class, 'qualification_id');
    }

    public function qualifications(): MorphMany
    {
        return $this->morphMany(Qualification::class, 'qualifiable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): Factory
    {
        return StudentFactory::new();
    }
}
