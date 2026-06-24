<?php

namespace Modules\Correspondence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\User\Models\User;

class CorrespondenceAttachment extends Model
{
    protected $fillable = [
        'correspondence_id',
        'original_filename',
        'stored_filename',
        'storage_disk',
        'mime_type',
        'file_size',
        'uploaded_by',
        'checksum',
        'visibility',
        'description',
    ];

    public function correspondence(): BelongsTo
    {
        return $this->belongsTo(Correspondence::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
