<?php

namespace App\Models\Pivot;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class AttachmentHasModel extends MorphPivot
{
    public $incrementing = true;

    public $timestamps = true;
}
