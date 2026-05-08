<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'created_by_user_id',
        'title',
        'slug',
        'summary',
        'content',
        'attachment_path',
        'attachment_mime',
        'audience',
    ];

    protected $casts = [
    ];
}

