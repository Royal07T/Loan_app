<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Loan;
use App\Models\User;

class LoanPdfController extends Controller
{
    /**
     * Generate PDF for a user's loan details
     */
    public function generateLoanReport($user_id)
    {
        // Get User & Loan Details
        $user = User::findOrFail($user_id);
        $loans = Loan::where('user_id', $user_id)->get();

        // Load View with Data
        $pdf = Pdf::loadView('pdf.loan_report', compact('user', 'loans'));

        // Download PDF File
        return $pdf->download("Loan_Report_{$user->name}.pdf");
    }
}
