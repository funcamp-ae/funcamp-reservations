<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentLink ;
class PaymenntService
{
    protected $refundService;
    protected $apiKey;
    protected $apiSecret;
    protected $apiId;
    protected $baseUrl;
    protected $isTest;

    public function __construct(RefundService $refundService)
    {
        $this->apiKey = config('services.paymennt.api_key'); // pk_live_...
        $this->apiSecret = config('services.paymennt.api_secret'); // mer_...
        $this->apiId = config('services.paymennt.api_id'); // 17c368305a0ce997
        $this->isTest = config('services.paymennt.test_mode', true);
        $this->baseUrl = $this->isTest
            ? 'https://api.test.paymennt.com/mer/v2.0'
            : 'https://api.paymennt.com/mer/v2.0';
        $this->refundService = $refundService;
    }

    /**
     * Get HTTP client with proper authentication headers
     */
    private function getHttpClient()
    {
        return Http::withHeaders([
            'X-Paymennt-Api-Key' => $this->apiId, // Use the short API ID
            'X-Paymennt-Api-Secret' => $this->apiSecret, // Use the API secret
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]);
    }

    /**
     * إنشاء رابط دفع جديد
     */
    public function createPaymentLink($data)
    {
        try {
            // Ensure customer data is properly set
            $customerData = [
                'id' => (string)($data['customer_id'] ?? '1'),
                'firstName' => $data['customer_name'] ?? 'Customer',
                'lastName' => $data['customer_last_name'] ?? 'Customer',
                'email' => $data['customer_email'] ?? 'customer@example.com',
                'phone' => $data['customer_phone'] ?? '+971500000000'
            ];

            // Debug customer data
            Log::info('Customer data before API call', $customerData);

            $payload = [
                'requestId' => $data['request_id'] ?? uniqid('PAY-'),
                'orderId' => 'funcamp_'.$data['order_id'] ?? uniqid('funcamp-'),
                'description' => $data['description'] ?? 'Payment Link',
                'currency' => $data['currency'] ?? 'AED',
                'amount' => $data['amount'],
                'sendSms' => $data['send_sms'] ?? false,
                'sendEmail' => $data['send_email'] ?? false,
                'customer' => $customerData,
                'billingAddress' => [
                    'name' => ($data['customer_name'] ?? 'Customer') . ' ' . ($data['customer_last_name'] ?? 'Customer'),
                    'address1' => $data['address'] ?? 'Address',
                    'city' => $data['city'] ?? 'Dubai',
                    'state' => $data['state'] ?? 'Dubai',
                    'zip' => $data['zip'] ?? '00000',
                    'country' => $data['country'] ?? 'AE'
                ],
                'language' => app()->getLocale() === 'ar' ? 'ar' : 'en',
                'metadata' => [
                    'payment_id' => $data['payment_id'] ?? null
                ]
            ];

            // إضافة العناصر إذا كانت متوفرة
            if (isset($data['items']) && is_array($data['items'])) {
                $payload['items'] = $data['items'];
            }

            // إضافة العنوان إذا كان مختلفاً
            if (isset($data['delivery_address'])) {
                $payload['deliveryAddress'] = $data['delivery_address'];
            }

            // إضافة انتهاء الصلاحية
            if (isset($data['expires_in'])) {
                $payload['expiresIn'] = $data['expires_in'];
            }

            Log::info('Paymennt API Request', [
                'url' => $this->baseUrl . '/checkout/link',
                'payload' => $payload,
                'headers' => [
                    'X-Paymennt-Api-Key' => $this->apiId,
                    'X-Paymennt-Api-Secret' => substr($this->apiSecret, 0, 20) . '...'
                ]
            ]);

            $response = $this->getHttpClient()
                ->post($this->baseUrl . '/checkout/link', $payload);

            Log::info('Paymennt API Response', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if ($result['success']) {
                    return [
                        'success' => true,
                        'data' => $result['result'],
                        'checkout_url' => $result['result']['redirectUrl'] ?? null,
                        'checkout_id' => $result['result']['id'] ?? null,
                        'checkout_key' => $result['result']['checkoutKey'] ?? null
                    ];
                }
            }

            Log::error('Paymennt API Error', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create payment link',
                'details' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Paymennt Service Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * الحصول على حالة الدفع
     */
    public function getCheckoutStatus($checkoutId)
    {
        try {
            $response = $this->getHttpClient()
                ->get($this->baseUrl . '/checkout/' . $checkoutId);

            if ($response->successful()) {
                $result = $response->json();
                if ($result['success']) {
                    return [
                        'success' => true,
                        'data' => $result['result']
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Failed to get checkout status'
            ];

        } catch (\Exception $e) {
            Log::error('Paymennt Get Status Error', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * إلغاء رابط الدفع
     */
    public function cancelCheckout($checkoutId)
    {
        try {
            $response = $this->getHttpClient()
                ->post($this->baseUrl . '/checkout/' . $checkoutId . '/cancel');

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => $result['success'] ?? false,
                    'data' => $result['result'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to cancel checkout'
            ];

        } catch (\Exception $e) {
            Log::error('Paymennt Cancel Error', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * إعادة إرسال رابط الدفع
     */
    public function resendCheckout($checkoutId)
    {
        try {
            $response = $this->getHttpClient()
                ->post($this->baseUrl . '/checkout/' . $checkoutId . '/resend');

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => $result['success'] ?? false,
                    'data' => $result['result'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to resend checkout'
            ];

        } catch (\Exception $e) {
            Log::error('Paymennt Resend Error', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Refund money from Paymennt gateway
     *
     * @param string $checkoutId
     * @param float|null $amount If null, provider will attempt full refund
     * @param string|null $currency 3-letter ISO, e.g., AED
     * @param string|null $note Optional note for the refund
     * @return array{success:bool,data?:mixed,error?:string}
     */
    private function refundMoney(string $checkoutId, ?float $amount = null, ?string $currency = null, ?string $note = null)
    {
        try {
            // According to docs, parameters are sent as query parameters
            $query = [];
            if (!is_null($amount)) {
                $query['amount'] = $amount;
            }
            if (!is_null($currency)) {
                $query['currency'] = $currency;
            }
            if (!is_null($note)) {
                $query['note'] = $note;
            } else {
                $query['note'] = 'the reservation was canceled';
            }

            $url = $this->baseUrl . '/checkout/' . $checkoutId . '/refund';
            if (!empty($query)) {
                $url .= '?' . http_build_query($query);
            }

            Log::info('Paymennt refund request', [
                'url' => $url,
                'query' => $query,
            ]);

            // Send POST with query string (no JSON body needed according to docs)
            $response = $this->getHttpClient()->post($url);

            Log::info('Paymennt refund response', [
                'status' => $response->status(),
                'json' => $response->json(),
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => $result['success'] ?? false,
                    'data' => $result['result'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to refund checkout',
            ];
        } catch (\Exception $e) {
            Log::error('Paymennt Refund Error', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Service error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Refund a checkout (full or partial) using RefundService
     *
     * @param string $checkoutId
     * @param float|null $amount If null, provider will attempt full refund
     * @param string|null $currency 3-letter ISO, e.g., AED
     * @param string|null $note Optional note for the refund
     * @return array{success:bool,data?:mixed,error?:string,refund_info?:array}
     */
    public function refundCheckout(string $checkoutId, ?float $amount = null, ?string $currency = null, ?string $note = null)
    {
        // Find the payment link
        $paymentLink = PaymentLink::where('checkout_id', $checkoutId)->first();

        if (!$paymentLink) {
            Log::error('Payment link not found for refund', [
                'checkout_id' => $checkoutId
            ]);
            return [
                'success' => false,
                'error' => 'Payment link not found'
            ];
        }

        // If amount is null, refund the full remaining amount
        if ($amount === null) {
            $currentRefundedAmount = $paymentLink->refunded_amount ?? 0;
            $amount = $paymentLink->amount - $currentRefundedAmount;
        }

        // Validate refund request using RefundService
        $validation = $this->refundService->validateRefundRequest($paymentLink, $amount);
        if (!$validation['valid']) {
            Log::error('Refund validation failed', [
                'checkout_id' => $checkoutId,
                'errors' => $validation['errors']
            ]);
            return [
                'success' => false,
                'error' => implode(', ', $validation['errors']),
                'validation' => $validation
            ];
        }

        // Attempt to refund money from the gateway
        $refundResult = $this->refundMoney($checkoutId, $amount, $currency, $note);

        if (!$refundResult['success']) {
            Log::error('Gateway refund failed', [
                'checkout_id' => $checkoutId,
                'error' => $refundResult['error'] ?? 'Unknown error'
            ]);
            return $refundResult;
        }
        // Process refund using RefundService (handles payment link and payment record updates)
        $processResult = $this->refundService->processRefund($paymentLink, $amount, [
            'currency' => $currency,
            'note' => $note,
            'gateway_response' => $refundResult['data'] ?? null
        ]);

        if (!$processResult['success']) {
            Log::error('Refund processing failed after gateway success', [
                'checkout_id' => $checkoutId,
                'error' => $processResult['error'] ?? 'Unknown error'
            ]);
            return [
                'success' => false,
                'error' => $processResult['error'] ?? 'Failed to process refund',
                'gateway_refund_successful' => true,
                'warning' => 'Gateway refund succeeded but local processing failed. Manual intervention may be required.'
            ];
        }

        Log::info('Refund completed successfully', [
            'checkout_id' => $checkoutId,
            'refund_type' => $processResult['refund_type'] ?? 'unknown',
            'refunded_amount' => $processResult['refunded_amount'] ?? 0,
            'total_refunded_amount' => $processResult['total_refunded_amount'] ?? 0,
            'payment_link_status' => $processResult['payment_link_status'] ?? 'unknown'
        ]);

        return [
            'success' => true,
            'data' => $refundResult['data'],
            'refund_info' => $processResult
        ];
    }
}
