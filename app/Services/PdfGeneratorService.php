<?php

namespace App\Services;

use App\Models\Order;
use App\Models\TermsSittng;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;

class PdfGeneratorService
{
    /**
     * Generate a temporary PDF for an order based on type
     *
     * @param Order $order
     * @param string $type (show_price|reservation_data|reservation_confirmation|invoice|receipt)
     * @return string|false Path to generated PDF or false on failure
     */
    public function generateTempPdf($order, $type)
    {
        $termsSittng = TermsSittng::firstOrFail();
        $view = '';
        $qrCodeFullPath = null;

        switch ($type) {
            case 'show_price':
                $view = 'dashboard.orders.pdf.quote';
                break;
            case 'reservation_data':
            case 'reservation_confirmation':
                $view = 'dashboard.orders.pdf.reservation';
                // Generate QR code for reservation data
                try {
                    $editUrl = route('orders.edit', $order->id);
                    $qrCode = QrCode::create($editUrl)
                        ->setSize(100)
                        ->setMargin(0)
                        ->setBackgroundColor(new Color(255, 255, 255));

                    $writer = new PngWriter();
                    $result = $writer->write($qrCode);

                    $qrCodePath = 'qrcodes/order_' . $order->id . '.png';
                    Storage::disk('public')->put($qrCodePath, $result->getString());
                    $qrCodeFullPath = Storage::disk('public')->path($qrCodePath);
                } catch (\Exception $e) {
                    Log::error('QR code generation failed: ' . $e->getMessage());
                    $qrCodeFullPath = null;
                }
                break;
            case 'invoice':
                $view = 'dashboard.orders.pdf.invoice';
                break;
            case 'receipt':
                $view = 'dashboard.orders.pdf.receipt';
                break;
            default:
                return false;
        }

        // Render the view with appropriate variables
        if ($type === 'reservation_data' || $type === 'reservation_confirmation') {
            $editUrl = $editUrl ?? route('orders.edit', $order->id);
            $html = view($view, compact('order', 'termsSittng', 'qrCodeFullPath', 'editUrl'))->render();
        } else {
            $html = view($view, compact('order', 'termsSittng'))->render();
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'xbriyaz',
            'directionality' => 'rtl'
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->SetFooter('{PAGENO} / {nbpg}');
        $mpdf->WriteHTML($html);

        // Ensure temp directory exists
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $tempPath = storage_path('app/temp/' . $type . '_' . $order->id . '.pdf');
        $mpdf->Output($tempPath, \Mpdf\Output\Destination::FILE);

        // Clean up QR code file if created
        if (($type === 'reservation_data' || $type === 'reservation_confirmation') && isset($qrCodePath) && Storage::disk('public')->exists($qrCodePath)) {
            Storage::disk('public')->delete($qrCodePath);
        }

        return $tempPath;
    }

    /**
     * Generate addon receipt PDF
     *
     * @param int $orderId
     * @param int $addonId
     * @return string|false
     */
    public function generateAddonReceiptPdf($orderId, $addonId)
    {
        $order = \App\Models\Order::with(['payments', 'addons', 'services', 'customer', 'items'])
            ->findOrFail($orderId);

        $addon = $order->addons->where('pivot.id', $addonId)->first();
        if (!$addon) {
            return false;
        }

        $termsSittng = TermsSittng::firstOrFail();
        $html = view('dashboard.orders.pdf.addon_receipt', compact('order', 'addon', 'termsSittng'))->render();

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->WriteHTML($html);

        // Ensure temp directory exists
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $tempPath = storage_path('app/temp/addon_receipt_' . $addonId . '.pdf');
        $mpdf->Output($tempPath, \Mpdf\Output\Destination::FILE);

        return $tempPath;
    }

    /**
     * Generate payment receipt PDF
     *
     * @param int $orderId
     * @param int $paymentId
     * @return string|false
     */
    public function generatePaymentReceiptPdf($orderId, $paymentId)
    {
        $order = \App\Models\Order::findOrFail($orderId);
        $payment = \App\Models\Payment::with(['account'])->findOrFail($paymentId);
        
        if ($payment->order_id != $order->id) {
            return false;
        }

        $termsSittng = TermsSittng::firstOrFail();
        $html = view('dashboard.orders.pdf.payment_receipt', compact('order', 'payment', 'termsSittng'))->render();

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->WriteHTML($html);

        // Ensure temp directory exists
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $tempPath = storage_path('app/temp/payment_receipt_' . $paymentId . '.pdf');
        $mpdf->Output($tempPath, \Mpdf\Output\Destination::FILE);

        return $tempPath;
    }

    /**
     * Generate warehouse receipt PDF
     *
     * @param int $orderId
     * @param int $itemId
     * @return string|false
     */
    public function generateWarehouseReceiptPdf($orderId, $itemId)
    {
        $order = \App\Models\Order::findOrFail($orderId);
        $warehouseItem = \App\Models\OrderItem::with(['stock'])->findOrFail($itemId);
        
        if ($warehouseItem->order_id != $order->id) {
            return false;
        }

        $termsSittng = TermsSittng::firstOrFail();
        $html = view('dashboard.orders.pdf.warehouse_receipt', compact('order', 'warehouseItem', 'termsSittng'))->render();

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->WriteHTML($html);

        // Ensure temp directory exists
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $tempPath = storage_path('app/temp/warehouse_receipt_' . $itemId . '.pdf');
        $mpdf->Output($tempPath, \Mpdf\Output\Destination::FILE);

        return $tempPath;
    }

    /**
     * Clean up temporary PDF files
     *
     * @param int $orderId
     * @param array $documents List of document identifiers
     * @return void
     */
    public function cleanupTempFiles($orderId, $documents)
    {
        foreach ($documents as $document) {
            if (str_starts_with($document, 'addon_receipt_')) {
                $addonId = str_replace('addon_receipt_', '', $document);
                $tempPath = storage_path('app/temp/addon_receipt_' . $addonId . '.pdf');
            } elseif (str_starts_with($document, 'payment_receipt_')) {
                $paymentId = str_replace('payment_receipt_', '', $document);
                $tempPath = storage_path('app/temp/payment_receipt_' . $paymentId . '.pdf');
            } elseif (str_starts_with($document, 'warehouse_receipt_')) {
                $itemId = str_replace('warehouse_receipt_', '', $document);
                $tempPath = storage_path('app/temp/warehouse_receipt_' . $itemId . '.pdf');
            } else {
                $tempPath = storage_path('app/temp/' . $document . '_' . $orderId . '.pdf');
            }

            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    /**
     * Generate and output PDF directly to browser
     *
     * @param Order $order
     * @param string $type
     * @param string $filename
     * @param string $output (I=inline, D=download, F=file, S=string)
     * @return mixed
     */
    public function generateAndOutputPdf($order, $type, $filename, $output = 'I')
    {
        $termsSittng = TermsSittng::firstOrFail();
        $view = '';
        $qrCodeFullPath = null;
        $editUrl = null;

        switch ($type) {
            case 'quote':
                $view = 'dashboard.orders.pdf.quote';
                break;
            case 'reservation':
                $view = 'dashboard.orders.pdf.reservation';
                try {
                    $editUrl = route('orders.edit', $order->id);
                    $qrCode = QrCode::create($editUrl)
                        ->setSize(100)
                        ->setMargin(0)
                        ->setBackgroundColor(new Color(255, 255, 255));

                    $writer = new PngWriter();
                    $result = $writer->write($qrCode);

                    $qrCodePath = 'qrcodes/order_' . $order->id . '.png';
                    Storage::disk('public')->put($qrCodePath, $result->getString());
                    $qrCodeFullPath = Storage::disk('public')->path($qrCodePath);
                } catch (\Exception $e) {
                    Log::error('QR code generation failed: ' . $e->getMessage());
                }
                break;
            case 'invoice':
                $view = 'dashboard.orders.pdf.invoice';
                break;
            case 'receipt':
                $view = 'dashboard.orders.pdf.receipt';
                break;
            default:
                return false;
        }

        if ($type === 'reservation' && $qrCodeFullPath) {
            $html = view($view, compact('order', 'termsSittng', 'qrCodeFullPath', 'editUrl'))->render();
        } else {
            $html = view($view, compact('order', 'termsSittng'))->render();
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'xbriyaz',
            'directionality' => 'rtl'
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->SetFooter('{PAGENO} / {nbpg}');
        $mpdf->WriteHTML($html);

        // Clean up QR code file if created
        if ($type === 'reservation' && isset($qrCodePath) && Storage::disk('public')->exists($qrCodePath)) {
            Storage::disk('public')->delete($qrCodePath);
        }

        return $mpdf->Output($filename, $output);
    }
}
