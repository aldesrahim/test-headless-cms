<?php

namespace App\Models;

use App\Services\Pages\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Page extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'slug',
        'title',
        'body',
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
}
