<?php

namespace Modules\Student\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\StudyGroup;
use Modules\Student\Support\AcademicRegulation;

class CourseEnrollment extends Model
{
    protected $fillable = [
        'student_id',
        'study_group_id',
        'class_id',
        'course_id',
        'raw_semester_work',
        'raw_final_exam',
        'total_mark',
        'is_carried',
        'grade_evaluation',
        'status',
    ];

    protected $casts = [
        'raw_semester_work' => 'float',
        'raw_final_exam' => 'float',
        'total_mark' => 'float',
        'is_carried' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function studyGroup(): BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public static function evaluationFor(float $total): string
    {
        return AcademicRegulation::evaluationLabel($total);
    }
}
