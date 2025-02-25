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
        $loans = Loan::all(); // Fetch all loans
        $pdf = Pdf::loadView('reports.loans_pdf', compact('loans'));

        return $pdf->download('loans.pdf');
    }
}
