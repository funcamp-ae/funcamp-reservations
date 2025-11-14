<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message->load('user', 'chat');
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->chat_id)
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'chat_id' => $this->message->chat_id,
                'user_id' => $this->message->user_id,
                'message' => $this->message->message,
                'type' => $this->message->type,
                'file_path' => $this->message->file_path,
                'file_url' => $this->message->file_url,
                'file_type' => $this->message->file_type,
                'is_read' => $this->message->is_read,
                'read_at' => $this->message->read_at,
                'created_at' => $this->message->created_at,
                'updated_at' => $this->message->updated_at,
                'user' => $this->message->user
            ],
            'chat_id' => $this->message->chat_id,
            'user' => $this->message->user
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
