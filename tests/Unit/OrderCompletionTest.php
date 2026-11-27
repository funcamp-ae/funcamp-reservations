<?php

namespace Tests\Unit;

use App\Models\Order;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class OrderCompletionTest extends TestCase
{
    public function test_completion_time_survives_edits_and_resets_on_status_transition()
    {
        $previousDispatcher = Order::getEventDispatcher();
        Order::setEventDispatcher(new Dispatcher());
        Order::clearBootedModels();
        try {
            Carbon::setTestNow(Carbon::parse('2026-11-27 20:00:00'));
            $order = new class extends Order {
                public function fireSaving()
                {
                    $this->fireModelEvent('saving');
                }
            };
            $order->setDateFormat('Y-m-d H:i:s');
            $order->status = 'approved';
            $order->syncOriginal();
            $order->status = 'completed';
            $order->fireSaving();
            $this->assertSame('2026-11-27 20:00:00', $order->completed_at->toDateTimeString());
            $order->syncOriginal();

            Carbon::setTestNow(Carbon::parse('2026-11-28 10:00:00'));
            $order->notes = 'Updated note';
            $order->fireSaving();
            $this->assertSame('2026-11-27 20:00:00', $order->completed_at->toDateTimeString());

            $order->status = 'approved';
            $order->fireSaving();
            $this->assertNull($order->completed_at);
            $order->syncOriginal();
            $order->status = 'completed';
            $order->fireSaving();
            $this->assertSame('2026-11-28 10:00:00', $order->completed_at->toDateTimeString());
        } finally {
            Carbon::setTestNow();
            Order::clearBootedModels();
            if ($previousDispatcher) {
                Order::setEventDispatcher($previousDispatcher);
            } else {
                Order::unsetEventDispatcher();
            }
        }
    }
}
