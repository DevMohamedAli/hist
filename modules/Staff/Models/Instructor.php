<?php

namespace Modules\Staff\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // adjust namespace if needed
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\Department;
use Modules\Qualification\Models\Qualification;
use Modules\User\Models\User; // adjust namespace if needed
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Instructor extends Model
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
        'user_id',
        'employee_id',
        'department_id',
        'name',
        'national_id',
        'email',
        'phone',
        'academic_rank',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => 'string', // or use an enum later
    ];

    /**
     * Relationship: the user account (if linked)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: academic department
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function courseClasses(): HasMany
    {
        return $this->hasMany(CourseClass::class, 'instructor_id');
    }

    public function qualifications(): MorphToMany
    {
        return $this->morphToMany(Qualification::class, 'qualifiable', 'qualificationables')
            ->withTimestamps();
    }
}
