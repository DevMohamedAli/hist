<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Student\Models\CourseEnrollment;

class StudyGroup extends Model
{
    protected $fillable = [
        'specialization_id',
        'academic_semester_id',
        'semester_level',
        'group_name',
        'capacity'
    ];

    /**
     * Relation to Specialization
     */
    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    /**
     * Primary relation to AcademicSemester (used by controller withCount / with)
     */
    public function academicSemester(): BelongsTo
    {
        return $this->belongsTo(AcademicSemester::class, 'academic_semester_id');
    }

    /**
     * Alias for academicSemester() – keeps backward compatibility
     */
    public function semester(): BelongsTo
    {
        return $this->academicSemester();
    }

    /**
     * جلب المواد الأكاديمية المخصصة لهذا التخصص وهذا السمستر من جدول العلاقة الوسيط
     */
    public function courses()
    {
        return Course::whereHas('specializations', function ($query) {
            $query->where('specialization_id', $this->specialization_id)
                ->where('semester_level', $this->semester_level);
        })->get();
    }

    /**
     * Relation to CourseEnrollment (list of enrolled courses)
     */
    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function courseClasses()
    {
        return $this->hasMany(CourseClass::class);
    }

    public function students()
    {
        return $this->belongsToMany(\Modules\Student\Models\Student::class, 'course_enrollments', 'study_group_id', 'student_id')
            ->distinct(); // هذه الكلمة تمنع التكرار من جذوره في الداتابيز
    }
}
