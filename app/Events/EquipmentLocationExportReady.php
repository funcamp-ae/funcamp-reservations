<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EquipmentLocationExportReady implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public string $token,
        public string $downloadUrl
    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'equipment-location-export.ready';
    }

    public function broadcastWith(): array
    {
        return [
            'userId' => $this->userId,
            'token' => $this->token,
            'downloadUrl' => $this->downloadUrl,
        ];
    }
}
