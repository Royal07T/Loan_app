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
            'amount' => 'required|numeric|min:5000|max:1000000', // Min ₦5000, Max ₦1,000,000
            'duration' => 'required|integer|min:1|max:36', // Min 1 month, Max 3 years
        ]);

        //  Prevent user from applying if they already have a pending loan
        if (Loan::where('user_id', Auth::id())->where('status', 'pending')->exists()) {
            return redirect()->back()->with('error', 'You already have a pending loan application.');
        }

        //  Create the loan
        Loan::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'duration' => $request->duration,
            'interest_rate' => 10.00, // Default 10% interest rate
            'status' => 'pending',
            'due_date' => now()->addMonths($request->duration), // Auto-set due date
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan request submitted successfully!');
    }

    /**
     * Show all loan applications for the logged-in user.
     */
    public function index()
    {
        //  Only authenticated users can view their loans
        $loans = Auth::user()->loans;
        return view('loans.index', compact('loans'));
    }
}
