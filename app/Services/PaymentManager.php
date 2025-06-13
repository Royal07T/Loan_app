<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\PaymentTransaction;

class PaymentManager
{
    protected $gateways = [];

    public function __construct()
    {
        $this->gateways = [
            'paystack' => new PaystackPaymentService(),
            'stripe' => new StripePaymentService(),
        ];
    }

    /**
     * Initialize payment with the appropriate gateway
     */
    public function initializePayment(array $data): array
    {
        $gateway = $this->getGatewayForCurrency($data['currency'] ?? 'NGN');
        
        if (!$gateway) {
            return [
                'success' => false,
                'message' => 'No suitable payment gateway found for this currency'
            ];
        }

        // Generate unique reference
        $data['reference'] = $data['reference'] ?? $this->generateReference();
        
        // Initialize payment
        $result = $gateway->initializePayment($data);
        
        if ($result['success']) {
            // Log transaction
            PaymentTransaction::create([
                'user_id' => $data['user_id'],
                'loan_id' => $data['loan_id'] ?? null,
                'gateway' => $gateway->getGatewayName(),
                'reference' => $result['reference'],
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'NGN',
                'status' => 'pending',
                'metadata' => $data,
            ]);
        }

        return $result;
    }

    /**
     * Verify payment with the appropriate gateway
     */
    public function verifyPayment(string $reference, string $gateway = null): array
    {
        // Find transaction to determine gateway if not provided
        if (!$gateway) {
            $transaction = PaymentTransaction::where('reference', $reference)->first();
            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found'
                ];
            }
            $gateway = $this->getGatewayByName($transaction->gateway);
        } else {
            $gateway = $this->gateways[$gateway] ?? null;
        }

        if (!$gateway) {
            return [
                'success' => false,
                'message' => 'Invalid payment gateway'
            ];
        }

        $result = $gateway->verifyPayment($reference);
        
        if ($result['success']) {
            // Update transaction status
            $transaction = PaymentTransaction::where('reference', $reference)->first();
            if ($transaction) {
                $transaction->update([
                    'status' => $result['status'],
                    'gateway_reference' => $result['gateway_ref'] ?? null,
                    'paid_at' => $result['paid_at'] ?? now(),
                    'metadata' => array_merge($transaction->metadata ?? [], $result),
                ]);
            }
        }

        return $result;
    }

    /**
     * Process refund
     */
    public function refundPayment(string $reference, float $amount): array
    {
        $transaction = PaymentTransaction::where('reference', $reference)->first();
        
        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Transaction not found'
            ];
        }

        $gateway = $this->getGatewayByName($transaction->gateway);
        
        if (!$gateway) {
            return [
                'success' => false,
                'message' => 'Invalid payment gateway'
            ];
        }

        return $gateway->refundPayment($reference, $amount);
    }

    /**
     * Get gateway for currency
     */
    protected function getGatewayForCurrency(string $currency): ?PaymentGatewayInterface
    {
        $currency = strtoupper($currency);
        
        foreach ($this->gateways as $gateway) {
            if (in_array(strtolower($currency), array_map('strtolower', $gateway->getSupportedCurrencies()))) {
                return $gateway;
            }
        }
        
        return null;
    }

    /**
     * Get gateway by name
     */
    protected function getGatewayByName(string $name): ?PaymentGatewayInterface
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->getGatewayName() === $name) {
                return $gateway;
            }
        }
        
        return null;
    }

    /**
     * Generate unique reference
     */
    protected function generateReference(): string
    {
        return 'PAY_' . time() . '_' . rand(1000, 9999);
    }

    /**
     * Get available gateways
     */
    public function getAvailableGateways(): array
    {
        $gateways = [];
        
        foreach ($this->gateways as $key => $gateway) {
            $gateways[$key] = [
                'name' => $gateway->getGatewayName(),
                'currencies' => $gateway->getSupportedCurrencies(),
            ];
        }
        
        return $gateways;
    }

    /**
     * Get transaction by reference
     */
    public function getTransaction(string $reference): ?PaymentTransaction
    {
        return PaymentTransaction::where('reference', $reference)->first();
    }

    /**
     * Get user's payment history
     */
    public function getUserPaymentHistory(int $userId): array
    {
        return PaymentTransaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
} 