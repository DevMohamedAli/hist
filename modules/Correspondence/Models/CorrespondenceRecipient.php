<?php

namespace Modules\Correspondence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Correspondence\Enums\CorrespondenceRecipientType;
use Modules\User\Models\User;

class CorrespondenceRecipient extends Model
{
    protected $fillable = [
        'correspondence_id',
        'user_id',
        'recipient_type',
        'delivery_status',
        'received_at',
        'read_at',
        'responded_at',
        'action_status',
    ];

    protected $casts = [
        'recipient_type' => CorrespondenceRecipientType::class,
        'received_at' => 'datetime',
        'read_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function correspondence(): BelongsTo
    {
        return $this->belongsTo(Correspondence::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
