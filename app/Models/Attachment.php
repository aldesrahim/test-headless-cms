<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
