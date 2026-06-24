<?php

namespace Modules\Graduation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\Specialization;
use Modules\Student\Models\Student;
use Modules\User\Models\User;

/**
 * @property int $id
 * @property int $student_id
 * @property int $specialization_id
 * @property int|null $approved_by
 * @property string $certificate_number
 * @property string|null $graduation_date
 * @property float $cgpa
 * @property int $total_units
 * @property string $status
 * @property string|null $notes
 * @property-read Student $student
 * @property-read Specialization $specialization
 * @property-read User|null $approver
 *
 * @method static \Illuminate\Database\Eloquent\Builder query()
 *
 * @mixin \Eloquent
 */
class GraduationRecord extends Model
{
    protected $fillable = [
        'student_id',
        'specialization_id',
        'approved_by',
        'certificate_number',
        'graduation_date',
        'cgpa',
        'total_units',
        'status',
        'notes',
    ];

    protected $casts = [
        'graduation_date' => 'date',
        'cgpa' => 'float',
        'total_units' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
