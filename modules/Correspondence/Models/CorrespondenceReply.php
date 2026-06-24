<?php

namespace Modules\Correspondence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\User\Models\User;

class CorrespondenceReply extends Model
{
    protected $fillable = [
        'correspondence_id',
        'parent_reply_id',
        'sender_user_id',
        'body',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
