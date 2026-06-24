<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academic\Database\Factories\SpecializationFactory;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @method static \Illuminate\Database\Eloquent\Builder query()
 * @mixin \Eloquent
 */
class Specialization extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /** @use HasFactory<SpecializationFactory> */
    use HasFactory;

    protected $fillable = ['department_id', 'name', 'code', 'description', 'semesters_count'];

    /**
     * The attributes that should be cast.
     */

    /**
     * Get the department that owns the specialization.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * العلاقة مع المقررات الأكاديمية المتعددة المدرجة في خطة التخصص
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_specialization')
            ->withPivot('semester_level')
            ->withTimestamps();
    }

    public function studyGroups(): HasMany
    {
        return $this->hasMany(StudyGroup::class);
    }
}
