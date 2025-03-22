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

        $lateFee = 0;
        if (Carbon::now()->greaterThan($loan->due_date)) {
            $lateFee = 0.05 * $request->amount_paid;
        }

        if ($request->payment_method === 'crypto') {
            $exchangeRate = $this->fetchCryptoExchangeRate($request->crypto_currency);
            $request->amount_paid *= $exchangeRate;
        }

        Repayment::create([
            'loan_id' => $loan->id,
            'user_id' => Auth::id(),
            'amount_paid' => $request->amount_paid,
            'late_fee' => $lateFee,
            'payment_date' => now(),
            'payment_method' => $request->payment_method,
            'status' => 'paid',
        ]);

        if (($totalPaid + $request->amount_paid + $lateFee) >= $loan->amount) {
            $loan->update(['status' => 'paid']);
        }

        DB::commit();
        return redirect()->route('repayments.index')->with('success', 'Payment successful!');
    }

    private function fetchCryptoExchangeRate($crypto)
    {
        $response = Http::get("https://api.coingecko.com/api/v3/simple/price", [
            'ids' => strtolower($crypto),
            'vs_currencies' => 'ngn'
        ]);
        return $response->json()[$crypto]['ngn'] ?? null;
    }
}
