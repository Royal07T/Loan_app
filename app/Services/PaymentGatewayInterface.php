<?php

namespace App\Services;

interface PaymentGatewayInterface
{
    /**
     * Initialize a payment transaction
     */
    public function initializePayment(array $data): array;

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(string $reference): array;

    /**
     * Process a refund
     */
    public function refundPayment(string $reference, float $amount): array;

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $reference): string;

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array;

    /**
     * Get gateway name
     */
    public function getGatewayName(): string;
} 