<?php

namespace App\Models;

use App\Support\Articles\YoutubeEmbed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    protected $fillable = [
        'article_category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'banner_image',
        'youtube_url',
        'status',
        'is_featured',
        'views_count',
        'author_id',
        'managed_by_bidang_id',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function managedByBidang(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'managed_by_bidang_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeLatestPublished(Builder $query): Builder
    {
        return $query->published()->latest('published_at');
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->published()->orderByDesc('views_count')->latest('published_at');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->lessThanOrEqualTo(now());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner_image ? Storage::url($this->banner_image) : null;
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        return YoutubeEmbed::embedUrl($this->youtube_url);
    }

    public function getReadingMinutesAttribute(): int
    {
        preg_match_all('/[\p{L}\p{N}\']+/u', strip_tags((string) $this->content), $matches);

        return max(1, (int) ceil(count($matches[0] ?? []) / 200));
    }
}
