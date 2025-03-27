<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Loan;
use App\Models\Repayment;

class RepaymentPdfController extends Controller
{
    /**
     * Generate a PDF for a user's repayment history.
     */
    public function generateRepaymentReport($loan_id)
    {
        // Fetch loan and repayments with eager loading
        $loan = Loan::with('user')->findOrFail($loan_id);
        $repayments = Repayment::where('loan_id', $loan_id)->get();

        // Handle case where no repayments exist
        if ($repayments->isEmpty()) {
            return back()->with('error', 'No repayment records found for this loan.');
        }

        // Calculate total payments and remaining balance
        $totalPaid = $repayments->sum('amount_paid');
        $remainingBalance = max($loan->amount - $totalPaid, 0);
        $totalLateFees = $repayments->sum('late_fee'); // If applicable

        // Generate PDF
        $pdf = Pdf::loadView('pdf.repayment_statement', compact('loan', 'repayments', 'remainingBalance', 'totalLateFees'));

        // Use a timestamped filename for better organization
        $filename = "Repayment_Statement_Loan_{$loan->id}_" . now()->format('Y-m-d_H-i-s') . ".pdf";

        return $pdf->download($filename);
    }
}
