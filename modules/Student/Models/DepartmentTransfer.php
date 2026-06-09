<?php

namespace Modules\Student\Models;

use Database\Factories\DepartmentTransferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentTransfer extends Model
{
    /** @use HasFactory<DepartmentTransferFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'from_specialization_id',
        'to_specialization_id',
        'transfer_date',
        'approval_reference',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fromSpecialization()
    {
        return $this->belongsTo(\Modules\Academic\Models\Specialization::class, 'from_specialization_id');
    }

    public function toSpecialization()
    {
        return $this->belongsTo(\Modules\Academic\Models\Specialization::class, 'to_specialization_id');
    }
}
