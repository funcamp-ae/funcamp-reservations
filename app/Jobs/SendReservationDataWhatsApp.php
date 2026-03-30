<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\WhatsappMessageTemplate;
use App\Models\ServiceSiteAndCustomerService;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendReservationDataWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;

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
            $order = $this->order;

            if (!$order->customer || !$order->customer->phone) {
                Log::warning('Order customer has no phone number for WhatsApp reservation data', [
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id
                ]);
                return;
            }

            $template = WhatsappMessageTemplate::getByType('reservation_data');
            if (!$template) {
                Log::warning('No active reservation data template found', ['order_id' => $order->id]);
                return;
            }

            $serviceSiteData = ServiceSiteAndCustomerService::getLatestForWhatsApp();
            $message = $template->getBilingualMessage($order->customer->name, '', $serviceSiteData);

            // Generate PDF via PDF service
            $pdfService = app(\App\Services\PdfGeneratorService::class);
            $pdfPath = null;
            try {
                $pdfPath = $pdfService->generateTempPdf($order, 'reservation_data');
            } catch (\Throwable $e) {
                Log::warning('PDF generation for reservation_data failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }

            $whatsAppService = new WhatsAppService();
            $success = false;

            if ($pdfPath && file_exists($pdfPath)) {
                $success = $whatsAppService->sendFile($order->customer->phone, $pdfPath, $message);

                if (file_exists($pdfPath)) {
                    @unlink($pdfPath);
                }
            } else {
                $success = $whatsAppService->sendTextMessage($order->customer->phone, $message);
            }

            if ($success) {
                Log::info('WhatsApp reservation data message sent successfully', [
                    'order_id' => $order->id,
                    'customer_phone' => $order->customer->phone
                ]);
            } else {
                Log::error('Failed to send WhatsApp reservation data message', [
                    'order_id' => $order->id,
                    'customer_phone' => $order->customer->phone
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp reservation data sending failed', [
                'order_id' => $this->order->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
