<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        // ✅ Validate input
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        // ✅ Ensure loan is still pending before updating
        if ($loan->status !== 'pending') {
            return redirect()->back()->with('error', 'Loan has already been processed.');
        }

        // ✅ Secure transaction to update loan status
        try {
            DB::beginTransaction();
            $loan->update(['status' => $request->status]);
            DB::commit();

            return redirect()->back()->with('success', 'Loan status updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Loan Update Error', ['loan_id' => $loan->id, 'error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}
