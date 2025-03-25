<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
//use App\Http\Controllers\Charts;

class LoanAnalyticsController extends Controller
{
    public function index()
    {
        //  Ensure there are loans before charting
        $loans = Loan::all();

        if ($loans->isEmpty()) {
            return view('admin.analytics')->with('error', 'No loan data available.');
        }

        $loanChart = Charts::database($loans, 'bar', 'chartjs')   //Changed to chartjs
            ->title("Loans Statistics")
            ->elementLabel("Total Loans")
            ->dimensions(1000, 500)
            ->responsive(true)
            ->groupBy('status');

        return view('admin.analytics', compact('loanChart'));
    }
}
