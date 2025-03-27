<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Loan;
use App\Models\User;

class LoanPdfController extends Controller
{
    /**
     * Generate PDF for a user's loan details.
     */
    public function generateLoanReport($id)
    {
        // Fetch user with loans (Eager Loading)
        $user = User::with('loans')->findOrFail($id);
        $loans = $user->loans;

        if ($loans->isEmpty()) {
            return back()->with('error', 'No loans found for this user.');
        }

        // Generate PDF
        $pdf = Pdf::loadView('pdf.loan_report', compact('user', 'loans'));

        // Sanitize filename
        $safeName = preg_replace('/[^A-Za-z0-9_-]/', '_', $user->name);

        // Download PDF
        return $pdf->download("Loan_Report_{$safeName}.pdf");
    }
}
