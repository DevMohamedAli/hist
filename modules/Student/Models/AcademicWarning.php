<?php

namespace Modules\Student\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\AcademicSemester;

class AcademicWarning extends Model
{
    protected $fillable = [
        'student_id',
        'academic_semester_id',
        'type',
        'reason',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function semester()
    {
        return $this->belongsTo(AcademicSemester::class, 'academic_semester_id');
    }
}
