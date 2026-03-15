<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
class ValidateCampReportMediaUrls extends Command
{
    protected $signature = 'camp-reports:validate-media-urls
                            {--type=photo : Media type to validate (photo|audio|video|all)}
                            {--timeout=12 : HTTP timeout in seconds}
                            {--accept=200,301,302,307,308,401,403 : Comma-separated acceptable status codes}
                            {--dry-run : Do not delete, only report}
                            {--chunk=200 : Chunk size for processing}';

    protected $description = 'Validate Camp Report media URLs via HTTP HEAD and remove invalid records while logging all actions';

    public function handle(): int
    {
        $mediaItems = \App\Models\CampReportItemMedia::all();

        foreach ($mediaItems as $media) {
            $url = $media->full_url ?: env('AWS_URL') . '/' . $media->path;

            try {
                $response = Http::head($url);
                if ($response->successful()) {
                    \Log::info("URL is valid: {$url}");
                    } else {
                        \Log::error("URL is not accessible (status: {$response->status()}): {$url}");
                        $media->delete();
                }
            } catch (\Exception $e) {
                \Log::error("Error accessing URL: {$url} - " . $e->getMessage());
            }
        }

        return 0;
    }
}
