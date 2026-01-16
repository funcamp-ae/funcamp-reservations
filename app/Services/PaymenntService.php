<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentLink ;
class PaymenntService
{
    protected $financialDeletionService;
    protected $apiKey;
    protected $apiSecret;
    protected $apiId;
    protected $baseUrl;
    protected $isTest;

    public function __construct(FinancialDeletionService $financialDeletionService)
    {
        $this->apiKey = config('services.paymennt.api_key'); // pk_live_...
        $this->apiSecret = config('services.paymennt.api_secret'); // mer_...
        $this->apiId = config('services.paymennt.api_id'); // 17c368305a0ce997
        $this->isTest = config('services.paymennt.test_mode', true);
        $this->baseUrl = $this->isTest
            ? 'https://api.test.paymennt.com/mer/v2.0'
            : 'https://api.paymennt.com/mer/v2.0';
        $this->financialDeletionService = $financialDeletionService;
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
     * Refund a checkout (full or partial) and delete the payment record
     *
     * @param string $checkoutId
     * @param float|null $amount If null, provider will attempt full refund
     * @param string|null $currency 3-letter ISO, e.g., AED
     * @param string|null $note Optional note for the refund
     * @return array{success:bool,data?:mixed,error?:string,deleted_amount?:float}
     */
    public function refundCheckout(string $checkoutId, ?float $amount = null, ?string $currency = null, ?string $note = null)
    {
        // First, find the payment link and get payment_id from it
        $paymentLink = PaymentLink::where('checkout_id', $checkoutId)->first();
        $paymentId = null;

        if ($paymentLink && $paymentLink->payment_id) {
            $paymentId = $paymentLink->payment_id;
            
            Log::info('Retrieved payment_id from payment link', [
                'checkout_id' => $checkoutId,
                'payment_link_id' => $paymentLink->id,
                'payment_id' => $paymentId
            ]);
        } else {
            Log::warning('Payment link or payment_id not found for refund', [
                'checkout_id' => $checkoutId,
                'payment_link_exists' => $paymentLink ? true : false
            ]);
        }

        // Attempt to refund money from the gateway
        $refundResult = $this->refundMoney($checkoutId, $amount, $currency, $note);

        if (!$refundResult['success']) {
            return $refundResult;
        }

        // Update payment link status to refunded
        if ($paymentLink) {
            $paymentLink->update([
                'status' => 'refunded'
            ]);

            Log::info('Payment link status updated to refunded', [
                'checkout_id' => $checkoutId,
                'payment_link_id' => $paymentLink->id
            ]);
        }

        // If refund was successful and paymentId is available, delete the payment record
        if ($paymentId) {
            $deletionResult = $this->financialDeletionService->deletePaymentById((int) $paymentId);

            if (!$deletionResult['success']) {
                Log::warning('Refund successful but payment deletion failed', [
                    'checkout_id' => $checkoutId,
                    'payment_id' => $paymentId,
                    'deletion_message' => $deletionResult['message'] ?? 'Unknown error'
                ]);

                return [
                    'success' => true,
                    'data' => $refundResult['data'],
                    'warning' => 'Refund successful but payment deletion failed: ' . ($deletionResult['message'] ?? 'Unknown error')
                ];
            }

            Log::info('Refund and payment deletion completed successfully', [
                'checkout_id' => $checkoutId,
                'payment_id' => $paymentId,
                'deleted_amount' => $deletionResult['deleted_amount'] ?? 0
            ]);

            return [
                'success' => true,
                'data' => $refundResult['data'],
                'deleted_amount' => $deletionResult['deleted_amount'] ?? 0
            ];
        }

        // If no paymentId found, just return the refund result
        Log::info('Refund completed without payment deletion', [
            'checkout_id' => $checkoutId,
            'reason' => 'No payment_id found in payment link'
        ]);
        
        return $refundResult;
    }
}
