<?php

namespace Modules\Student\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\CourseClass;

class StudentEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'semester_work_grade',
        'final_exam_grade',
        'total_grade',
        'grade_evaluation',
    ];

    protected $casts = [
        'semester_work_grade' => 'decimal:2',
        'final_exam_grade' => 'decimal:2',
        'total_grade' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }
}
