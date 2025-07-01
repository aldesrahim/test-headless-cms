<?php

namespace App\Services\Posts\Concerns;

use App\Models\Attachment;
use App\Models\Post;
use App\Services\Attachments\AttachmentService;
use App\Services\Attachments\Data\StoreData;
use App\Services\Posts\Enums\AttachmentKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

trait HandleAttachments
{
    public const IMAGE_TARGET_DIR = 'posts';

    public function storeAttachment(Post $post, UploadedFile|int $attachment, ?Model $subject = null): Attachment
    {
        $disk = config('filesystems.default');

        $storedAttachment = app(AttachmentService::class)->store(new StoreData(
            file: $attachment,
            path: static::IMAGE_TARGET_DIR,
            disk: $disk,
            model: $post,
            subject: $subject,
            key: $key = AttachmentKey::Banner->value,
        ));

        if ($post->wasRecentlyCreated) {
            return $storedAttachment;
        }

        $previousAttachments = $post->attachments()
            ->wherePivot('attachment_id', '<>', $storedAttachment->id)
            ->wherePivot('key', $key)
            ->get();

        if (filled($previousAttachments)) {
            $post->attachments()->detach($previousAttachments);
        }

        return $storedAttachment;
    }
}
