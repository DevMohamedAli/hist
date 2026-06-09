<?php

namespace Modules\Import\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\User\Models\User;

class ImportJob extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'file_name',
        'file_path',
        'original_columns',
        'header_row_index',
        'mapping',
        'status',
        'total_rows',
        'processed_rows',
        'errors',
    ];

    protected $casts = [
        'original_columns' => 'array',
        'mapping' => 'array',
        'errors' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
