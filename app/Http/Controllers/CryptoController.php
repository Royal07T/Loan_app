<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Illuminate\Support\Facades\Auth;

class CryptoController extends Controller
{
    protected $web3;

    public function __construct()
    {
        // Connect to Ethereum Mainnet via Infura (replace with your project ID)
        $this->web3 = new Web3(new HttpProvider(new HttpRequestManager('https://mainnet.infura.io/v3/YOUR_INFURA_PROJECT_ID')));
    }

    // Get balance for a specific wallet (ETH)
    public function getBalance($address)
    {
        $eth = $this->web3->eth;
        $result = null;
        $eth->getBalance($address, function ($err, $balance) use (&$result) {
            if ($err !== null) {
                $result = response()->json(['error' => $err->getMessage()], 500);
                return;
            }
            $balanceInEth = $balance->toString() / 1e18;
            $result = response()->json(['balance' => $balanceInEth]);
        });

        // Wait briefly to simulate a blocking call
        usleep(500000); // half a second

        return $result;
    }

    // Show wallet information (fiat + crypto balance)
    public function walletInfo()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        $eth = $this->web3->eth;
        $result = null;

        $eth->getBalance($wallet->crypto_address, function ($err, $balance) use (&$result, $wallet) {
            if ($err !== null) {
                $result = response()->json(['error' => $err->getMessage()], 500);
                return;
            }
            $balanceInEth = $balance->toString() / 1e18;
            $fiatBalance = $wallet->balance;
            $result = view('wallet.info', compact('balanceInEth', 'fiatBalance'));
        });

        usleep(500000); // simulate wait
        return $result;
    }

    // Send crypto (ETH)
    public function sendCrypto(Request $request)
    {
        $request->validate([
            'to_address' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;
        $fromAddress = $wallet->crypto_address;
        $privateKey = $wallet->private_key; // 🛑 Make sure this is encrypted and secure

        $eth = $this->web3->eth;
        $utils = $this->web3->utils;

        $result = null;
        $eth->personal->sendTransaction([
            'from' => $fromAddress,
            'to' => $request->to_address,
            'value' => $utils->toWei($request->amount, 'ether')
        ], $privateKey, function ($err, $transactionHash) use (&$result) {
            if ($err !== null) {
                $result = response()->json(['error' => $err->getMessage()], 500);
                return;
            }
            $result = response()->json(['transaction_hash' => $transactionHash]);
        });

        usleep(500000);
        return $result;
    }

    // Return user's receiving crypto address
    public function receiveCrypto()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        return response()->json(['address' => $wallet->crypto_address]);
    }

    // Transaction history (dummy example; ensure you store transaction logs)
    public function transactionHistory()
    {
        $user = Auth::user();
        $transactions = $user->wallet->transactions ?? [];

        return view('wallet.transactions', compact('transactions'));
    }
}
