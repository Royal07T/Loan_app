<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Web3;

class CryptoController extends Controller
{
    protected $web3;
    protected $contract;

    public function __construct()
    {
        // Connecting to the Ethereum network via Infura (Mainnet example)
        $this->web3 = new Web3(new HttpProvider(new HttpRequestManager('https://mainnet.infura.io/v3/YOUR_INFURA_PROJECT_ID')));
    }

    // Get balance for a specific wallet (ETH balance)
    public function getBalance($address)
    {
        $eth = $this->web3->eth;

        $eth->getBalance($address, function ($err, $balance) {
            if ($err !== null) {
                return response()->json(['error' => $err->getMessage()], 500);
            }
            // Convert the balance from Wei to ETH
            $balanceInEth = $balance->toString() / 1000000000000000000;
            return response()->json(['balance' => $balanceInEth]);
        });
    }
    // Show wallet information (fiat + crypto balance)
    public function walletInfo()
    {
        $user = auth()->user();
        $wallet = $user->wallet; // Assuming a one-to-one relationship with wallet

        // Fetch Ethereum balance from blockchain
        $eth = $this->web3->eth;
        $eth->getBalance($wallet->crypto_address, function ($err, $balance) use ($wallet) {
            if ($err !== null) {
                return response()->json(['error' => $err->getMessage()], 500);
            }
            // Convert the balance from Wei to ETH
            $balanceInEth = $balance->toString() / 1000000000000000000;

            // Here, you can fetch Fiat balance from the database or another API
            // Assume wallet->balance is fiat balance
            $fiatBalance = $wallet->balance;

            return view('wallet.info', compact('balanceInEth', 'fiatBalance'));
        });
    }

    // Send crypto (ETH)
    public function sendCrypto(Request $request)
    {
        $request->validate([
            'to_address' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = auth()->user();
        $wallet = $user->wallet;
        $fromAddress = $wallet->crypto_address;
        $privateKey = $wallet->private_key; // Securely manage the private key (maybe .env or encryption)

        $eth = $this->web3->eth;

        // Sending ETH transaction
        $eth->personal->sendTransaction([
            'from' => $fromAddress,
            'to' => $request->to_address,
            'value' => $this->web3->utils->toWei($request->amount, 'ether')
        ], $privateKey, function ($err, $transactionHash) {
            if ($err !== null) {
                return response()->json(['error' => $err->getMessage()], 500);
            }
            return response()->json(['transaction_hash' => $transactionHash]);
        });
    }

    // Generate a new address for receiving crypto (ETH)
    public function receiveCrypto()
    {
        $user = auth()->user();
        $wallet = $user->wallet;
        // For receiving ETH, we just return the user's wallet address
        return response()->json(['address' => $wallet->crypto_address]);
    }

    // View transaction history (this will depend on how you log transactions)
    public function transactionHistory()
    {
        $user = auth()->user();
        // Assuming you have a transactions table or logs for this
        $transactions = $user->wallet->transactions; // Eloquent relationship example
        return view('wallet.transactions', compact('transactions'));
    }
}
