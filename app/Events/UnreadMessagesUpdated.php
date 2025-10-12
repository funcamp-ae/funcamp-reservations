<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class UnreadMessagesUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $chatId;
    public $unreadCount;
    public $totalUnread;
    public $message;
    /**
     * Create a new event instance.
     */
    public function __construct($userId, $chatId, $unreadCount, $totalUnread, ?Message $message = null)  
    {
        $this->userId = $userId;
        $this->chatId = $chatId;
        $this->unreadCount = $unreadCount; // unread count for this chat
        $this->totalUnread = $totalUnread; // total unread count for all chats
        $this->message = $message;
        // Don't broadcast to the sender
        $this->dontBroadcastToCurrentUser();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('unread_message_count_for_user.' . $this->userId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'unread_message_count_for_user.updated';
    }
}
