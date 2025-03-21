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
        // ✅ Fetch pending loans with pagination (better performance)
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

            $loan->update(['status' => $request->status]);

            // ✅ Send notification to user
            $loan->user->notify(new LoanStatusNotification($loan, $request->status));

            DB::commit();
            return redirect()->back()->with('success', 'Loan status updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}
