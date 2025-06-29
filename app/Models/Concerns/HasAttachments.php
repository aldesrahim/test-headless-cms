<?php

namespace App\Models\Concerns;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/** @mixin Model */
trait HasAttachments
{
    public function attachments(): MorphToMany
    {
        return $this
            ->morphToMany(Attachment::class, 'model', 'model_has_attachments')
            ->withPivot(['key', 'created_at', 'updated_at']);
    }
}
