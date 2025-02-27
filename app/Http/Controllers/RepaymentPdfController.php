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
        // Fetch the loan and its repayments
        $loan = Loan::findOrFail($loan_id);
        $repayments = Repayment::where('loan_id', $loan_id)->get();

        // Calculate total paid amount
        $totalPaid = $repayments->sum('amount_paid');
        $remainingBalance = max($loan->amount - $totalPaid, 0);
        $totalLateFees = $repayments->sum('late_fee'); // If you store late fees

        // Load the view with repayment data
        $pdf = Pdf::loadView('pdf.repayment_statement', compact('loan', 'repayments', 'remainingBalance', 'totalLateFees'));

        // Download PDF
        return $pdf->download("Repayment_Statement_Loan_{$loan->id}.pdf");
    }
}
