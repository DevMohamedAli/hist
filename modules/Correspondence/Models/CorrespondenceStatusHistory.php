<?php

namespace Modules\Correspondence\Models;

use Illuminate\Database\Eloquent\Model;

class CorrespondenceStatusHistory extends Model
{
    public $updated_at = null;

    protected $fillable = [
        'correspondence_id',
        'actor_user_id',
        'from_status',
        'to_status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
