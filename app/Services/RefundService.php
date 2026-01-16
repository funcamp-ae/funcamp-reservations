<?php

namespace App\Services;

use App\Models\PaymentLink;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundService
{
    protected $financialDeletionService;
    protected $paymentService;

    public function __construct(FinancialDeletionService $financialDeletionService, PaymentService $paymentService)
    {
        $this->financialDeletionService = $financialDeletionService;
        $this->paymentService = $paymentService;
    }

    /**
     * Process refund for a payment link (handles both full and partial refunds)
     *
     * @param PaymentLink $paymentLink
     * @param float $refundAmount
     * @param array $refundData Additional refund data (currency, note, etc.)
     * @return array
     */
    public function processRefund(PaymentLink $paymentLink, float $refundAmount, array $refundData = [])
    {
        DB::beginTransaction();
        try {
            // Get current refunded amount (default to 0 if null)
            $currentRefundedAmount = $paymentLink->refunded_amount ?? 0;
            $totalRefundedAmount = $currentRefundedAmount + $refundAmount;

            // Validate refund amount
            if ($totalRefundedAmount > $paymentLink->amount) {
                return [
                    'success' => false,
                    'error' => 'Refund amount exceeds payment link amount',
                    'details' => [
                        'payment_amount' => $paymentLink->amount,
                        'current_refunded' => $currentRefundedAmount,
                        'requested_refund' => $refundAmount,
                        'would_be_total' => $totalRefundedAmount
                    ]
                ];
            }

            // Determine if this is a full or partial refund
            $isFullRefund = $totalRefundedAmount == $paymentLink->amount;
            $isPartialRefund = !$isFullRefund;

            // Update payment link with refund information
            $paymentLink->update([
                'refunded_amount' => $totalRefundedAmount,
                'status' => $isFullRefund ? 'refunded' : 'partially_refunded'
            ]);

            Log::info('Payment link refund updated', [
                'payment_link_id' => $paymentLink->id,
                'checkout_id' => $paymentLink->checkout_id,
                'refund_amount' => $refundAmount,
                'total_refunded' => $totalRefundedAmount,
                'is_full_refund' => $isFullRefund,
                'new_status' => $paymentLink->status
            ]);

            // Handle payment record if exists
            $paymentResult = null;
            if ($paymentLink->payment_id) {
                $payment = Payment::find($paymentLink->payment_id);
                
                if ($payment) {
                    if ($isFullRefund) {
                        // Full refund: delete the payment record
                        $paymentResult = $this->financialDeletionService->deletePaymentById((int) $paymentLink->payment_id);
                        
                        if (!$paymentResult['success']) {
                            DB::rollBack();
                            return [
                                'success' => false,
                                'error' => 'Payment deletion failed: ' . ($paymentResult['message'] ?? 'Unknown error')
                            ];
                        }

                        Log::info('Full refund: Payment deleted', [
                            'payment_id' => $paymentLink->payment_id,
                            'deleted_amount' => $paymentResult['deleted_amount'] ?? 0
                        ]);
                    } else {
                        // Partial refund: update the payment record
                        $newPaymentAmount = $payment->price - $refundAmount;
                        $paymentResult = $this->paymentService->updatePaymentAmount(
                            (int) $paymentLink->payment_id, 
                            $newPaymentAmount,
                            $refundAmount
                        );

                        if (!$paymentResult['success']) {
                            DB::rollBack();
                            return [
                                'success' => false,
                                'error' => 'Payment update failed: ' . ($paymentResult['message'] ?? 'Unknown error')
                            ];
                        }

                        Log::info('Partial refund: Payment updated', [
                            'payment_id' => $paymentLink->payment_id,
                            'original_amount' => $payment->price,
                            'refund_amount' => $refundAmount,
                            'new_amount' => $newPaymentAmount,
                            'updated_balance' => $paymentResult['updated_balance'] ?? 0
                        ]);
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'refund_type' => $isFullRefund ? 'full' : 'partial',
                'refunded_amount' => $refundAmount,
                'total_refunded_amount' => $totalRefundedAmount,
                'remaining_amount' => $paymentLink->amount - $totalRefundedAmount,
                'payment_link_status' => $paymentLink->status,
                'payment_result' => $paymentResult
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RefundService: Process refund failed', [
                'payment_link_id' => $paymentLink->id,
                'refund_amount' => $refundAmount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Refund processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate refund request
     *
     * @param PaymentLink $paymentLink
     * @param float $refundAmount
     * @return array
     */
    public function validateRefundRequest(PaymentLink $paymentLink, float $refundAmount)
    {
        $errors = [];

        // Check payment link status
        if (!in_array($paymentLink->status, ['paid', 'partially_refunded'])) {
            $errors[] = 'Payment link must be paid or partially refunded to process refund';
        }

        // Check checkout_id exists
        if (!$paymentLink->checkout_id) {
            $errors[] = 'Payment link missing checkout_id';
        }

        // Check refund amount
        if ($refundAmount <= 0) {
            $errors[] = 'Refund amount must be greater than 0';
        }

        $currentRefundedAmount = $paymentLink->refunded_amount ?? 0;
        $remainingAmount = $paymentLink->amount - $currentRefundedAmount;

        if ($refundAmount > $remainingAmount) {
            $errors[] = "Refund amount ({$refundAmount}) exceeds remaining amount ({$remainingAmount})";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'remaining_amount' => $remainingAmount,
            'current_refunded_amount' => $currentRefundedAmount
        ];
    }

    /**
     * Get refund summary for a payment link
     *
     * @param PaymentLink $paymentLink
     * @return array
     */
    public function getRefundSummary(PaymentLink $paymentLink)
    {
        $refundedAmount = $paymentLink->refunded_amount ?? 0;
        $remainingAmount = $paymentLink->amount - $refundedAmount;
        $refundPercentage = $paymentLink->amount > 0 ? ($refundedAmount / $paymentLink->amount) * 100 : 0;

        return [
            'payment_link_id' => $paymentLink->id,
            'checkout_id' => $paymentLink->checkout_id,
            'total_amount' => $paymentLink->amount,
            'refunded_amount' => $refundedAmount,
            'remaining_amount' => $remainingAmount,
            'refund_percentage' => round($refundPercentage, 2),
            'status' => $paymentLink->status,
            'is_fully_refunded' => $refundedAmount >= $paymentLink->amount,
            'is_partially_refunded' => $refundedAmount > 0 && $refundedAmount < $paymentLink->amount,
            'can_refund' => in_array($paymentLink->status, ['paid', 'partially_refunded']) && $remainingAmount > 0
        ];
    }
}
