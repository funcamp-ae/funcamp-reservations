<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyAdminsOrderApproved implements ShouldQueue
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
            $admins = User::role('admin')->get();
            if ($admins->isEmpty()) {
                return;
            }

            $whatsApp = new WhatsAppService();
            $order = $this->order;
            $message = "New order approved\n" .
                "Order #: {$order->id}\n" .
                (optional($order->customer)->name ? "Customer: {$order->customer->name}\n" : '') .
                ($order->date ? "Date: {$order->date}\n" : '') .
                ($order->time_from ? "From: {$order->time_from}\n" : '') .
                ($order->time_to ? "To: {$order->time_to}\n" : '') .
                (isset($order->price) ? "Total: {$order->price}\n" : '') .
                "Link: " . route('orders.edit', $order->id);

            foreach ($admins as $admin) {
                if (!empty($admin->phone)) {
                    $whatsApp->sendTextMessage($admin->phone, $message);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to notify admins via WhatsApp for approved order', [
                'order_id' => $this->order->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
