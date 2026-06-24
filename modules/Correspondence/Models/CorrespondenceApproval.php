<?php

namespace Modules\Correspondence\Models;

use Illuminate\Database\Eloquent\Model;

class CorrespondenceApproval extends Model
{
    protected $fillable = [
        'correspondence_id',
        'approver_user_id',
        'decision',
        'notes',
        'content_hash',
        'ip_address',
        'user_agent',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];
}
