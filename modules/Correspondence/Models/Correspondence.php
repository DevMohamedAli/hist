<?php

namespace Modules\Correspondence\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Correspondence\Database\Factories\CorrespondenceFactory;
use Modules\Correspondence\Enums\CorrespondenceClassification;
use Modules\Correspondence\Enums\CorrespondencePriority;
use Modules\Correspondence\Enums\CorrespondenceStatus;
use Modules\Correspondence\Enums\CorrespondenceType;
use Modules\User\Models\User;

class Correspondence extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'parent_id',
        'thread_id',
        'sender_user_id',
        'category_id',
        'type',
        'subject',
        'body',
        'priority',
        'classification',
        'status',
        'requires_approval',
        'approved_by',
        'approved_at',
        'sent_at',
        'closed_at',
        'archived_at',
    ];

    protected $casts = [
        'type' => CorrespondenceType::class,
        'priority' => CorrespondencePriority::class,
        'classification' => CorrespondenceClassification::class,
        'status' => CorrespondenceStatus::class,
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
        'closed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query
                ->where('sender_user_id', $user->id)
                ->orWhere('approved_by', $user->id)
                ->orWhereHas('recipients', fn (Builder $query) => $query->where('user_id', $user->id));
        });
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CorrespondenceCategory::class, 'category_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CorrespondenceRecipient::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CorrespondenceAttachment::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(CorrespondenceReply::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(CorrespondenceStatusHistory::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(CorrespondenceApproval::class);
    }

    protected static function newFactory()
    {
        return CorrespondenceFactory::new();
    }
}
