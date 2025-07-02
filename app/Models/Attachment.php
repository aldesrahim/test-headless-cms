<?php

namespace App\Models;

use App\Models\Pivot\AttachmentHasModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    public const CACHE_KEY = 'attachment_public_url';

    protected $fillable = [
        'filename',
        'path',
        'disk',
        'size',
        'mime_type',
        'extension',
    ];

    protected $appends = [
        'public_url',
    ];

    protected static function booted(): void
    {
        static::deleted(function (Attachment $attachment) {
            Cache::forget($attachment->getCacheKey());
        });
    }

    public function getCacheKey(): string
    {
        return static::CACHE_KEY.':'.$this->id;
    }

    protected function publicUrl(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => Cache::remember(
            $this->getCacheKey(),
            now()->addWeek(),
            fn () => Storage::disk($attributes['disk'])->url($attributes['path']))
        );
    }

    public function attachmentHasModels(): HasMany
    {
        return $this->hasMany(AttachmentHasModel::class);
    }
}
