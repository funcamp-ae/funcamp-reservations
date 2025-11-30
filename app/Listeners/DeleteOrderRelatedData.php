<?php

namespace App\Listeners;

use App\Events\OrderCanceled;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Log\LogManager;
use App\Events\GeneralPayment;
class DeleteOrderRelatedData
{
    private $log;

    public function __construct(LogManager $log)
    {
        $this->log = $log;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCanceled $event)
    {
        $order = $event->order;

        try {
            // // Delete uploaded files attached directly to the order (hetzner disk)
            // $hetzner = Storage::disk('hetzner');
            // foreach (['image_before_receiving','image_after_delivery','voice_note','video_note','video_note_direct_key','video_note_logout','voice_note_logout'] as $col) {
            //     if (!empty($order->$col) && $hetzner->exists($order->$col)) {
            //         $hetzner->delete($order->$col);
            //     }
            // }

            // // Delete pre-login / pre-logout images (these use public disk in controller)
            // foreach ($order->preLoginImages()->get() as $img) {
            //     try { Storage::disk('public')->delete($img->image); } catch (\Throwable $e) {}
            //     $img->delete();
            // }
            // foreach ($order->preLogoutImages()->get() as $img) {
            //     try { Storage::disk('public')->delete($img->image); } catch (\Throwable $e) {}
            //     $img->delete();
            // }

            // Remove related models / pivots
            // $order->services()->detach();
            $order->addons()->detach();
            // $order->stocks()->detach();

            // Delete other related records
            $order->transactions()->delete();
            $order->payments()->delete();
            $order->paymentLinks()->delete();
            $order->items()->delete();
            GeneralPayment::where('order_id', $order->id)->delete();
            // $order->reports()->delete();
            // $order->internalNotes()->delete();
            $order->expenses()->delete();
            $order->surveyResponses()->delete();
            if ($order->surveyEmailLog) $order->surveyEmailLog->delete();

            // Note: do not delete the order record itself here; the controller flow can decide.
        } catch (\Throwable $e) {
            $this->log->error('Failed to delete related data for canceled order: '.$order->id, ['error' => $e->getMessage()]);
        }
    }
}
