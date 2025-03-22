<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Loan;
use App\Models\Repayment;
use Carbon\Carbon;

class RepaymentController extends Controller
{
    public function store(Request $request, Loan $loan)
    {
        // ✅ Validate input
        $request->validate([
            'amount_paid' => 'required|numeric|min:1',
            'payment_method' => 'required|string|max:50',
            'crypto_currency' => 'nullable|required_if:payment_method,crypto|in:BTC,ETH,USDT',
        ]);

        if ($loan->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $totalPaid = Repayment::where('loan_id', $loan->id)->sum('amount_paid');
        $remainingBalance = $loan->amount - $totalPaid;

        if ($request->amount_paid > $remainingBalance) {
            return redirect()->back()->with('error', 'Payment exceeds remaining balance.');
        }

        DB::beginTransaction();
        try {
            $lateFee = 0;
            $today = Carbon::now();
            $dueDate = Carbon::parse($loan->due_date);

            if ($today->greaterThan($dueDate)) {
                $lateFee = 0.05 * $request->amount_paid; // 5% late fee
            }

            // ✅ Convert Crypto Payment to Naira Equivalent
            $convertedAmount = $request->amount_paid; // Default to fiat amount
            if ($request->payment_method === 'crypto') {
                $exchangeRate = $this->fetchCryptoExchangeRate($request->crypto_currency);
                if (!$exchangeRate) {
                    return redirect()->back()->with('error', 'Failed to fetch exchange rate.');
                }
                $convertedAmount = $request->amount_paid * $exchangeRate;
            }

            // ✅ Store the repayment
            Repayment::create([
                'loan_id' => $loan->id,
                'user_id' => Auth::id(),
                'amount_paid' => $convertedAmount, // Use converted amount
                'late_fee' => $lateFee,
                'payment_date' => now(),
                'payment_method' => $request->payment_method,
                'status' => 'paid',
            ]);

            // ✅ Check if the loan is fully paid
            if (($totalPaid + $convertedAmount + $lateFee) >= $loan->amount) {
                $loan->update(['status' => 'paid']);
            }

            DB::commit();
            return redirect()->route('repayments.index')->with('success', 'Payment successful!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Repayment Error', [
                'loan_id' => $loan->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Fetches crypto exchange rate from CoinGecko.
     */
    private function fetchCryptoExchangeRate($crypto)
    {
        try {
            $response = Http::get("https://api.coingecko.com/api/v3/simple/price", [
                'ids' => strtolower($crypto),
                'vs_currencies' => 'ngn'
            ]);

            $data = $response->json();
            return $data[strtolower($crypto)]['ngn'] ?? null;
        } catch (\Exception $e) {
            Log::error('Crypto API Error: ' . $e->getMessage());
            return null;
        }
    }
}
