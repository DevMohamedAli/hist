<?php

namespace Modules\Student\Models;

use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(\Modules\Academic\Models\AcademicSemester::class, 'academic_semester_id');
    }
}
