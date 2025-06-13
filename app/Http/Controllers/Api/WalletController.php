<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get wallet information and status.
     */
    public function info(): JsonResponse
    {
        $user = Auth::user();
        $wallet = $this->walletService->getUserWallet($user);
        $status = $this->walletService->getWalletStatus($wallet);

        return response()->json([
            'success' => true,
            'data' => $status,
            'message' => 'Wallet information retrieved successfully'
        ]);
    }

    /**
     * Connect MetaMask wallet.
     */
    public function connect(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'wallet_address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $wallet = $this->walletService->connectMetaMask($user, $request->wallet_address);
            
            return response()->json([
                'success' => true,
                'data' => $this->walletService->getWalletStatus($wallet),
                'message' => 'Wallet connected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Disconnect wallet.
     */
    public function disconnect(): JsonResponse
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'No wallet found'
            ], 404);
        }

        $this->walletService->disconnectWallet($wallet);

        return response()->json([
            'success' => true,
            'message' => 'Wallet disconnected successfully'
        ]);
    }

    /**
     * Get wallet balance for a specific address.
     */
    public function balance(string $address): JsonResponse
    {
        if (!$this->walletService->isValidEthereumAddress($address)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Ethereum address format'
            ], 400);
        }

        $balance = $this->walletService->getWalletBalance($address);

        return response()->json([
            'success' => true,
            'data' => [
                'address' => $address,
                'balance' => $balance
            ],
            'message' => 'Balance retrieved successfully'
        ]);
    }

    /**
     * Get exchange rates.
     */
    public function exchangeRates(): JsonResponse
    {
        $rates = $this->walletService->getExchangeRates();

        return response()->json([
            'success' => true,
            'data' => $rates,
            'message' => 'Exchange rates retrieved successfully'
        ]);
    }

    /**
     * Get transaction history.
     */
    public function transactions(): JsonResponse
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'No wallet found'
            ], 404);
        }

        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'message' => 'Transaction history retrieved successfully'
        ]);
    }

    /**
     * Log a receive transaction.
     */
    public function logReceive(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'hash' => 'required|string',
            'currency' => 'required|in:BTC,ETH,USDT',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'No wallet found'
            ], 404);
        }

        // Create transaction record
        $transaction = $wallet->transactions()->create([
            'type' => 'receive',
            'counterparty' => 'external',
            'amount' => $request->amount,
            'hash' => $request->hash,
            'currency' => $request->currency,
        ]);

        // Update wallet balance
        $this->walletService->updateWalletBalance($wallet);

        return response()->json([
            'success' => true,
            'data' => $transaction,
            'message' => 'Receive transaction logged successfully'
        ]);
    }
}
