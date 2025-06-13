<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Illuminate\Support\Facades\Log;

class StripePaymentService implements PaymentGatewayInterface
{
    protected $secretKey;
    protected $publicKey;

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret');
        $this->publicKey = config('services.stripe.key');
        Stripe::setApiKey($this->secretKey);
    }

    /**
     * Initialize a payment transaction
     */
    public function initializePayment(array $data): array
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $data['amount'] * 100, // Convert to cents
                'currency' => strtolower($data['currency'] ?? 'usd'),
                'metadata' => [
                    'loan_id' => $data['loan_id'] ?? null,
                    'user_id' => $data['user_id'] ?? null,
                    'payment_type' => $data['payment_type'] ?? 'loan_repayment',
                    'reference' => $data['reference'] ?? null,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'reference' => $data['reference'] ?? $paymentIntent->id,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
            ];

        } catch (\Exception $e) {
            Log::error('Stripe payment initialization error', ['error' => $e->getMessage()]);
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
            $paymentIntent = PaymentIntent::retrieve($reference);

            return [
                'success' => true,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'reference' => $paymentIntent->id,
                'gateway_ref' => $paymentIntent->id,
                'metadata' => $paymentIntent->metadata->toArray(),
                'paid_at' => $paymentIntent->created,
                'payment_method' => $paymentIntent->payment_method,
            ];

        } catch (\Exception $e) {
            Log::error('Stripe payment verification error', [
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
            $refund = Refund::create([
                'payment_intent' => $reference,
                'amount' => $amount * 100, // Convert to cents
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount / 100,
                'currency' => $refund->currency,
            ];

        } catch (\Exception $e) {
            Log::error('Stripe refund error', [
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
        try {
            $paymentIntent = PaymentIntent::retrieve($reference);
            return $paymentIntent->status;
        } catch (\Exception $e) {
            Log::error('Stripe payment status check error', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);
            return 'failed';
        }
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return ['usd', 'eur', 'gbp', 'cad', 'aud', 'jpy'];
    }

    /**
     * Get gateway name
     */
    public function getGatewayName(): string
    {
        return 'Stripe';
    }

    /**
     * Create a payment method
     */
    public function createPaymentMethod(array $data): array
    {
        try {
            $paymentMethod = \Stripe\PaymentMethod::create([
                'type' => 'card',
                'card' => [
                    'number' => $data['card_number'],
                    'exp_month' => $data['exp_month'],
                    'exp_year' => $data['exp_year'],
                    'cvc' => $data['cvc'],
                ],
            ]);

            return [
                'success' => true,
                'payment_method_id' => $paymentMethod->id,
            ];

        } catch (\Exception $e) {
            Log::error('Stripe payment method creation error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Payment method creation failed',
                'error' => $e->getMessage()
            ];
        }
    }
} 