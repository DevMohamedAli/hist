<?php

namespace Modules\Student\Models;

use Database\Factories\RegistrationSuspensionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationSuspension extends Model
{
    /** @use HasFactory<RegistrationSuspensionFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'semester_id',
        'suspension_reason',
        'approval_date',
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
