<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Illuminate\Support\Facades\Auth;

class CryptoController extends Controller
{
    protected $web3;

    const WALLET_NOT_FOUND = 'Wallet not found';

    public function __construct()
    {
        // Connect to Ethereum Mainnet via Infura
        $this->web3 = new Web3(
            new HttpProvider(
                new HttpRequestManager('https://gas.api.infura.io/v3/9ab0bb56187947f9a0212b58eaedcc65')
            )
        );
    }

    // Get ETH balance of a given address
    public function getBalance($address)
    {
        $eth = $this->web3->eth;
        $result = null;

        $eth->getBalance($address, function ($err, $balance) use (&$result) {
            if ($err !== null) {
                $result = response()->json(['error' => $err->getMessage()], 500);
                return;
            }
            $balanceInEth = bcdiv($balance->toString(), bcpow('10', '18', 18), 18);
            $result = response()->json(['balance' => $balanceInEth]);
        });

        usleep(500000); // wait for callback
        return $result ?? response()->json(['error' => 'Failed to fetch balance'], 500);
    }

    // Show wallet info (ETH balance + fiat balance)
    public function walletInfo()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['error' => self::WALLET_NOT_FOUND], 404);
        }

        $eth = $this->web3->eth;
        $result = null;

        $eth->getBalance($wallet->wallet_address, function ($err, $balance) use (&$result, $wallet) {
            if ($err !== null) {
                $result = response()->json(['error' => $err->getMessage()], 500);
                return;
            }
            $balanceInEth = bcdiv($balance->toString(), bcpow('10', '18', 18), 18);
            $fiatBalance = $wallet->fiat_balance ?? 0;
            $result = view('wallet.info', compact('balanceInEth', 'fiatBalance'));
        });

        usleep(500000);
        return $result ?? response()->json(['error' => 'Failed to fetch wallet info'], 500);
    }

    // Send ETH transaction and log it
    public function sendCrypto(Request $request)
    {
        $request->validate([
            'to_address' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet || empty($wallet->private_key)) {
            return response()->json(['error' => self::WALLET_NOT_FOUND], 400);
        }

        $fromAddress = $wallet->wallet_address;
        $privateKey = decrypt($wallet->private_key);

        $eth = $this->web3->eth;
        $utils = $this->web3->utils;

        $result = null;

        $eth->personal->sendTransaction([
            'from' => $fromAddress,
            'to' => $request->to_address,
            'value' => $utils->toWei((string)$request->amount, 'ether'),
        ], $privateKey, function ($err, $transactionHash) use (&$result, $wallet, $request) {
            if ($err !== null) {
                $result = response()->json(['error' => $err->getMessage()], 500);
                return;
            }

            // Log the send transaction
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'send',
                'counterparty' => $request->to_address,
                'amount' => $request->amount,
                'hash' => $transactionHash,
            ]);

            $result = response()->json(['transaction_hash' => $transactionHash]);
        });

        usleep(500000);
        return $result ?? response()->json(['error' => 'Transaction failed'], 500);
    }

    // Return user's receiving wallet address
    public function receiveCrypto()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['error' => self::WALLET_NOT_FOUND], 404);
        }

        return response()->json(['address' => $wallet->wallet_address]);
    }

    // Log a receive transaction manually
    public function logReceiveTransaction(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'hash' => 'required|string',
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
        ]);

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
}
// This controller handles all wallet and crypto-related operations, including balance checks, sending/receiving transactions, and transaction history.
