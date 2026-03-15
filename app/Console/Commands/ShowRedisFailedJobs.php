<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ShowRedisFailedJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:failed:redis {--limit=20 : Number of failed jobs to show} {--raw : Print raw JSON entries}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show failed queue jobs stored in Redis key queue:failed_jobs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');

        if ($limit < 1) {
            $this->error('The --limit value must be greater than zero.');
            return 1;
        }

        if (config('database.redis.client') === 'phpredis' && ! class_exists(\Redis::class)) {
            $this->warn('Redis extension is not installed. Use `php artisan queue:failed` to view failed jobs from database.');
            return 0;
        }

        try {
            $rawEntries = Redis::connection('default')->lrange('queue:failed_jobs', 0, $limit - 1);
        } catch (\Throwable $e) {
            $this->warn('Redis is unavailable: ' . $e->getMessage());
            $this->line('Use `php artisan queue:failed` to view failed jobs from database.');
            return 0;
        }

        if (empty($rawEntries)) {
            $this->info('No failed jobs found in Redis key queue:failed_jobs.');
            return 0;
        }

        if ((bool) $this->option('raw')) {
            foreach ($rawEntries as $entry) {
                $this->line($entry);
            }

            return 0;
        }

        $rows = [];

        foreach ($rawEntries as $index => $entry) {
            $decoded = json_decode($entry, true);

            if (!is_array($decoded)) {
                $rows[] = [
                    '#' => $index + 1,
                    'failed_at' => '-',
                    'connection' => '-',
                    'queue' => '-',
                    'job_id' => '-',
                    'exception' => mb_substr((string) $entry, 0, 120),
                ];

                continue;
            }

            $rows[] = [
                '#' => $index + 1,
                'failed_at' => (string) ($decoded['failed_at'] ?? '-'),
                'connection' => (string) ($decoded['connection'] ?? '-'),
                'queue' => (string) ($decoded['queue'] ?? '-'),
                'job_id' => (string) ($decoded['job_id'] ?? '-'),
                'exception' => mb_substr((string) ($decoded['exception'] ?? '-'), 0, 120),
            ];
        }

        $this->table(['#', 'failed_at', 'connection', 'queue', 'job_id', 'exception'], $rows);

        return 0;
    }
}
