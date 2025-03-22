<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Loan;

class LoanController extends Controller
{
    /**
     * Show loan application form.
     */
    public function create()
    {
        return view('loans.apply');
    }

    /**
     * Store a new loan application.
     */
    public function store(Request $request)
    {
        //  Validate the request with stricter rules
        $request->validate([
            'amount' => 'required|numeric|min:5000|max:1000000',
            'duration' => 'required|integer|min:1|max:36',
            'loan_type' => 'required|in:fiat,crypto',
            'crypto_currency' => 'nullable|required_if:loan_type,crypto|in:BTC,ETH,USDT',
        ]);

        //  Prevent user from applying if they already have a pending loan
        if (Loan::where('user_id', Auth::id())->where('status', 'pending')->exists()) {
            return redirect()->back()->with('error', 'You already have a pending loan application.');
        }

        // ✅ Fetch exchange rate if crypto loan
        $exchangeRate = null;
        if ($request->loan_type === 'crypto') {
            $exchangeRate = $this->fetchCryptoExchangeRate($request->crypto_currency);
            if (!$exchangeRate) {
                return redirect()->back()->with('error', 'Invalid cryptocurrency selection.');
            }
        }

        // ✅ Create the loan
        Loan::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'duration' => $request->duration,
            'interest_rate' => 10.00,
            'status' => 'pending',
            'due_date' => now()->addMonths($request->duration),
            'loan_type' => $request->loan_type,
            'crypto_currency' => $request->crypto_currency,
            'exchange_rate' => $exchangeRate,
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan request submitted successfully!');
    }

    /**
     * Fetch crypto exchange rate (Mock API - Replace with real API)
     */
    private function fetchCryptoExchangeRate($crypto)
    {
        $rates = [
            'BTC' => 62000000,
            'ETH' => 4200000,
            'USDT' => 1500,
        ];

        return $rates[$crypto] ?? null;
    }
}
