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

    protected function publicUrl(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => Cache::remember(
            'attachment_public_url:'.$attributes['id'],
            now()->addWeek(),
            fn () => Storage::disk($attributes['disk'])->url($attributes['path']))
        );
    }

    public function attachmentHasModels(): HasMany
    {
        return $this->hasMany(AttachmentHasModel::class);
    }
}
