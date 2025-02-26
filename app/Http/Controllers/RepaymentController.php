<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Loan;
use App\Models\Repayment;
use Carbon\Carbon; // ✅ Import Carbon for date handling

class RepaymentController extends Controller
{
    /**
     * Show user's loan repayment page.
     */
    public function index()
    {
        // ✅ Ensure a user is logged in
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'You must be logged in.');
        }

        // ✅ Get approved loans for the logged-in user & calculate remaining balance
        $loans = Loan::where('user_id', $user->id)
            ->where('status', 'approved')
            ->get()
            ->map(function ($loan) {
                $totalPaid = Repayment::where('loan_id', $loan->id)->sum('amount_paid');
                $loan->remaining_balance = max($loan->amount - $totalPaid, 0);
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
        ]);

        // ✅ Ensure user owns the loan
        if ($loan->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // ✅ Get total amount already paid
        $totalPaid = Repayment::where('loan_id', $loan->id)->sum('amount_paid');
        $remainingBalance = $loan->amount - $totalPaid;

        // ✅ Ensure payment does not exceed remaining balance
        if ($request->amount_paid > $remainingBalance) {
            return redirect()->back()->with('error', 'Payment exceeds remaining balance.');
        }

        // ✅ Use database transaction for safety
        try {
            DB::beginTransaction();

            // ✅ Calculate Late Fee (5% if overdue)
            $dueDate = $loan->created_at->addMonths($loan->duration);
            $today = Carbon::now();
            $lateFee = 0;

            if ($today->gt($dueDate)) {
                $lateFee = 0.05 * $request->amount_paid; // 5% of the amount paid
            }

            // ✅ Store repayment
            Repayment::create([
                'loan_id' => $loan->id,
                'user_id' => Auth::id(),
                'amount_paid' => $request->amount_paid + $lateFee,
                'payment_date' => now(),
                'payment_method' => $request->payment_method,
                'status' => ($today->gt($dueDate)) ? 'overdue' : 'paid',
            ]);

            // ✅ Update loan status if fully paid
            if (($totalPaid + $request->amount_paid) >= $loan->amount) {
                $loan->update(['status' => 'paid']);
            }

            DB::commit();
            return redirect()->route('repayments.index')->with('success', 'Payment recorded successfully! Late Fee: ₦' . number_format($lateFee, 2));
        } catch (\Exception $e) {
            DB::rollBack();

            // ✅ Log the error for debugging
            Log::error('Repayment Error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}
