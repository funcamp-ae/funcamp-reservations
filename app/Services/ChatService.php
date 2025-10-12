<?php

namespace App\Services;

use App\Events\UnreadMessagesUpdated;
use App\Models\Message;
use App\Models\Chat;
class ChatService
{
    /**
     * Update unread counts for all participants in a chat
     *
     * @param Chat $chat
     * @param int $excludeUserId ID of the user to exclude (usually the sender or current user)
     * @return void
     */
    public function updateUnreadCounts(Chat $chat, int $excludeUserId, ?Message $message = null): void
    {
        $participants = $chat->users()
            ->where('user_id', '!=', $excludeUserId)
            ->pluck('user_id');

        foreach ($participants as $userId) {
            $this->broadcastUnreadCount($chat, $userId, $message);
        }
    }

    /**
     * Broadcast unread count for a specific user in a chat
     *
     * @param Chat $chat
     * @param int $userId
     * @return void
     */
    public function broadcastUnreadCount(Chat $chat, int $userId, ?Message $message = null): void
    {
        // Get unread count for this chat
        $unreadCount = Message::where('chat_id', $chat->id)
            ->where('user_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
            
        // Get total unread count across all chats
        $totalUnread = Message::getUnreadCount($userId);
        
        broadcast(new UnreadMessagesUpdated(
            $userId,
            $chat->id,
            $unreadCount,
            $totalUnread,
            $message
        ));
    }
}
