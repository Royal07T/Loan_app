<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Repayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Notifications\RepaymentNotification;
use Illuminate\Validation\Rule;

class RepaymentController extends Controller
{
    public function store(Request $request, Loan $loan)
    {
        // Validate input
        $request->validate([
            'amount_paid' => 'required|numeric|min:1',
            'payment_method' => ['required', 'string', 'max:50', Rule::in(['bank', 'crypto'])],
            'crypto_currency' => ['nullable', Rule::requiredIf($request->payment_method === 'crypto'), Rule::in(['BTC', 'ETH', 'USDT'])],
        ]);

        // Ensure user owns the loan
        abort_unless($loan->user_id === Auth::id(), 403, 'Unauthorized action.');

        // Get remaining balance in a single query
        $loan->loadSum('repayments', 'amount_paid');
        $remainingBalance = $loan->amount - $loan->repayments_sum_amount_paid;

        if ($request->amount_paid > $remainingBalance) {
            return back()->with('error', 'Payment exceeds remaining balance.');
        }

        DB::beginTransaction();
        try {
            $lateFee = 0;
            $today = now();
            $dueDate = Carbon::parse($loan->due_date);

            if ($today->greaterThan($dueDate)) {
                $lateFee = 0.05 * $request->amount_paid; // 5% late fee for late payments
            }

            // Convert Crypto Payment to Naira Equivalent
            $convertedAmount = $request->amount_paid;
            if ($request->payment_method === 'crypto') {
                $exchangeRate = $this->fetchCryptoExchangeRate($request->crypto_currency);
                if (!$exchangeRate) {
                    return back()->with('error', 'Failed to fetch exchange rate.');
                }
                $convertedAmount *= $exchangeRate;
            }

            // Store the repayment
            $repayment = Repayment::create([
                'loan_id' => $loan->id,
                'user_id' => Auth::id(),
                'amount_paid' => $convertedAmount,
                'late_fee' => $lateFee,
                'payment_date' => now(),
                'payment_method' => $request->payment_method,
                'status' => 'paid',
            ]);

            // Notify user
            $loan->user->notify(new RepaymentNotification($repayment));

            // Update loan status if fully paid
            if (($loan->repayments_sum_amount_paid + $convertedAmount + $lateFee) >= $loan->amount) {
                $loan->update(['status' => 'paid']);
            }

            DB::commit();
            return redirect()->route('repayments.index')->with('success', 'Payment successful!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Repayment Error', [
                'loan_id' => $loan->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Fetches crypto exchange rate from CoinGecko (cached for 10 minutes).
     */
    private function fetchCryptoExchangeRate($crypto)
    {
        return Cache::remember("crypto_rate_{$crypto}", now()->addMinutes(10), function () use ($crypto) {
            try {
                $response = Http::get("https://api.coingecko.com/api/v3/simple/price", [
                    'ids' => strtolower($crypto),
                    'vs_currencies' => 'ngn'
                ]);

                $data = $response->json();
                return $data[strtolower($crypto)]['ngn'] ?? null;
            } catch (\Throwable $e) {
                Log::error('Crypto API Error: ' . $e->getMessage());
                return null;
            }
        });
    }
}
