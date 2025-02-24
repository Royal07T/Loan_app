<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;

class AdminLoanController extends Controller
{
    /**
     * Show all pending loans for admin approval.
     */
    public function index()
    {
        $loans = Loan::where('status', 'pending')->get();
        return view('admin.loans', compact('loans'));
    }

    /**
     * Approve or Reject Loan.
     */
    public function update(Request $request, Loan $loan)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $loan->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Loan status updated!');
    }
}
