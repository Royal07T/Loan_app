<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CryptoController extends Controller
{
    protected $walletService;

    const WALLET_NOT_FOUND = 'Wallet not found';

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    // Get wallet info (ETH balance + fiat balance)
    public function walletInfo()
    {
        $user = Auth::user();
        $wallet = $this->walletService->getUserWallet($user);

        if (!$wallet) {
            return response()->json(['error' => self::WALLET_NOT_FOUND], 404);
        }

        $walletStatus = $this->walletService->getWalletStatus($wallet);
        
        // Extract the values the view expects
        $balanceInEth = $walletStatus['balance'] ?? 0;
        $fiatBalance = $walletStatus['fiat_balance'] ?? 0;

        return view('wallet.info', compact('balanceInEth', 'fiatBalance', 'walletStatus'));
    }

    // Get ETH balance of a given address
    public function getBalance($address)
    {
        if (!$this->walletService->isValidEthereumAddress($address)) {
            return response()->json(['error' => 'Invalid Ethereum address format'], 400);
        }

        $balance = $this->walletService->getWalletBalance($address);
        
        return response()->json(['balance' => $balance]);
    }

    // Connect MetaMask wallet
    public function connectMetaMask(Request $request)
    {
        $request->validate([
            'wallet_address' => 'required|string',
        ]);

        try {
            $user = Auth::user();
            $wallet = $this->walletService->connectMetaMask($user, $request->wallet_address);
            
            return response()->json([
                'success' => true,
                'message' => 'Wallet connected successfully',
                'wallet' => $this->walletService->getWalletStatus($wallet)
            ]);
        } catch (\Exception $e) {
            Log::error('MetaMask connection failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Disconnect wallet
    public function disconnectWallet()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['error' => self::WALLET_NOT_FOUND], 404);
        }

        $this->walletService->disconnectWallet($wallet);

        return response()->json([
            'success' => true,
            'message' => 'Wallet disconnected successfully'
        ]);
    }

    // Return user's receiving wallet address
    public function receiveCrypto()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet || !$wallet->isConnected()) {
            return response()->json(['error' => 'No connected wallet found'], 404);
        }

        return response()->json(['address' => $wallet->wallet_address]);
    }

    // Log a receive transaction manually
    public function logReceiveTransaction(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'hash' => 'required|string',
            'currency' => 'required|in:BTC,ETH,USDT',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['error' => self::WALLET_NOT_FOUND], 404);
        }

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'receive',
            'counterparty' => 'external',
            'amount' => $request->amount,
            'hash' => $request->hash,
            'currency' => $request->currency,
        ]);

        // Update wallet balance
        $this->walletService->updateWalletBalance($wallet);

        return response()->json(['message' => 'Receive transaction logged successfully']);
    }

    // Show transaction history
    public function transactionHistory()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['error' => self::WALLET_NOT_FOUND], 404);
        }

        $transactions = $wallet->transactions()->orderBy('created_at', 'desc')->get();

        return view('wallet.transactions', compact('transactions'));
    }

    // Get exchange rates
    public function getExchangeRates()
    {
        $rates = $this->walletService->getExchangeRates();
        
        return response()->json($rates);
    }

    // Get wallet status
    public function getWalletStatus()
    {
        $user = Auth::user();
        $wallet = $this->walletService->getUserWallet($user);
        
        $status = $this->walletService->getWalletStatus($wallet);
        
        return response()->json($status);
    }
}
// This controller handles all wallet and crypto-related operations, including balance checks, sending/receiving transactions, and transaction history.
