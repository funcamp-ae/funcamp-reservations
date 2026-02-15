<?php

namespace App\Services\Insurance;

use App\Models\Payment;
use Illuminate\Support\Collection;

class InsuranceReturnsHandler
{
    /**
     * Calculate all totals for insurance returns
     *
     * @param Collection $payments
     * @return array
     */
    public function calculateTotals(Collection $payments): array
    {
        $totalInsurancePaid = $payments->sum('price');
        
        $totalDeducted = $payments->sum(function ($payment) {
            if (!$payment->order->insurance_status) {
                return 0; // No insurance status means no deduction
            }
            return $payment->transaction ? $payment->transaction->amount : 0;
        });
        $totalNotReturned = $payments->filter(function ($payment) {
            return !$payment->order->insurance_status ;
        })->sum('price');
        $totalReturned = $totalInsurancePaid - $totalDeducted - $totalNotReturned;

        return [
            'total_insurance_paid' => $totalInsurancePaid,
            'total_deducted' => $totalDeducted,
            'total_returned' => $totalReturned,
            'total_not_returned' => $totalNotReturned,
        ];
    }

    /**
     * Get status counts for insurance payments
     *
     * @param Collection $payments
     * @return array
     */
    public function getStatusCounts(Collection $payments): array
    {
        return [
            'returned' => $this->countByStatus($payments, 'returned'),
            'confiscated_full' => $this->countByStatus($payments, 'confiscated_full'),
            'confiscated_partial' => $this->countByStatus($payments, 'confiscated_partial'),
            'not_returned' => $this->countPending($payments),
        ];
    }

    /**
     * Calculate return amount for a single payment
     *
     * @param Payment $payment
     * @return float
     */
    public function calculateReturnAmount(Payment $payment): float
    {
        $deductedAmount = $payment->transaction ? $payment->transaction->amount : 0;
        return $payment->price - $deductedAmount;
    }

    /**
     * Get deducted amount for a single payment
     *
     * @param Payment $payment
     * @return float
     */
    public function getDeductedAmount(Payment $payment): float
    {
        return $payment->transaction ? $payment->transaction->amount : 0;
    }

    /**
     * Process insurance returns data and return complete summary
     *
     * @param Collection $payments
     * @return array
     */
    public function processReturnsData(Collection $payments): array
    {
        $totals = $this->calculateTotals($payments);
        $statusCounts = $this->getStatusCounts($payments);
        $totalRecords = $payments->count();

        return [
            'totals' => $totals,
            'status_counts' => $statusCounts,
            'summary' => [
                'total_records' => $totalRecords,
                'average_insurance_amount' => $totalRecords > 0 
                    ? $totals['total_insurance_paid'] / $totalRecords 
                    : 0,
                'average_deducted_amount' => $totalRecords > 0 
                    ? $totals['total_deducted'] / $totalRecords 
                    : 0,
                'average_return_amount' => $totalRecords > 0 
                    ? $totals['total_returned'] / $totalRecords 
                    : 0,
                'most_common_status' => $this->getMostCommonStatus($statusCounts),
            ]
        ];
    }

    /**
     * Count payments by specific status type
     *
     * @param Collection $payments
     * @param string $status
     * @return int
     */
    protected function countByStatus(Collection $payments, string $status): int
    {
        return $payments->filter(function ($payment) use ($status) {
            return $payment->order && $payment->order->insurance_status === $status;
        })->count();
    }

    /**
     * Count pending payments (no status set)
     *
     * @param Collection $payments
     * @return int
     */
    protected function countPending(Collection $payments): int
    {
        return $payments->filter(function ($payment) {
            return !$payment->order || !$payment->order->insurance_status;
        })->count();
    }

    /**
     * Get the most common insurance status
     *
     * @param array $statusCounts
     * @return string
     */
    protected function getMostCommonStatus(array $statusCounts): string
    {
        if (empty($statusCounts)) {
            return 'none';
        }

        return array_keys($statusCounts, max($statusCounts))[0] ?? 'none';
    }
}
