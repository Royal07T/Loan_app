<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminLoanController extends Controller
{
    /**
     * Show all pending loans for admin approval.
     */
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

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

        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        if ($loan->status !== 'pending') {
            return redirect()->back()->with('error', 'Loan has already been processed.');
        }

        // ✅ Use DB transaction properly
        try {
            DB::beginTransaction(); // Start transaction

            $loan->update(['status' => $request->status]);

            DB::commit(); // Commit transaction
            return redirect()->back()->with('success', 'Loan status updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if an error occurs
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}
