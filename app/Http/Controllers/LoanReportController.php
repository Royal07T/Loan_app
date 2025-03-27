<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use Barryvdh\DomPDF\Facade\Pdf;

class LoanReportController extends Controller
{
    /**
     * Export loans to PDF.
     */
    public function exportPDF()
    {
        // Fetch loans with user data for better context
        $loans = Loan::with('user')->get();

        // Handle case where no loans exist
        if ($loans->isEmpty()) {
            return back()->with('error', 'No loan records available.');
        }

        // Load PDF view
        $pdf = Pdf::loadView('reports.loans_pdf', compact('loans'));

        // Generate a timestamped filename
        $filename = 'Loan_Report_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }
}
