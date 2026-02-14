<?php

namespace App\Exports;

use App\Models\Payment;
use App\Services\Insurance\InsuranceReturnsHandler;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InsuranceReturnsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $customerId;
    protected $orderId;
    protected $insuranceStatus;
    protected $dateFrom;
    protected $dateTo;
    protected $handler;

    public function __construct(
        $customerId, 
        $orderId, 
        $insuranceStatus, 
        $dateFrom, 
        $dateTo,
        InsuranceReturnsHandler $handler = null
    ) {
        $this->customerId = $customerId;
        $this->orderId = $orderId;
        $this->insuranceStatus = $insuranceStatus;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->handler = $handler ?? new InsuranceReturnsHandler();
    }

    public function collection()
    {
        $query = Payment::with(['order.customer', 'transaction', 'account', 'insuranceHandledBy'])
            ->where('statement', 'the_insurance')
            ->where('verified', "1");

        // Filter by customer
        if ($this->customerId) {
            $query->whereHas('order.customer', function ($q) {
                $q->where('id', $this->customerId);
            });
        }

        // Filter by order
        if ($this->orderId) {
            $query->where('order_id', $this->orderId);
        }

        // Filter by insurance status
        if ($this->insuranceStatus) {
            if ($this->insuranceStatus === 'not_returned') {
                // Filter for null insurance_status (not returned)
                $query->whereHas('order', function ($q) {
                    $q->whereNull('insurance_status');
                });
            } else {
                $query->whereHas('order', function ($q) {
                    $q->where('insurance_status', $this->insuranceStatus);
                });
            }
        }

        // Filter by date range
        if ($this->dateFrom && $this->dateTo) {
            $dateFrom = Carbon::parse($this->dateFrom)->startOfDay();
            $dateTo = Carbon::parse($this->dateTo)->endOfDay();
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            __('dashboard.booking_number'),
            __('dashboard.date'),
            __('dashboard.customer_name'),
            __('dashboard.insurance_amount_paid'),
            __('dashboard.payment_method'),
            __('dashboard.deducted_amount'),
            __('dashboard.return_amount'),
            __('dashboard.insurance_status'),
            __('dashboard.handled_by'),
        ];
    }

    public function map($payment): array
    {
        $deductedAmount = $this->handler->getDeductedAmount($payment);
        $returnAmount = $this->handler->calculateReturnAmount($payment);
        
        $insuranceStatus = '';
        if ($payment->order) {
            switch ($payment->order->insurance_status) {
                case 'returned':
                    $insuranceStatus = __('dashboard.insurance_returned');
                    break;
                case 'confiscated_full':
                    $insuranceStatus = __('dashboard.insurance_confiscated_full');
                    break;
                case 'confiscated_partial':
                    $insuranceStatus = __('dashboard.insurance_confiscated_partial');
                    break;
                default:
                    $insuranceStatus = __('dashboard.insurance_not_returned');
                    break;
            }
        }

        return [
            $payment->order ? $payment->order->id : 'N/A',
            $payment->created_at ? $payment->created_at->format('Y-m-d') : 'N/A',
            $payment->order && $payment->order->customer ? $payment->order->customer->name : 'N/A',
            number_format($payment->price, 2),
            $payment->payment_method ?? 'N/A',
            number_format($deductedAmount, 2),
            number_format($returnAmount, 2),
            $insuranceStatus,
            $payment->insuranceHandledBy ? $payment->insuranceHandledBy->name : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}
