<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatRoom extends Model
{
    protected $fillable = ['status', 'lastActivity'];

    protected $casts = [
        'lastActivity' => 'datetime',
        'status' => 'string'
    ];

    /**
     * Get the messages for the chat room.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the users in this chat room.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_room_user');
    }

    /**
     * Get the latest message in the chat room.
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latest();
    }
}
