<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Notifications\LoanStatusNotification;

class AdminLoanController extends Controller
{
    /**
     * Show all pending loans for admin approval.
     */
    public function index()
    {
        // ✅ Fetch pending loans with pagination
        $loans = Loan::where('status', 'pending')->paginate(10);
        return view('admin.loans', compact('loans'));
    }

    /**
     * Approve or Reject a Loan.
     */
    public function update(Request $request, Loan $loan)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        if ($loan->status !== 'pending') {
            return redirect()->back()->with('error', 'Loan has already been processed.');
        }

        try {
            DB::beginTransaction();

            // ✅ Apply real-time exchange rate if the loan is in crypto
            if ($loan->loan_type === 'crypto') {
                $exchangeRate = $this->fetchCryptoExchangeRate($loan->crypto_currency);
                $loan->update(['exchange_rate' => $exchangeRate]);
            }

            $loan->update(['status' => $request->status]);

            // Send notification
            $loan->user->notify(new LoanStatusNotification($loan, $request->status));

            DB::commit();
            return redirect()->back()->with('success', 'Loan status updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Loan Approval Error', ['message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Fetch crypto exchange rate (Mock API Integration - Replace with real API)
     */
    private function fetchCryptoExchangeRate($crypto)
    {
        $rates = [
            'BTC' => 62000000, // Example: 1 BTC = 62M NGN
            'ETH' => 4200000, // Example: 1 ETH = 4.2M NGN
            'USDT' => 1500, // Example: 1 USDT = 1500 NGN
        ];

        return $rates[$crypto] ?? null;
    }
}
