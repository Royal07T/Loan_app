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
        $validated = $request->validate([
            'amount' => 'required|numeric|min:5000|max:1000000',
            'duration' => 'required|integer|min:1|max:36',
            'loan_type' => 'required|in:fiat,crypto',
            'crypto_currency' => 'nullable|required_if:loan_type,crypto|in:BTC,ETH,USDT',
        ]);

        // Prevent duplicate loan applications
        if (Loan::where(['user_id' => Auth::id(), 'status' => 'pending'])->exists()) {
            return back()->with('error', 'You already have a pending loan application.');
        }

        // Fetch exchange rate if loan type is crypto
        $exchangeRate = $validated['loan_type'] === 'crypto' ? $this->fetchCryptoExchangeRate($validated['crypto_currency']) : null;
        if ($validated['loan_type'] === 'crypto' && !$exchangeRate) {
            return back()->with('error', 'Invalid cryptocurrency selection.');
        }

        // Create loan
        Loan::create([
            'user_id' => Auth::id(),
            'amount' => $validated['amount'],
            'duration' => $validated['duration'],
            'interest_rate' => 10.00,
            'status' => 'pending',
            'due_date' => now()->addMonths($validated['duration']),
            'loan_type' => $validated['loan_type'],
            'crypto_currency' => $validated['crypto_currency'] ?? null,
            'exchange_rate' => $exchangeRate,
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan request submitted successfully!');
    }

    /**
     * Fetch crypto exchange rate (Mock API - Replace with real API)
     */
    private function fetchCryptoExchangeRate($crypto)
    {
        return [
            'BTC' => 62000000,
            'ETH' => 4200000,
            'USDT' => 1500,
        ][$crypto] ?? null;
    }
}
