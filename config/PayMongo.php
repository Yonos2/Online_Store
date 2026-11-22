<?php

final class PayMongo
{
    private const API_BASE_URL = 'https://api.paymongo.com/v1';

    private string $secretKey;

    public function __construct()
    {
        $secretKey = getenv('PAYMONGO_SECRET_KEY');
        if (!is_string($secretKey) || $secretKey === '') {
            throw new RuntimeException(
                'Online payments are not configured yet. Please choose Cash on Delivery or contact the store.'
            );
        }

        if (!str_starts_with($secretKey, 'sk_test_')) {
            throw new RuntimeException(
                'Online payments are configured for test mode only.'
            );
        }

        $this->secretKey = $secretKey;
    }

    public static function isConfigured(): bool
    {
        $secretKey = getenv('PAYMONGO_SECRET_KEY');
        return is_string($secretKey) && str_starts_with($secretKey, 'sk_test_');
    }

    public static function baseUrl(): string
    {
        $baseUrl = getenv('APP_BASE_URL');
        if (!is_string($baseUrl) || $baseUrl === '') {
            return 'http://localhost/online_store';
        }

        return rtrim($baseUrl, '/');
    }

    public function createCheckoutSession(
        string $orderNumber,
        int $orderId,
        int $customerId,
        array $items,
        int $totalAmount
    ): array {
        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = [
                'currency' => 'PHP',
                'amount' => (int) round((float) $item['price'] * 100),
                'name' => substr((string) $item['product_name'], 0, 250),
                'quantity' => (int) $item['quantity']
            ];
        }

        return $this->request('POST', '/checkout_sessions', [
            'data' => [
                'attributes' => [
                    'line_items' => $lineItems,
                    'payment_method_types' => [
                        'card',
                        'gcash',
                        'paymaya'
                    ],
                    'description' => 'Online Store order ' . $orderNumber,
                    'reference_number' => $orderNumber,
                    'success_url' => self::baseUrl()
                        . '/payments/return?order='
                        . rawurlencode($orderNumber),
                    'cancel_url' => self::baseUrl()
                        . '/payments/return?result=cancelled&order='
                        . rawurlencode($orderNumber),
                    'send_email_receipt' => false,
                    'show_description' => true,
                    'show_line_items' => true,
                    'metadata' => [
                        'order_id' => (string) $orderId,
                        'order_number' => $orderNumber,
                        'customer_id' => (string) $customerId,
                        'amount_php_minor' => (string) $totalAmount
                    ]
                ]
            ]
        ]);
    }

    public function retrieveCheckoutSession(string $sessionId): array
    {
        if (!preg_match('/^cs_[A-Za-z0-9]+$/', $sessionId)) {
            throw new RuntimeException('Invalid payment session.');
        }

        return $this->request(
            'GET',
            '/checkout_sessions/' . rawurlencode($sessionId)
        );
    }

    public static function isPaid(array $session): bool
    {
        $attributes = $session['data']['attributes'] ?? [];
        if (($attributes['status'] ?? '') === 'paid') {
            return true;
        }

        foreach (($attributes['payments'] ?? []) as $payment) {
            if (($payment['attributes']['status'] ?? '') === 'paid') {
                return true;
            }
        }

        return false;
    }

    public static function checkoutUrl(array $session): ?string
    {
        $url = $session['data']['attributes']['checkout_url'] ?? null;
        if (
            !is_string($url) ||
            parse_url($url, PHP_URL_SCHEME) !== 'https' ||
            parse_url($url, PHP_URL_HOST) !== 'checkout.paymongo.com'
        ) {
            return null;
        }

        return $url;
    }

    public static function paymentId(array $session): ?string
    {
        foreach (($session['data']['attributes']['payments'] ?? []) as $payment) {
            if (
                ($payment['attributes']['status'] ?? '') === 'paid' &&
                is_string($payment['id'] ?? null)
            ) {
                return $payment['id'];
            }
        }

        return null;
    }

    private function request(
        string $method,
        string $path,
        ?array $payload = null
    ): array {
        $curl = curl_init(self::API_BASE_URL . $path);
        if ($curl === false) {
            throw new RuntimeException('Unable to initialize the payment service.');
        }

        $headers = ['Accept: application/json'];
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_USERPWD => $this->secretKey . ':',
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 25
        ];

        if ($payload !== null) {
            $encodedPayload = json_encode($payload, JSON_THROW_ON_ERROR);
            $headers[] = 'Content-Type: application/json';
            $options[CURLOPT_POSTFIELDS] = $encodedPayload;
        }

        $options[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($curl, $options);
        $responseBody = curl_exec($curl);
        $httpStatus = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if (!is_string($responseBody)) {
            error_log('PayMongo request failed: ' . $curlError);
            throw new RuntimeException(
                'The payment service is temporarily unavailable. Please try again.'
            );
        }

        try {
            $response = json_decode(
                $responseBody,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $ex) {
            error_log('PayMongo returned invalid JSON: ' . $ex->getMessage());
            throw new RuntimeException(
                'The payment service returned an invalid response.'
            );
        }

        if ($httpStatus < 200 || $httpStatus >= 300 || !is_array($response)) {
            $providerMessage = $response['errors'][0]['detail'] ?? null;
            error_log(
                'PayMongo API error (HTTP ' . $httpStatus . '): '
                . (is_string($providerMessage) ? $providerMessage : 'Unknown error')
            );
            throw new RuntimeException(
                'Unable to start online payment. Please check your payment details or try again.'
            );
        }

        return $response;
    }
}
