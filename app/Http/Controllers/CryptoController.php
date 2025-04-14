<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Web3\Web3;
use Web3\Utils;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

class CryptoController extends Controller
{
    protected $web3;

    public function __construct()
    {
        $this->web3 = new Web3(new HttpProvider(
            new HttpRequestManager('https://mainnet.infura.io/v3/YOUR_INFURA_PROJECT_ID', 5)
        ));
    }

    public function syncWalletBalance($userId)
    {
        $user = User::findOrFail($userId);
        $wallet = $user->wallet;

        if (!$wallet || !$wallet->wallet_address) {
            return response()->json(['error' => 'Wallet not found or address not set'], 404);
        }

        $eth = $this->web3->eth;

        $eth->getBalance($wallet->wallet_address, function ($err, $balance) use ($wallet) {
            if ($err !== null) {
                return response()->json(['error' => $err->getMessage()], 500);
            }

            // Convert from Wei to ETH
            $ethBalance = Utils::fromWei($balance, 'ether');

            // Assume 1 ETH = 3,000 USD (you can dynamically fetch this from CoinGecko or similar)
            $usdRate = 3000;
            $fiatBalance = $ethBalance * $usdRate;

            // Update wallet
            $wallet->crypto_balance = $ethBalance;
            $wallet->fiat_balance = $fiatBalance;
            $wallet->save();
        });

        return response()->json(['message' => 'Wallet synced successfully']);
    }

    public function getWalletOverview($userId)
    {
        $user = User::with('wallet')->findOrFail($userId);

        if (!$user->wallet) {
            return response()->json(['error' => 'No wallet linked'], 404);
        }

        return response()->json([
            'wallet_address' => $user->wallet->wallet_address,
            'crypto_balance_eth' => $user->wallet->crypto_balance,
            'fiat_balance_usd' => $user->wallet->fiat_balance,
        ]);
    }
}
