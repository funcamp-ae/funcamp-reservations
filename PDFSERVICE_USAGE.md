# PDF Generator Service Usage Guide

The `PdfGeneratorService` provides a centralized way to generate PDF documents for orders throughout the application.

## Setup

### 1. Inject the Service in Your Controller

```php
use App\Services\PdfGeneratorService;

class YourController extends Controller
{
    private $pdfService;

    public function __construct(PdfGeneratorService $pdfService)
    {
        $this->pdfService = $pdfService;
    }
}
```

## Available Methods

### 1. Generate Temporary PDF Files

Generate a PDF and save it to `storage/app/temp/` directory:

```php
$order = Order::find($orderId);

// Generate different types of PDFs
$pdfPath = $this->pdfService->generateTempPdf($order, 'show_price');
$pdfPath = $this->pdfService->generateTempPdf($order, 'reservation_data');
$pdfPath = $this->pdfService->generateTempPdf($order, 'reservation_confirmation');
$pdfPath = $this->pdfService->generateTempPdf($order, 'invoice');
$pdfPath = $this->pdfService->generateTempPdf($order, 'receipt');

// $pdfPath contains the full file path: storage/app/temp/invoice_123.pdf
```

### 2. Generate and Output PDF Directly to Browser

Output PDF directly to the user's browser (inline or download):

```php
$order = Order::find($orderId);
$filename = 'INV-' . $order->order_number . '.pdf';

// Display inline in browser (I)
$this->pdfService->generateAndOutputPdf($order, 'invoice', $filename, 'I');

// Force download (D)
$this->pdfService->generateAndOutputPdf($order, 'quote', $filename, 'D');

// Save to file (F) - requires full path as filename
$this->pdfService->generateAndOutputPdf($order, 'reservation', '/path/to/file.pdf', 'F');

// Return as string (S)
$pdfContent = $this->pdfService->generateAndOutputPdf($order, 'invoice', $filename, 'S');
```

### 3. Generate Receipt PDFs

```php
// Addon receipt
$pdfPath = $this->pdfService->generateAddonReceiptPdf($orderId, $addonId);

// Payment receipt
$pdfPath = $this->pdfService->generatePaymentReceiptPdf($orderId, $paymentId);

// Warehouse receipt
$pdfPath = $this->pdfService->generateWarehouseReceiptPdf($orderId, $itemId);
```

### 4. Clean Up Temporary Files

```php
$documentsToCleanup = [
    'invoice',
    'addon_receipt_15',
    'payment_receipt_42',
    'warehouse_receipt_8'
];

$this->pdfService->cleanupTempFiles($orderId, $documentsToCleanup);
```

## Complete Example: Send PDF via Email

```php
use App\Services\PdfGeneratorService;
use Illuminate\Support\Facades\Mail;

public function sendInvoiceEmail(Request $request, $orderId)
{
    $order = Order::with(['customer'])->findOrFail($orderId);
    
    // Generate temporary PDF
    $pdfPath = $this->pdfService->generateTempPdf($order, 'invoice');
    
    if ($pdfPath && file_exists($pdfPath)) {
        // Send email with PDF attachment
        Mail::to($order->customer->email)->send(
            new InvoiceMail($order, $pdfPath)
        );
        
        // Clean up
        $this->pdfService->cleanupTempFiles($orderId, ['invoice']);
        
        return response()->json(['success' => true]);
    }
    
    return response()->json(['success' => false, 'message' => 'Failed to generate PDF'], 500);
}
```

## Complete Example: Send PDF via WhatsApp

```php
use App\Services\PdfGeneratorService;
use App\Services\WhatsAppService;

public function sendInvoiceWhatsApp(Request $request, $orderId)
{
    $order = Order::with(['customer'])->findOrFail($orderId);
    $whatsAppService = new WhatsAppService();
    
    // Generate temporary PDF
    $pdfPath = $this->pdfService->generateTempPdf($order, 'invoice');
    
    if ($pdfPath && file_exists($pdfPath)) {
        $message = "مرحباً {$order->customer->name}، إليك فاتورتك";
        
        // Send via WhatsApp
        $success = $whatsAppService->sendFile(
            $order->customer->phone,
            $pdfPath,
            $message
        );
        
        // Clean up
        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }
        
        if ($success) {
            return response()->json(['success' => true]);
        }
    }
    
    return response()->json(['success' => false], 500);
}
```

## PDF Types Available

| Type | Description | View Template |
|------|-------------|---------------|
| `show_price` | Price quote | `dashboard.orders.pdf.quote` |
| `reservation_data` | Reservation details with QR code | `dashboard.orders.pdf.reservation` |
| `reservation_confirmation` | Reservation confirmation with QR code | `dashboard.orders.pdf.reservation` |
| `invoice` | Invoice document | `dashboard.orders.pdf.invoice` |
| `receipt` | Receipt document | `dashboard.orders.pdf.receipt` |
| `quote` | Quote (via `generateAndOutputPdf`) | `dashboard.orders.pdf.quote` |

## Notes

- All PDFs are generated with RTL (right-to-left) directionality for Arabic text
- QR codes are automatically generated for reservation PDFs
- Temporary files are stored in `storage/app/temp/`
- Always clean up temporary files after use to save disk space
- The service handles all PDF configuration (fonts, margins, etc.)

## Benefits of Using the Service

1. **Reusability**: Use the same PDF generation logic across multiple controllers
2. **Consistency**: All PDFs follow the same configuration and styling
3. **Maintainability**: Update PDF logic in one place
4. **Testability**: Easy to mock and test PDF generation
5. **Clean Code**: Keep controllers thin and focused
