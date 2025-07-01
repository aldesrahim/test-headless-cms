<?php

namespace App\Models;

use App\Models\Concerns\HasAttachments;
use App\Services\Posts\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use HasAttachments;
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'slug',
        'title',
        'content',
        'excerpt',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function isPublished(): bool
    {
        return filled($this->published_at);
    }

    public function markAsPublished(): bool
    {
        return $this->update(['published_at' => now(), 'status' => Status::Published]);
    }

    public function markAsDraft(): bool
    {
        return $this->update(['published_at' => null, 'status' => Status::Draft]);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withPivot(['created_at', 'updated_at']);
    }
}
