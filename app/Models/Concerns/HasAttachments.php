<?php

namespace App\Models\Concerns;

use App\Models\Attachment;
use App\Models\Pivot\AttachmentHasModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/** @mixin Model */
trait HasAttachments
{
    public function attachments(): MorphToMany
    {
        return $this
            ->morphToMany(Attachment::class, 'model', 'attachment_has_model')
            ->using(AttachmentHasModel::class)
            ->withTimestamps()
            ->withPivot(['key']);
    }
}
