<?php

namespace App\Services;

class CryptoService
{
    protected $exchangeRates = [
        'BTC' => 50000, // Example rate in USD
        'ETH' => 3500,
        'USDT' => 1,
    ];

    public function fetchExchangeRate($currency)
    {
        return $this->exchangeRates[$currency] ?? null;
    }
}
