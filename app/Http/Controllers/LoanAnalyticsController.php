<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Charts\LoanChart;

class LoanAnalyticsController extends Controller
{
    public function index()
    {
        // Check if there are loans before charting
        $loans = Loan::all();

        if ($loans->isEmpty()) {
            return view('admin.analytics')->with('error', 'No loan data available.');
        }

        // Create a new LoanChart instance
        $loanChart = new LoanChart;
        $loanChart->labels(['Pending', 'Approved', 'Rejected', 'Paid'])
            ->dataset('Loans by Status', 'bar', [
                Loan::where('status', 'pending')->count(),
                Loan::where('status', 'approved')->count(),
                Loan::where('status', 'rejected')->count(),
                Loan::where('status', 'paid')->count(),
            ])
            ->backgroundColor(['#FFCD56', '#36A2EB', '#FF6384', '#4BC0C0']);

        return view('admin.analytics', ['loanChart' => $loanChart->render()]);
    }
}
