<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'message',
        'type',
        'is_read',
        'read_at',
        'unread_notification_sent_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'unread_notification_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the chat that owns the message.
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the user that sent the message.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark message as read.
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Get unread messages count for a user in a specific chat.
     * Counts messages sent BY others TO the current user.
     */
    public static function getUnreadCount($userId, $chatId = null)
    {
        // Get messages sent by others (not by the current user)
        $query = self::where('user_id', '!=', $userId)
            ->where('is_read', false);

        if ($chatId) {
            $query->where('chat_id', $chatId);
        } else {
            // Get all chats where user is a member
            $userChats = Chat::whereHas('users', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->pluck('id');
            
            $query->whereIn('chat_id', $userChats);
        }

        return $query->count();
    }

    /**
     * Get unread messages for a user.
     * Returns messages sent BY others TO the current user.
     */
    public static function getUnreadMessages($userId)
    {
        // Get all chats where the user is a member
        $userChats = Chat::whereHas('users', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->pluck('id');

        // Get messages in those chats that were sent by others and are unread
        return self::whereIn('chat_id', $userChats)
            ->where('user_id', '!=', $userId)  // Messages sent by others
            ->where('is_read', false)          // Messages not yet read
            ->with(['chat', 'user'])
            ->latest()
            ->get();
    }
}
