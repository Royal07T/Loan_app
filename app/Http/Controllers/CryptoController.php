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

        $balanceInEth = null;

        $eth->getBalance($address, function ($err, $balance) use (&$balanceInEth) {
            if ($err !== null) {
                $balanceInEth = 'error';
            } else {
                $balanceInEth = $balance->toString(); // Wei
            }
        });

        // Give Web3 some milliseconds to complete (Laravel doesn't wait on async JS-style behavior)
        usleep(500000); // 0.5 sec

        if ($balanceInEth === 'error') {
            return response()->json(['error' => 'Failed to fetch balance.'], 500);
        }

        // Convert Wei to ETH
        $ethValue = bcdiv($balanceInEth, bcpow('10', '18', 18), 18); // ETH = wei / 10^18

        return response()->json([
            'wallet_address' => $address,
            'balance_eth' => $ethValue . ' ETH',
        ]);
    }
        'loan_id' => $loan->id,
            'user_id' => Auth::id(),
            'amount_paid' => $convertedAmount,
            'payment_method' => $request->payment_method,
            'crypto_currency' => $request->crypto_currency,
            'late_fee' => $lateFee,
        ]);

            // Update loan status if fully paid
            if ($remainingBalance <= $request->amount_paid) {
                $loan->update(['status' => 'paid']);
            }

            DB::commit();

            // Notify user
            Auth::user()->notify(new RepaymentNotification($repayment));

            return redirect()->route('loans.index')->with('success', 'Repayment successful!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Repayment error: ' . $e->getMessage());
            return back()->with('error', 'Failed to process repayment.');
        }
