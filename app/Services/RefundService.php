<?php

namespace App\Services;

use App\Models\PaymentLink;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundService
{
    protected $financialDeletionService;
    protected $paymentService;
    protected $insuranceVerificationService;

    public function __construct(
        FinancialDeletionService $financialDeletionService, 
        PaymentService $paymentService,
        InsuranceVerificationService $insuranceVerificationService
    )
    {
        $this->financialDeletionService = $financialDeletionService;
        $this->paymentService = $paymentService;
        $this->insuranceVerificationService = $insuranceVerificationService;
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
                'status' => $isFullRefund ? 'refunded' : 'partially_refunded',
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
            $insuranceUpdateResult = null;
            
            if ($paymentLink->payment_id) {
                $payment = Payment::find($paymentLink->payment_id);
                
                if ($payment) {
                    // Update insurance status if payment is insurance-related
                    if ($payment->statement === 'the_insurance') {
                        $insuranceUpdateResult = $this->updateInsuranceStatusAfterRefund($paymentLink, $payment, $refundAmount, $isFullRefund , $refundData['note'] ?? null);
                        
                        // Verify the order insurance
                        if ($insuranceUpdateResult['success'] && $payment->order) {
                            $verificationResult = $this->paymentService->makeVerficationTotheReservation($payment->order);                   
                            if (!$verificationResult['success']) {
                                Log::warning('Insurance verification failed', [
                                    'order_id' => $payment->order->id,
                                    'error' => $verificationResult['message'] ?? 'Unknown error'
                                ]);
                            }
                        }
                        
                        if (!$insuranceUpdateResult['success']) {
                            Log::warning('Insurance status update failed but continuing with refund', [
                                'payment_id' => $payment->id,
                                'error' => $insuranceUpdateResult['error'] ?? 'Unknown error'
                            ]);
                        }
                    }
                    
                    // Handle non-insurance payment refunds
                    if ($payment->statement !== 'the_insurance') {
                        $paymentResult = $this->handleNonInsurancePaymentRefund(
                            $paymentLink,
                            $payment,
                            $refundAmount,
                            $isFullRefund
                        );

                        if (!$paymentResult['success']) {
                            DB::rollBack();
                            return $paymentResult;
                        }
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
                'payment_result' => $paymentResult,
                'insurance_update_result' => $insuranceUpdateResult
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
        if ($paymentLink->order->is_insurance_verified) {
            $errors[] = 'Cannot process refund while there is reservation insurance verified';
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

    /**
     * Handle refund for non-insurance payments
     * Deletes payment for full refunds, updates for partial refunds
     *
     * @param PaymentLink $paymentLink
     * @param Payment $payment
     * @param float $refundAmount
     * @param bool $isFullRefund
     * @return array
     */
    private function handleNonInsurancePaymentRefund(
        PaymentLink $paymentLink,
        Payment $payment,
        float $refundAmount,
        bool $isFullRefund
    ): array
    {
        try {
            if ($isFullRefund) {
                // Full refund: delete the payment record
                $paymentResult = $this->financialDeletionService->deletePaymentById((int) $paymentLink->payment_id);
                
                if (!$paymentResult['success']) {
                    return [
                        'success' => false,
                        'error' => 'Payment deletion failed: ' . ($paymentResult['message'] ?? 'Unknown error')
                    ];
                }

                Log::info('Full refund: Payment deleted', [
                    'payment_id' => $paymentLink->payment_id,
                    'deleted_amount' => $paymentResult['deleted_amount'] ?? 0
                ]);

                return $paymentResult;
            } else {
                // Partial refund: update the payment record
                $newPaymentAmount = $payment->price - $refundAmount;
                $paymentResult = $this->paymentService->updatePaymentAmount(
                    (int) $paymentLink->payment_id, 
                    $newPaymentAmount,
                    $refundAmount
                );

                if (!$paymentResult['success']) {
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

                return $paymentResult;
            }
        } catch (\Exception $e) {
            Log::error('Failed to handle non-insurance payment refund', [
                'payment_id' => $payment->id,
                'refund_amount' => $refundAmount,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to process non-insurance payment refund: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update order insurance status after refund
     * Resets insurance verification and related fields
     *
     * @param Payment $payment
     * @param float $refundAmount
     * @param bool $isFullRefund
     * @return array
     */
    public function updateInsuranceStatusAfterRefund( PaymentLink $paymentLink, Payment $payment, float $refundAmount, bool $isFullRefund , ?string $refundNote = null): array
    {
        try {
            // Only process if payment is insurance-related
            if ($payment->statement !== 'the_insurance') {
                return [
                    'success' => true,
                    'message' => 'Payment is not insurance-related, no insurance update needed'
                ];
            }

            $order = $payment->order;
            if (!$order) {
                return [
                    'success' => false,
                    'error' => 'Order not found for payment'
                ];
            }

            DB::beginTransaction();
            try {
                $order->update([
                    'insurance_status' => $isFullRefund ? 'returned' : 'confiscated_partial',
                    'confiscation_description' => $refundNote ?? " ",
                    'partial_confiscation_amount' => $isFullRefund ? null : $paymentLink->amount - $refundAmount,
                    ]);
                DB::commit();

                return [
                    'success' => true,
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to update insurance status after refund', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to update insurance status: ' . $e->getMessage()
            ];
        }
    }
}
