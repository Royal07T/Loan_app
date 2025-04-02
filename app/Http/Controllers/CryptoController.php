<?php

namespace App\Http\Controllers;

use Web3\Web3;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

class CryptoController extends Controller
{
    protected $web3;
    protected $contract;

    public function __construct()
    {
        $this->web3 = new Web3(new HttpProvider(new HttpRequestManager('https://mainnet.infura.io/v3/YOUR_INFURA_PROJECT_ID')));

        // Example: Load a smart contract (Replace with your contract address & ABI)
        $this->contract = new Contract($this->web3->provider, 'YOUR_SMART_CONTRACT_ABI');
    }

    public function getBalance($address)
    {
        $eth = $this->web3->eth;
        $eth->getBalance($address, function ($err, $balance) {
            if ($err !== null) {
                return response()->json(['error' => $err->getMessage()], 500);
            }
            return response()->json(['balance' => $balance->toString()]);
        });
    }
}
