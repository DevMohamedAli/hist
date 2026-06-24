<?php

namespace Modules\Correspondence\Models;

use Illuminate\Database\Eloquent\Model;

class CorrespondenceCategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'is_student_available',
        'requires_approval',
        'allowed_destination_roles',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_student_available' => 'boolean',
        'requires_approval' => 'boolean',
        'allowed_destination_roles' => 'array',
    ];
}
