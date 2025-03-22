<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Loan;
use App\Models\Repayment;
use Carbon\Carbon;

class RepaymentController extends Controller
{
    /**
     * Show user's loan repayment page.
     */
    public function index()
    {
        $user = Auth::user();

        // ✅ Fetch user's approved loans with remaining balance
        $loans = Loan::where('user_id', $user->id)
            ->where('status', 'approved')
            ->withSum('repayments', 'amount_paid')
            ->get()
            ->map(function ($loan) {
                $loan->remaining_balance = max($loan->amount - $loan->repayments_sum_amount_paid, 0);
                return $loan;
            });

        return view('repayments.index', compact('loans'));
    }

    /**
     * Store a new repayment.
     */
    public function store(Request $request, Loan $loan)
    {
        // ✅ Validate request
        $request->validate([
            'amount_paid' => 'required|numeric|min:1',
            'payment_method' => 'required|string|max:50',
            'repayment_currency' => 'required|in:fiat,crypto',
            'crypto_currency' => 'nullable|required_if:repayment_currency,crypto|in:BTC,ETH,USDT',
        ]);

        // ✅ Ensure user owns the loan
        if ($loan->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // ✅ Convert Crypto Payment to Naira Equivalent
        $exchangeRate = null;
        if ($request->repayment_currency === 'crypto') {
            $exchangeRate = $this->fetchCryptoExchangeRate($request->crypto_currency);
            $request->amount_paid *= $exchangeRate;
        }

        // ✅ Ensure payment does not exceed remaining balance
        $totalPaid = Repayment::where('loan_id', $loan->id)->sum('amount_paid');
        $remainingBalance = $loan->amount - $totalPaid;

        if ($request->amount_paid > $remainingBalance) {
            return redirect()->back()->with('error', 'Payment exceeds remaining balance.');
        }

        // ✅ Calculate Late Fee (5% if overdue)
        $dueDate = Carbon::parse($loan->created_at)->addMonths($loan->duration);
        $today = Carbon::now();
        $lateFee = ($today->gt($dueDate)) ? 0.05 * $request->amount_paid : 0;

        // ✅ Store repayment
        Repayment::create([
            'loan_id' => $loan->id,
            'user_id' => Auth::id(),
            'amount_paid' => $request->amount_paid,
            'late_fee' => $lateFee,
            'payment_date' => now(),
            'payment_method' => $request->payment_method,
            'repayment_currency' => $request->repayment_currency,
            'crypto_currency' => $request->crypto_currency,
            'exchange_rate' => $exchangeRate,
            'status' => ($today->gt($dueDate)) ? 'overdue' : 'paid',
        ]);

        return redirect()->route('repayments.index')->with('success', 'Payment recorded successfully!');
    }
}
