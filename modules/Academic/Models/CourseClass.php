<?php

namespace Modules\Academic\Models;

use Database\Factories\CourseClassFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Staff\Models\Instructor;
use Modules\Student\Models\CourseEnrollment;

class CourseClass extends Model
{
    /** @use HasFactory<CourseClassFactory> */
    use HasFactory;

    protected $fillable = [
        'course_id',
        'semester_id',
        'study_group_id',
        'instructor_id',
        'group_name',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(AcademicSemester::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function studyGroup(): BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class, 'class_id');
    }
}
