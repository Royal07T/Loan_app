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
        $request->validate([
            'amount' => 'required|numeric|min:5000',
            'duration' => 'required|integer|min:1',
        ]);

        Loan::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'duration' => $request->duration,
            'interest_rate' => 10.00, // Default 10% interest rate
            'status' => 'pending',
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan request submitted!');
    }

    /**
     * Show all loan applications for the logged-in user.
     */
    public function index()
    {
        $loans = Auth::user()->loans;
        return view('loans.index', compact('loans'));
    }
}
