<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

class WalletService
{
    protected $web3;
    protected $infuraUrl;

    public function __construct()
    {
        $this->infuraUrl = config('services.infura.url', 'https://mainnet.infura.io/v3/9ab0bb56187947f9a0212b58eaedcc65');
        $this->web3 = new Web3(
            new HttpProvider(
                new HttpRequestManager($this->infuraUrl)
            )
        );
    }

    /**
     * Create or get user wallet
     */
    public function getUserWallet(User $user): Wallet
    {
        $wallet = $user->wallet;
        
        if (!$wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'name' => 'Primary Wallet',
                'wallet_type' => 'external',
                'is_active' => true,
                'balance' => 0,
                'fiat_balance' => 0,
            ]);
        }
        
        return $wallet;
    }

    /**
     * Connect MetaMask wallet
     */
    public function connectMetaMask(User $user, string $walletAddress): Wallet
    {
        $wallet = $this->getUserWallet($user);
        
        // Validate Ethereum address format
        if (!$this->isValidEthereumAddress($walletAddress)) {
            throw new \InvalidArgumentException('Invalid Ethereum address format');
        }
        
        $wallet->update([
            'wallet_address' => $walletAddress,
            'wallet_type' => 'metamask',
            'is_active' => true,
        ]);
        
        return $wallet;
    }

    /**
     * Get wallet balance from blockchain
     */
    public function getWalletBalance(string $walletAddress): float
    {
        try {
            $eth = $this->web3->eth;
            $result = null;

            $eth->getBalance($walletAddress, function ($err, $balance) use (&$result) {
                if ($err !== null) {
                    Log::error('Failed to fetch wallet balance', ['error' => $err->getMessage()]);
                    $result = 0;
                    return;
                }
                
                // Convert from Wei to ETH
                $balanceInEth = bcdiv($balance->toString(), bcpow('10', '18', 18), 18);
                $result = (float) $balanceInEth;
            });

            usleep(500000); // Wait for callback
            return $result ?? 0;
            
        } catch (\Exception $e) {
            Log::error('Error fetching wallet balance', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Update wallet balance from external source
     */
    public function updateWalletBalance(Wallet $wallet): void
    {
        if (!$wallet->wallet_address) {
            return;
        }
        
        $balance = $this->getWalletBalance($wallet->wallet_address);
        $wallet->updateBalance($balance);
    }

    /**
     * Validate Ethereum address format
     */
    public function isValidEthereumAddress(string $address): bool
    {
        return preg_match('/^0x[a-fA-F0-9]{40}$/', $address) === 1;
    }

    /**
     * Get wallet connection status
     */
    public function getWalletStatus(Wallet $wallet): array
    {
        $status = [
            'is_connected' => $wallet->isConnected(),
            'wallet_type' => $wallet->wallet_type,
            'balance' => $wallet->balance,
            'fiat_balance' => $wallet->fiat_balance,
            'address' => $wallet->wallet_address,
        ];
        
        // Update balance from blockchain if connected
        if ($wallet->isConnected()) {
            $this->updateWalletBalance($wallet);
            $status['balance'] = $wallet->fresh()->balance;
        }
        
        return $status;
    }

    /**
     * Disconnect wallet
     */
    public function disconnectWallet(Wallet $wallet): void
    {
        $wallet->update([
            'wallet_address' => null,
            'wallet_type' => 'external',
            'is_active' => false,
        ]);
    }

    /**
     * Get crypto exchange rates
     */
    public function getExchangeRates(): array
    {
        try {
            $response = Http::get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => 'bitcoin,ethereum,tether',
                'vs_currencies' => 'usd,ngn'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'BTC' => [
                        'USD' => $data['bitcoin']['usd'] ?? 0,
                        'NGN' => $data['bitcoin']['ngn'] ?? 0,
                    ],
                    'ETH' => [
                        'USD' => $data['ethereum']['usd'] ?? 0,
                        'NGN' => $data['ethereum']['ngn'] ?? 0,
                    ],
                    'USDT' => [
                        'USD' => $data['tether']['usd'] ?? 0,
                        'NGN' => $data['tether']['ngn'] ?? 0,
                    ],
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch exchange rates', ['error' => $e->getMessage()]);
        }
        
        // Fallback rates
        return [
            'BTC' => ['USD' => 62000, 'NGN' => 62000000],
            'ETH' => ['USD' => 4200, 'NGN' => 4200000],
            'USDT' => ['USD' => 1, 'NGN' => 1500],
        ];
    }
} 