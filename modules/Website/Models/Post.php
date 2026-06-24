<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Website\Database\Factories\PostFactory;
use Modules\Website\Enums\WebsiteContentStatus;

class Post extends Model
{
    use HasFactory;

    protected $table = 'website_posts';

    protected $appends = [
        'featured_image_url',
    ];

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'type',
        'status',
        'featured_image_path',
        'seo_title',
        'seo_description',
        'published_at',
        'starts_at',
        'ends_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => WebsiteContentStatus::class,
        'published_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('status', WebsiteContentStatus::Published->value)
            ->where(fn (Builder $query) => $query
                ->whereNull('published_at')
                ->orWhere('published_at', '<=', now()));
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (! $this->featured_image_path) {
            return null;
        }

        if (Str::startsWith($this->featured_image_path, ['http://', 'https://', '/'])) {
            return $this->featured_image_path;
        }

        return '/storage/'.ltrim($this->featured_image_path, '/');
    }

    protected static function newFactory()
    {
        return PostFactory::new();
    }
}
