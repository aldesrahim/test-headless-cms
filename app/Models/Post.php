<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'content',
        'excerpt',
        'status',
        'published_at',
    ];
}
