<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academic\Database\Factories\CourseFactory;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Course extends Model
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
        'code',
        'name',
        'units',
        'has_practical',
        'description',
    ];

    protected $casts = [
        'units' => 'integer',
        'has_practical' => 'boolean',
    ];

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, 'course_specialization')
            ->withPivot('semester_level') // تمرير مستوى السمستر من جدول العلاقة الوسيط
            ->withTimestamps();
    }

    /**
     * Get the prerequisites for this course (many-to-many).
     */
    public function prerequisites()
    {
        return $this->belongsToMany(self::class, 'course_prerequisites', 'course_id', 'prerequisite_course_id');
    }

    /**
     * Get courses that have this course as a prerequisite.
     */
    public function usedAsPrerequisiteFor()
    {
        return $this->belongsToMany(self::class, 'course_prerequisites', 'prerequisite_course_id', 'course_id');
    }

    public function classes(): HasMany
    {
        return $this->hasMany(CourseClass::class);
    }

    protected static function newFactory(): Factory
    {
        return CourseFactory::new();
    }
}
