<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WhatsappMessageType;
use App\Models\WhatsappPlaceholder;

class WhatsappMessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'message_ar',
        'message_en',
        'is_active',
        'send_with_pdf',
        'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'send_with_pdf' => 'boolean',
    ];
    // protected static $types = [
    //     'show_price' => 'dashboard.show_price',
    //     'invoice' => 'dashboard.invoice',
    //     'receipt' => 'dashboard.receipt',
    //     'reservation_data' => 'dashboard.reservation_data',
    //     'evaluation' => 'dashboard.evaluation',
    //     'payment_link_created' => 'dashboard.payment_link_created',
    //     'payment_link_resend' => 'dashboard.payment_link_resend',
    //     'booking_reminder' => 'dashboard.booking_reminder',
    //     'booking_ending_reminder' => 'dashboard.booking_ending_reminder',
    //     'reservation_confirmation' => 'dashboard.reservation_confirmation',
    // ];
    public static function getTypes()
    {
        $types = self::getRawTypes();
        $result = [];
        foreach ($types as $type) {
            $translationKey = 'dashboard.' . $type;
            $translated = __($translationKey);
            if ($translated === $translationKey) {
                $translated = $type; // Fallback to raw type if no translation found
            }
            $result[$type] = $translated;
        }
        return $result;
    }

    // Get distinct raw types from the database
    public static function getRawTypes(): array
    {
        return WhatsappMessageType::all()->pluck('key')->toArray();
    }

    public static function addType(string $key, string $translationKey): void
    {
        WhatsappMessageType::updateOrCreate(['key' => $key]);
    }

    // Scope for active templates
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for specific type
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Get template by type (first active one)
    public static function getByType($type)
    {
        return self::active()->ofType($type)->first();
    }

    // Replace placeholders in message
    public function getProcessedMessage($language = 'ar', $serviceSiteData = null, $order = null)
    {
        $message = $language === 'en' ? $this->message_en : $this->message_ar;
        
        return $this->processMessageContent($message, $serviceSiteData, $order);
    }

    // Get bilingual message (Arabic + English)
    public function getBilingualMessage($serviceSiteData = null, $order = null, $context = null, $customerName = '')
    {
        $arabicMessage = $this->processMessageContent($this->message_ar, $serviceSiteData, $order , $customerName);
        $englishMessage = $this->processMessageContent($this->message_en, $serviceSiteData, $order, $customerName);
        
        if ($context) {
            return $arabicMessage . "<br>" . "━━━━━━━━━━━━━" . "<br>" . $englishMessage;
        }
        // Combine both messages with a separator
        return $arabicMessage . "\n\n" . "━━━━━━━━━━━━━" . "\n\n" . $englishMessage;
    }

    // Helper method to process message content
    private function processMessageContent($message, $serviceSiteData = null, $order = null, $customerName = '')
    {
        // Build variables and placeholders map
        $placeholders = $this->buildPlaceholdersFromOrder($order, $serviceSiteData , $customerName);
        \Log::info('Processing WhatsApp message template', [
            'placeholders' => $placeholders,
        ]);
        // Replace placeholders one by one to avoid edge cases
        if (!empty($placeholders)) {
            foreach ($placeholders as $needle => $replacement) {
                $message = str_replace($needle, (string) $replacement, $message);
            }
        }
        \Log::info('Processed WhatsApp message content', [
            'message' => $message,
        ]);
        // Convert HTML tags to WhatsApp formatting
        $message = str_replace(["<b>", "</b>"], "*", $message); // Bold
        $message = str_replace(["<strong>", "</strong>"], "*", $message); // Strong (bold)
        $message = str_replace(["<i>", "</i>"], "_", $message); // Italic
        $message = str_replace(["<em>", "</em>"], "_", $message); // Emphasis (italic)
        $message = str_replace(["<u>", "</u>"], "", $message); // Underline (not supported in WhatsApp)
        $message = str_replace(["<s>", "</s>"], "~", $message); // Strikethrough
        $message = str_replace(["<strike>", "</strike>"], "~", $message); // Strikethrough alternative
        $message = str_replace(["<del>", "</del>"], "~", $message); // Delete (strikethrough)
        
        // Handle HTML entities from CKEditor first
        $message = str_replace("&nbsp;", " ", $message); // Non-breaking spaces
        $message = html_entity_decode($message, ENT_QUOTES | ENT_HTML5, 'UTF-8'); // Decode all HTML entities
        
        // Handle paragraphs and line breaks
        $message = str_replace(["<p>", "</p>"], ["", "\n"], $message); // Paragraphs
        $message = str_replace("<br>", "\n", $message); // Line breaks
        $message = str_replace("<br/>", "\n", $message); // Self-closing line breaks
        $message = str_replace("<br />", "\n", $message); // Spaced self-closing line breaks
        
        // Clean up extra spaces at the beginning of lines (from CKEditor &nbsp;)
        $message = preg_replace('/\n\s+/', "\n", $message); // Remove spaces after newlines
        $message = ltrim($message); // Remove leading spaces from the entire message
        
        // Handle lists
        $message = str_replace(["<ul>", "</ul>"], ["", ""], $message); // Unordered lists
        $message = str_replace(["<ol>", "</ol>"], ["", ""], $message); // Ordered lists
        $message = str_replace(["<li>", "</li>"], ["• ", "\n"], $message); // List items
        
        // Handle links (preserve the link text and URL)
        $message = preg_replace('/<a[^>]*href=["\']([^"\']*)["\'][^>]*>([^<]*)<\/a>/', '$2 ($1)', $message);
        
        // Handle headings (convert to bold with line breaks)
        $message = str_replace(["<h1>", "</h1>"], ["*", "*\n"], $message);
        $message = str_replace(["<h2>", "</h2>"], ["*", "*\n"], $message);
        $message = str_replace(["<h3>", "</h3>"], ["*", "*\n"], $message);
        $message = str_replace(["<h4>", "</h4>"], ["*", "*\n"], $message);
        $message = str_replace(["<h5>", "</h5>"], ["*", "*\n"], $message);
        $message = str_replace(["<h6>", "</h6>"], ["*", "*\n"], $message);
        
        // Handle blockquotes
        $message = str_replace(["<blockquote>", "</blockquote>"], ["", ""], $message);
        
        // Handle code (use monospace formatting)
        $message = str_replace(["<code>", "</code>"], "```", $message);
        $message = str_replace(["<pre>", "</pre>"], ["```\n", "\n```"], $message);
        
        // Clean up any remaining HTML tags
        $message = strip_tags($message);
        
        // Clean up extra line breaks
        $message = preg_replace('/\n{3,}/', "\n\n", $message); // Max 2 consecutive line breaks
        $message = trim($message); // Remove leading/trailing whitespace
        
        return $message;
    }

    /**
     * Build the variable values used for placeholder replacement
     */
    private function buildReplacementVariables($customerName, $evaluationLink, $serviceSiteData)
    {
        return [
            'customer_name' => (string) $customerName,
            'evaluation_link' => (string) $evaluationLink,
            'service_site' => (string) $this->getServiceDataValue($serviceSiteData, 'service_site'),
            'service_location' => (string) $this->getServiceDataValue($serviceSiteData, 'service_location'),
            'worker_name_ar' => (string) $this->getServiceDataValue($serviceSiteData, 'workername_ar'),
            'worker_name_en' => (string) $this->getServiceDataValue($serviceSiteData, 'workername_en'),
            'worker_phone' => (string) $this->getServiceDataValue($serviceSiteData, 'workerphone'),
        ];
    }

    // Public accessor for default aliases (for UI usage)
    public static function getDefaultPlaceholders(): array
    {
        // Instantiate to access instance private method
        return (new self())->getDefaultPlaceholderAliases();
    }

    // Load aliases stored in the database (WhatsappPlaceholder)
    private function getDatabasePlaceholderAliases(): array
    {
        $map = [];
        try {
            $rows = WhatsappPlaceholder::all(['placeholder', 'variable_key']);
            foreach ($rows as $row) {
                if (!empty($row->placeholder) && !empty($row->variable_key)) {
                    $map[$row->placeholder] = $row->variable_key;
                }
            }
        } catch (\Throwable $e) {
            // fail-safe: if table not migrated yet, ignore
        }
        return $map;
    }

    // Safely read a field from service site data whether it's an array or object
    private function getServiceDataValue($serviceSiteData, string $key)
    {
        if (is_array($serviceSiteData)) {
            return $serviceSiteData[$key] ?? '';
        }
        if (is_object($serviceSiteData)) {
            return $serviceSiteData->$key ?? '';
        }
        return '';
    }

    /**
     * Map all supported placeholders to their corresponding variable values
     */
    private function buildPlaceholdersMap(array $vars)
    {
        // Resolve default aliases + runtime aliases to actual values
        // Merge default aliases, DB aliases, and runtime aliases
        $aliases = array_merge($this->getDefaultPlaceholderAliases(), $this->getDatabasePlaceholderAliases());
        $map = [];
        foreach ($aliases as $placeholder => $varKey) {
            $map[$placeholder] = $vars[$varKey] ?? '';
        }

        // // Also support generic bracketed variables like [price], [date], [service_location], etc.
        // foreach ($vars as $key => $value) {
        //     $placeholder = '[' . $key . ']';
        //     if (!array_key_exists($placeholder, $map)) {
        //         $map[$placeholder] = $value;
        //     }
        // }

        return $map;
    }

    // Register one alias at runtime: e.g., addPlaceholderAlias('[My Placeholder]', 'price')
    public static function addPlaceholderAlias(string $placeholder, string $variableKey): void
    {
        // Persist to DB so it is available across requests
        try {
            WhatsappPlaceholder::updateOrCreate(
                ['placeholder' => $placeholder],
                ['variable_key' => $variableKey]
            );
        } catch (\Throwable $e) {
            // If DB not ready, fall back to memory for this request
        }
    }

    // Register many aliases at runtime
    public static function addPlaceholderAliases(array $aliases): void
    {
        // $aliases = ['[My Placeholder]' => 'price', ...]
        foreach ($aliases as $placeholder => $variableKey) {
            try {
                WhatsappPlaceholder::updateOrCreate(
                    ['placeholder' => $placeholder],
                    ['variable_key' => $variableKey]
                );
            } catch (\Throwable $e) {
                // ignore DB issues and still populate in-memory cache
            }
        }
    }
    // Default human-friendly placeholder aliases mapped to variable keys
    private function getDefaultPlaceholderAliases(): array
    {
        return [
            // Customer name
            '[اسم العميل]' => 'customer_name',
            '[Customer Name]' => 'customer_name',
            "[customer's name]" => 'customer_name',
            "[Customer's Name]" => 'customer_name',

            // Evaluation link
            '[رابط التقييم]' => 'evaluation_link',
            '[🔗 رابط التقييم]' => 'evaluation_link',
            '[🔗 Evaluation Link]' => 'evaluation_link',
            '[Evaluation Link]' => 'evaluation_link',

            // Service site / location
            '[موقع المخيم]' => 'service_site',
            '[Camp Location]' => 'service_site',
            '[العنوان]' => 'service_location',
            '[Address]' => 'service_location',
            '[Camp Address]' => 'service_location',

            // Worker info
            '[اسم العامل]' => 'worker_name_ar',
            '[Worker Name]' => 'worker_name_en',
            '[رقم هاتف العامل]' => 'worker_phone',
            '[Worker Phone]' => 'worker_phone',

            // Other aliases
            // '[Terms & Conditions Link]' => 'terms_conditions_link',
        ];
    }

    /**
     * Build variables from Order model and ServiceSite data
     * - Safely extracts customer name and all relevant order attributes
     * - Merges with service site data fields
     */
    private function buildVariablesForReplacement($order = null, $serviceSiteData = null, $customerName = '')
    {
        
        $orderAttrs = [
            'customer_id', 'price', 'deposit', 'insurance_amount', 'notes', 'date', 'time_from', 'time_to',
            'time_of_receipt', 'time_of_receipt_notes', 'delivery_time', 'delivery_time_notes', 'voice_note',
            'video_note', 'image_before_receiving', 'image_after_delivery', 'status', 'refunds', 'refunds_notes',
            'delayed_time', 'inventory_withdrawal', 'insurance_status', 'confiscation_description', 'report_text',
            'show_price_notes', 'order_data_notes', 'invoice_notes', 'receipt_notes', 'people_count',
            'client_notes', 'insurance_handled_by', 'is_insurance_verified', 'partial_confiscation_amount',
            'video_note_logout', 'voice_note_logout', 'signature_path', 'signature', 'is_evaluation_sent',
        ];
        if ($order) {
            $vars = [
                'customer_name' => $order ? (string) ($order?->customer?->name ?? $customerName) : '',
                'evaluation_link' => (string) env('APP_URL').'/survey/'.$order->id,
                'terms_conditions_link' => (string) env('APP_URL').'/sign/'.$order->id,
            ];
            foreach ($orderAttrs as $attr) {
                $vars[$attr] = isset($order->$attr) ? (string) $order->$attr : '';
            }
        }

        $svcAttrs = [
            'service_site', 'service_location', 'workername_ar', 'workername_en', 'workerphone',
            'guidance_images', 'guidance_videos', 'important_notes',
        ];
        if ($serviceSiteData) {
            foreach ($svcAttrs as $attr) {
                $value = $serviceSiteData[$attr] ?? '';
                // Convert arrays to comma-separated strings for placeholders
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $vars[$attr] = (string) $value;
            }
        }
        if (!$serviceSiteData && !$order) {
            $vars = array_merge($svcAttrs, $orderAttrs , ['customer_name' , 'evaluation_link' , 'terms_conditions_link']);
        }

        return $vars;
    }

    /**
     * Public helper: build full placeholders map from Order and ServiceSite data
     */
    public function buildPlaceholdersFromOrder($order = null, $serviceSiteData = null, $customerName = '', $evaluationLink = '')
    {
        $vars = $this->buildVariablesForReplacement($order, $serviceSiteData , $customerName);
        return $this->buildPlaceholdersMap($vars);
    }
}
