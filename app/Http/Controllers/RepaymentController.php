<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Loan;
use App\Models\Repayment;

class RepaymentController extends Controller
{
    /**
     * Show user's loan repayment page.
     */
    public function index()
    {

        $loans = Auth::user()->loans->where('status', 'approved');
        return view('repayments.index', compact('loans'));
    }

    /**
     * Store a new repayment.
     */
    public function store(Request $request, Loan $loan)
    {
        $request->validate([
            'amount_paid' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
        ]);

        Repayment::create([
            'loan_id' => $loan->id,
            'user_id' => Auth::id(),
            'amount_paid' => $request->amount_paid,
            'payment_date' => now(),
            'status' => 'paid',
            'payment_method' => $request->payment_method,
        ]);

        return redirect()->route('repayments.index')->with('success', 'Payment recorded successfully!');
    }
}
