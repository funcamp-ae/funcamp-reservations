<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\WhatsAppService;
use App\Models\WhatsappMessageTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\ServiceSiteAndCustomerService ;
class SendWhatsAppEvaluationJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    public $tries = 3;

    public $backoff = [60, 300];

    public $uniqueFor = 3600;

    public function uniqueId()
    {
        return (string) $this->order->id;
    }

    /**
     * Create a new job instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->order->refresh();
            if ($this->order->status !== 'completed' || $this->order->is_evaluation_sent) {
                return;
            }
            // Get the evaluation template (already checked in command, but double-check here)
            $template = WhatsappMessageTemplate::getByType('evaluation');
            if (!$template) {
                Log::warning('No active evaluation template found', ['order_id' => $this->order->id]);
                return;
            }

            // Check if customer has phone number
            if (!$this->order->customer || !$this->order->customer->phone) {
                Log::warning('Order customer has no phone number for WhatsApp evaluation', [
                    'order_id' => $this->order->id,
                    'customer_id' => $this->order->customer_id
                ]);
                return;
            }

            // Generate survey URL
            $surveyUrl = route('surveys.public', ['order' => $this->order->id]);
            
            // Get bilingual message
            $serviceSiteData = ServiceSiteAndCustomerService::getLatestForWhatsApp();

            $message = $template->getBilingualMessage($serviceSiteData , $this->order);

            // Send WhatsApp message
            $whatsAppService = new WhatsAppService();
            $success = $whatsAppService->sendLinkPreview(
                $this->order->customer->phone,
                $surveyUrl,
                $message
            );

            if ($success) {
                // Mark order as evaluation sent
                $this->order->update(['is_evaluation_sent' => true]);
                
                Log::info('WhatsApp evaluation message sent successfully', [
                    'order_id' => $this->order->id,
                    'customer_phone' => $this->order->customer->phone,
                    'survey_url' => $surveyUrl
                ]);
            } else {
                throw new \RuntimeException('WhatsApp provider did not accept the evaluation message. See WhatsApp service logs for details.');
            }

        } catch (\Throwable $e) {
            Log::error('WhatsApp evaluation job failed', [
                'order_id' => $this->order->id,
                'attempt' => $this->attempts(),
                'occurred_at' => now()->toIso8601String(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('WhatsApp evaluation exhausted retries', [
            'order_id' => $this->order->id,
            'occurred_at' => now()->toIso8601String(),
            'error' => $exception->getMessage(),
        ]);
    }
}
