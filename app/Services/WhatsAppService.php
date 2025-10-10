<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $instanceId;
    protected $apiUrl;
    protected $token;

    public function __construct()
    {
        $this->instanceId = config('services.hypersender.instance_id');
        $this->apiUrl = config('services.hypersender.api_url', 'https://app.hypersender.com');
        $this->token = config('services.hypersender.token');
    }

    /**
     * Send a text message via WhatsApp using Send Text Safe endpoint
     * URL: https://app.hypersender.com/api/whatsapp/v1/:instance/send-text-safe
     * Based on: https://docs.hypersender.com/docs/whatsapp/wa-send-text-safe-message
     */
    public function sendTextMessage($phoneNumber, $message)
    {
        try {
            $response = Http::withToken($this->token)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl . '/api/whatsapp/v1/' . $this->instanceId . '/send-text-safe', [
                    'chatId' => $this->formatPhoneNumber($phoneNumber),
                    'text' => $message
                ]);

            Log::info('WhatsApp text message sent', [
                'phone' => $phoneNumber,
                'chat_id' => $this->formatPhoneNumber($phoneNumber),
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'response_json' => $response->json(),
                'headers' => $response->headers()
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp text message failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send a file via WhatsApp using Send File endpoint
     * URL: https://app.hypersender.com/api/whatsapp/v1/:instance/send-file
     * Based on: https://docs.hypersender.com/docs/whatsapp/wa-send-file
     */
    /**
     * Send a file via WhatsApp with retry logic
     * 
     * @param string $phoneNumber Recipient's phone number
     * @param string $filePath Path to the file to send
     * @param string|null $caption Optional caption for the file
     * @param int $maxRetries Maximum number of retry attempts (default: 2)
     * @param int $initialDelay Initial delay between retries in seconds (default: 1)
     * @return bool True if sent successfully, false otherwise
     */
    public function sendFile($phoneNumber, $filePath, $caption = null, $maxRetries = 2, $initialDelay = 1)
    {
        $attempt = 0;
        $lastError = null;
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);

        // Initial validation
        if (!file_exists($filePath) || !is_readable($filePath)) {
            Log::error('WhatsApp file not found or not readable', [
                'phone' => $phoneNumber,
                'file_path' => $filePath,
                'file_exists' => file_exists($filePath),
                'is_readable' => is_readable($filePath)
            ]);
            return false;
        }

        // Check file size (5MB limit for WhatsApp)
        $fileSize = filesize($filePath);
        $maxSize = 5 * 1024 * 1024; // 5MB in bytes
        
        if ($fileSize > $maxSize) {
            Log::error('WhatsApp file too large', [
                'phone' => $phoneNumber,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'max_size' => $maxSize
            ]);
            return false;
        }

        // Get file info for validation
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $filePath);
        finfo_close($fileInfo);

        // Allowed MIME types for WhatsApp
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'text/plain'
        ];

        if (!in_array($mimeType, $allowedTypes)) {
            Log::error('WhatsApp file type not allowed', [
                'phone' => $phoneNumber,
                'file_path' => $filePath,
                'mime_type' => $mimeType,
                'allowed_types' => $allowedTypes
            ]);
            return false;
        }

        do {
            $attempt++;
            $delay = $initialDelay * pow(2, $attempt - 1); // Exponential backoff
            
            try {
                Log::info('Sending WhatsApp file attempt', [
                    'attempt' => $attempt,
                    'max_attempts' => $maxRetries + 1,
                    'phone' => $phoneNumber,
                    'file' => $filePath
                ]);

                // Make the API request
                $response = Http::withToken($this->token)
                    ->timeout(30)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'X-Attempt' => $attempt,
                    ])
                    ->attach('file', file_get_contents($filePath), basename($filePath), [
                        'Content-Type' => $mimeType
                    ])
                    ->post($this->apiUrl . '/api/whatsapp/v1/' . $this->instanceId . '/send-file', [
                        'chatId' => $formattedPhone,
                        'caption' => $caption
                    ]);

                $statusCode = $response->status();
                $responseData = $response->json();

                // Log the attempt
                Log::info('WhatsApp API response', [
                    'attempt' => $attempt,
                    'status' => $statusCode,
                    'response' => $responseData,
                    'phone' => $phoneNumber,
                    'file' => $filePath
                ]);

                // Success case
                if ($statusCode >= 200 && $statusCode < 300) {
                    return true;
                }

                // Handle rate limiting (429) with retry
                if ($statusCode === 429) {
                    $retryAfter = (int)($response->header('Retry-After') ?: $delay);
                    Log::warning('Rate limited, will retry after delay', [
                        'retry_after' => $retryAfter,
                        'attempt' => $attempt,
                        'max_attempts' => $maxRetries
                    ]);
                    sleep($retryAfter);
                    continue;
                }

                // For server errors (5xx), log and retry
                if ($statusCode >= 500 && $statusCode < 600) {
                    $lastError = [
                        'status' => $statusCode,
                        'response' => $responseData,
                        'attempt' => $attempt
                    ];
                    Log::warning('Server error, will retry', $lastError);
                    
                    if ($attempt <= $maxRetries) {
                        sleep($delay);
                        continue;
                    }
                }

                // For other errors, log and fail
                Log::error('WhatsApp API error', [
                    'status' => $statusCode,
                    'response' => $responseData,
                    'attempt' => $attempt,
                    'max_attempts' => $maxRetries
                ]);
                return false;

            } catch (\Exception $e) {
                $lastError = [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                    'max_attempts' => $maxRetries
                ];
                
                // Only retry on connection/timeout errors
                if (!($e instanceof \Illuminate\Http\Client\ConnectionException || 
                      $e instanceof \Illuminate\Http\Client\RequestException)) {
                    Log::error('Non-retryable error sending WhatsApp file', $lastError);
                    return false;
                }

                Log::warning('Retryable error sending WhatsApp file', $lastError);
                
                if ($attempt <= $maxRetries) {
                    sleep($delay);
                    continue;
                }
                
                Log::error('Max retries reached for WhatsApp file send', [
                    'phone' => $phoneNumber,
                    'file' => $filePath,
                    'attempts' => $attempt,
                    'last_error' => $lastError
                ]);
                return false;
            }
            
        } while ($attempt <= $maxRetries);

        // If we get here, all retries failed
        Log::error('All WhatsApp send attempts failed', [
            'phone' => $phoneNumber,
            'file' => $filePath,
            'attempts' => $attempt,
            'last_error' => $lastError ?? 'Unknown error'
        ]);
        
        return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp file send failed', [
                'phone' => $phoneNumber,
                'file' => $filePath ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send a link with preview via WhatsApp using Send Link Preview endpoint
     * URL: https://app.hypersender.com/api/whatsapp/v1/:instance/send-link-preview
     * Based on: https://docs.hypersender.com/docs/whatsapp/wa-send-link-preview
     */
    public function sendLinkPreview($phoneNumber, $url, $message = null)
    {
        try {
            $response = Http::withToken($this->token)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl . '/api/whatsapp/v1/' . $this->instanceId . '/send-link-preview', [
                    'chatId' => $this->formatPhoneNumber($phoneNumber),
                    'url' => $url,
                    'title' => $message
                ]);

            Log::info('WhatsApp link preview sent', [
                'phone' => $phoneNumber,
                'chat_id' => $this->formatPhoneNumber($phoneNumber),
                'url' => $url,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'response_json' => $response->json(),
                'headers' => $response->headers()
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp link preview failed', [
                'phone' => $phoneNumber,
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Format phone number for WhatsApp chat_id (country_code + phone_number + @c.us)
     */
    protected function formatPhoneNumber($phoneNumber)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If phone doesn't start with country code, assume UAE (+971)
        // if (!str_starts_with($phone, '971') && !str_starts_with($phone, '+971')) {
        //     // Remove leading zero if present
        //     $phone = ltrim($phone, '0');
        //     $phone = '971' . $phone;
        // }
        
        // Remove + if present
        $phone = ltrim($phone, '+');
        
        // Format as WhatsApp chat_id
        return $phone . '@c.us';
    }

    /**
     * Get formatted phone number with + prefix for display
     */
    public function getDisplayPhoneNumber($phoneNumber)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If phone doesn't start with country code, assume UAE (+971)
        // if (!str_starts_with($phone, '971') && !str_starts_with($phone, '+971')) {
        //     // Remove leading zero if present
        //     $phone = ltrim($phone, '0');
        //     $phone = '971' . $phone;
        // }
        
        // Remove + if present
        $phone = ltrim($phone, '+');
        
        return $phone;
    }
}
