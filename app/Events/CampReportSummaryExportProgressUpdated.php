<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampReportSummaryExportProgressUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public string $token,
        public int $processedChunks,
        public int $totalChunks
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'camp-report-summary-export.progress';
    }

    public function broadcastWith(): array
    {
        $totalChunks = max(1, $this->totalChunks);
        $processedChunks = min(max(0, $this->processedChunks), $totalChunks);

        return [
            'token' => $this->token,
            'processed_chunks' => $processedChunks,
            'total_chunks' => $totalChunks,
            'progress_percent' => (int) floor(($processedChunks / $totalChunks) * 100),
            'status' => $processedChunks >= $totalChunks ? 'finishing' : 'processing',
        ];
    }
}
