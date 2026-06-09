<?php

namespace Modules\Import\Models;

use Illuminate\Database\Eloquent\Model;

class ImportMappingTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'name',
        'mapping',
    ];

    protected $casts = [
        'mapping' => 'array',
    ];
}
