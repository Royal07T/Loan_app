<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackPaymentService implements PaymentGatewayInterface
{
    protected $secretKey;
    protected $publicKey;
    protected $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
        $this->publicKey = config('services.paystack.public_key');
    }

    /**
     * Initialize a payment transaction
     */
    public function initializePayment(array $data): array
    {
        try {
            $payload = [
                'email' => $data['email'],
                'amount' => $data['amount'] * 100, // Convert to kobo
                'currency' => 'NGN',
                'reference' => $data['reference'],
                'callback_url' => $data['callback_url'] ?? route('payment.callback', ['gateway' => 'paystack']),
                'metadata' => [
                    'loan_id' => $data['loan_id'] ?? null,
                    'user_id' => $data['user_id'] ?? null,
                    'payment_type' => $data['payment_type'] ?? 'loan_repayment',
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/transaction/initialize', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'authorization_url' => $data['data']['authorization_url'],
                    'reference' => $data['data']['reference'],
                    'access_code' => $data['data']['access_code'],
                ];
            }

            Log::error('Paystack payment initialization failed', [
                'response' => $response->json(),
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'message' => 'Failed to initialize payment',
                'error' => $response->json()['message'] ?? 'Unknown error'
            ];

        } catch (\Exception $e) {
            Log::error('Paystack payment initialization error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Payment initialization failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(string $reference): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
            ])->get($this->baseUrl . '/transaction/verify/' . $reference);

            if ($response->successful()) {
                $data = $response->json();
                $transaction = $data['data'];

                return [
                    'success' => true,
                    'status' => $transaction['status'],
                    'amount' => $transaction['amount'] / 100, // Convert from kobo
                    'currency' => $transaction['currency'],
                    'reference' => $transaction['reference'],
                    'gateway_ref' => $transaction['id'],
                    'metadata' => $transaction['metadata'] ?? [],
                    'paid_at' => $transaction['paid_at'] ?? null,
                ];
            }

            Log::error('Paystack payment verification failed', [
                'reference' => $reference,
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Payment verification failed',
                'error' => $response->json()['message'] ?? 'Unknown error'
            ];

        } catch (\Exception $e) {
            Log::error('Paystack payment verification error', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Payment verification failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process a refund
     */
    public function refundPayment(string $reference, float $amount): array
    {
        try {
            $payload = [
                'transaction' => $reference,
                'amount' => $amount * 100, // Convert to kobo
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/refund', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'refund_id' => $data['data']['id'],
                    'status' => $data['data']['status'],
                    'amount' => $data['data']['amount'] / 100,
                ];
            }

            return [
                'success' => false,
                'message' => 'Refund failed',
                'error' => $response->json()['message'] ?? 'Unknown error'
            ];

        } catch (\Exception $e) {
            Log::error('Paystack refund error', [
                'reference' => $reference,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Refund failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $reference): string
    {
        $verification = $this->verifyPayment($reference);
        
        if ($verification['success']) {
            return $verification['status'];
        }
        
        return 'failed';
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return ['NGN'];
    }

    /**
     * Get gateway name
     */
    public function getGatewayName(): string
    {
        return 'Paystack';
    }
} 