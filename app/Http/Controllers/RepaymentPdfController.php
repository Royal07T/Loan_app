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

        // Load the view with repayment data
        $pdf = Pdf::loadView('pdf.repayment_statement', compact('loan', 'repayments'));

        // Download PDF
        return $pdf->download("Repayment_Statement_Loan_{$loan->id}.pdf");
    }
}
