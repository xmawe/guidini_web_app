<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'chat_room_id',
        'sender_id',
        'content',
        'read_at',
        'isRead'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'isRead' => 'boolean',
    ];

    /**
     * Get the chat room that owns the message.
     */
    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    /**
     * Get the user that sent the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
