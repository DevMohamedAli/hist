<?php

namespace Modules\Student\Models;

use Database\Factories\StudentSemesterSummaryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSemesterSummary extends Model
{
    /** @use HasFactory<StudentSemesterSummaryFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'semester_id',
        'semester_gpa',
        'cumulative_gpa',
        'total_registered_units',
        'carried_courses_count',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function semester()
    {
        return $this->belongsTo(\Modules\Academic\Models\AcademicSemester::class);
    }
}
