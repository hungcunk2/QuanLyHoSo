<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_conversation_id',
        'sender_role',
        'body',
        'attachment_path',
        'attachment_original_name',
        'attachment_mime',
        'attachment_type',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'chat_conversation_id');
    }

    public function hasAttachment(): bool
    {
        return filled($this->attachment_path);
    }

    public function isImageAttachment(): bool
    {
        return $this->attachment_type === 'image';
    }
}
