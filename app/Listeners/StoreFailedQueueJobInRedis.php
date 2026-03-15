<?php

namespace App\Listeners;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Redis;

class StoreFailedQueueJobInRedis
{
    public function handle(JobFailed $event): void
    {
        $payload = [
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'connection' => $event->connectionName,
            'queue' => method_exists($event->job, 'getQueue') ? $event->job->getQueue() : null,
            'job_id' => method_exists($event->job, 'getJobId') ? $event->job->getJobId() : null,
            'attempts' => method_exists($event->job, 'attempts') ? $event->job->attempts() : null,
            'payload' => method_exists($event->job, 'getRawBody') ? $event->job->getRawBody() : null,
            'exception' => $event->exception->getMessage(),
            'failed_at' => now()->toDateTimeString(),
        ];

        Redis::connection('default')->rpush('queue:failed_jobs', json_encode($payload, JSON_UNESCAPED_UNICODE));
        Redis::connection('default')->ltrim('queue:failed_jobs', 0, 999);
    }
}
