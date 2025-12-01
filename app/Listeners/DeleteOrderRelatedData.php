<?php

namespace App\Listeners;

use App\Events\OrderCanceled;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Log\LogManager;
use App\Models\GeneralPayment;
use App\Services\OrderAddonService;
class DeleteOrderRelatedData
{
    private $log;
    private $orderAddonService;

    public function __construct(LogManager $log , OrderAddonService $orderAddonService = null)
    {
        $this->log = $log;
        $this->orderAddonService = $orderAddonService ?? app(OrderAddonService::class);

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

            // Use the OrderAddonService to remove addon pivot rows safely
            $addons = $order->addons()->get();
            foreach ($addons as $addon) {
                try {
                    $pivot = $addon->pivot;
                    if ($pivot && !empty($pivot->id)) {
                        $res = $this->orderAddonService->removeAddon((int) $pivot->id);
                        $this->log->info('Removed order addon via service during order cancel', [
                            'order_id' => $order->id,
                            'addon_pivot_id' => $pivot->id,
                            'result' => $res,
                        ]);
                    }
                } catch (\Throwable $e) {
                    $this->log->error('Error removing addon via service during order cancel: ' . $e->getMessage(), [
                        'order_id' => $order->id,
                        'addon_id' => $addon->id ?? null,
                    ]);
                }
            }
            // $order->stocks()->detach();

            // Delete other related records using FinancialDeletionService so balances and transactions
            // are handled consistently.
            $financialDeletion = app(\App\Services\FinancialDeletionService::class);

            // Payments
            foreach ($order->payments()->get() as $payment) {
                try {
                    $res = $financialDeletion->deletePaymentById((int) $payment->id);
                } catch (\Throwable $e) {
                    $this->log->error('Failed deleting payment during order cancel: ' . $e->getMessage(), ['order_id' => $order->id, 'payment_id' => $payment->id]);
                }
            }

            // Order items (warehouse sales)
            foreach ($order->items()->get() as $item) {
                try {
                    $res = $financialDeletion->deleteOrderItemById((int) $item->id);
                } catch (\Throwable $e) {
                    $this->log->error('Failed deleting order item during order cancel: ' . $e->getMessage(), ['order_id' => $order->id, 'item_id' => $item->id]);
                }
            }

            // Expenses
            foreach ($order->expenses()->get() as $expense) {
                try {
                    $res = $financialDeletion->deleteExpenseById((int) $expense->id);
                } catch (\Throwable $e) {
                    $this->log->error('Failed deleting expense during order cancel: ' . $e->getMessage(), ['order_id' => $order->id, 'expense_id' => $expense->id]);
                }
            }

            // General payments related to this order
            $generalPayments = \App\Models\GeneralPayment::where('order_id', $order->id)->get();
            foreach ($generalPayments as $gp) {
                try {
                    $res = $financialDeletion->deleteGeneralPaymentById((int) $gp->id);
                } catch (\Throwable $e) {
                    $this->log->error('Failed deleting general payment during order cancel: ' . $e->getMessage(), ['order_id' => $order->id, 'general_payment_id' => $gp->id]);
                }
            }

            // Other relations: payment links & transactions (fallback cleanup)
            $order->paymentLinks()->delete();
            $order->transactions()->delete();
            // $order->reports()->delete();
            // $order->internalNotes()->delete();
            $order->surveyResponses()->delete();
            if ($order->surveyEmailLog) $order->surveyEmailLog->delete();

            // Note: do not delete the order record itself here; the controller flow can decide.
        } catch (\Throwable $e) {
            $this->log->error('Failed to delete related data for canceled order: '.$order->id, ['error' => $e->getMessage()]);
        }
    }
}
