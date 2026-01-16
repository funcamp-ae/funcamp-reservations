<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Services\InsuranceVerificationService;

class PaymentService
{
    /**
     * Update payment amount (used for partial refunds)
     * Reduces the payment amount and adjusts bank account balance accordingly
     *
     * @param int $id Payment ID
     * @param float $newAmount New payment amount after refund
     * @param float $refundAmount Amount being refunded
     * @return array
     */
    public function updatePaymentAmount(int $id, float $newAmount, float $refundAmount): array
    {
        DB::beginTransaction();
        try {
            $payment = Payment::findOrFail($id);
            $originalAmount = $payment->price;

            // Validate the new amount
            if ($newAmount < 0) {
                DB::rollBack();
                return ['success' => false, 'message' => 'New payment amount cannot be negative'];
            }

            if ($refundAmount > $originalAmount) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Refund amount exceeds original payment amount'];
            }

            // Reverse the refunded amount from bank account before updating payment
            $updatedBalance = null;
            if ($payment->verified) {
                $balanceResult = $this->reversePaymentAmountFromBankAccount($payment, $refundAmount);
                
                if (!$balanceResult['success']) {
                    DB::rollBack();
                    return $balanceResult;
                }
                
                $updatedBalance = $balanceResult['updated_balance'] ?? null;
            }

            // Update the payment amount
            $payment->update([
                'price' => $newAmount,
                'verified' => 0,
            ]);

            // Update transaction amount if exists
            $transaction = $payment->transaction;
            if ($transaction) {
                $transaction->update([
                    'amount' => $newAmount,
                    'verified' => 0,
                ]);
                
                \Log::info('Transaction amount updated for partial refund', [
                    'transaction_id' => $transaction->id,
                    'payment_id' => $id,
                    'original_amount' => $originalAmount,
                    'new_amount' => $newAmount,
                    'refund_amount' => $refundAmount
                ]);
            }

            \Log::info('Payment amount updated successfully', [
                'payment_id' => $id,
                'original_amount' => $originalAmount,
                'refund_amount' => $refundAmount,
                'new_amount' => $newAmount
            ]);

            DB::commit();
            return [
                'success' => true,
                'original_amount' => $originalAmount,
                'refund_amount' => $refundAmount,
                'new_amount' => $newAmount,
                'updated_balance' => $updatedBalance
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('updatePaymentAmount failed: ' . $e->getMessage(), [
                'payment_id' => $id,
                'new_amount' => $newAmount,
                'refund_amount' => $refundAmount
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Reverse (deduct) refunded amount from bank account balance before updating payment
     * This handles the financial rollback for partial refunds (non-insurance payments only)
     *
     * @param Payment $payment The payment being refunded
     * @param float $refundAmount Amount being refunded
     * @return array
     */
    public function reversePaymentAmountFromBankAccount(Payment $payment, float $refundAmount): array
    {
        try {
            $updatedBalance = null;

            if ($payment->account_id) {
                $bankAccount = BankAccount::find($payment->account_id);
                if ($bankAccount) {
                    // Decrease balance by refund amount (reverse the payment)
                    $bankAccount->decrement('balance', $refundAmount);
                    $updatedBalance = $bankAccount->balance;
                    
                    \Log::info('Bank account balance reversed for partial refund', [
                        'payment_id' => $payment->id,
                        'account_id' => $bankAccount->id,
                        'refund_amount' => $refundAmount,
                        'new_balance' => $updatedBalance
                    ]);
                }
            }

            return [
                'success' => true,
                'updated_balance' => $updatedBalance
            ];
        } catch (\Exception $e) {
            \Log::error('reversePaymentAmountFromBankAccount failed: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'refund_amount' => $refundAmount
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Make verification to the reservation
     * Updates insurance verification status and triggers verification event
     *
     * @param \App\Models\Order $order The order to verify
     * @return array
     */
    public function makeVerficationTotheReservation($order): array
    {
        try {
            if ($order->insurance_status && !$order->is_insurance_verified) {
                $order->update(['is_insurance_verified' => true]);
                $order->update(["insurance_handled_by" => auth()->user()->id]);
                event(new \App\Events\InsuranceVerified($order));
                
                \Log::info('Insurance verification completed for order', [
                    'order_id' => $order->id,
                    'handled_by' => auth()->user()->id
                ]);

                return [
                    'success' => true,
                    'message' => 'Insurance verification completed'
                ];
            }

            return [
                'success' => false,
                'message' => 'Order does not meet verification criteria'
            ];
        } catch (\Exception $e) {
            \Log::error('makeVerficationTotheReservation failed: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
