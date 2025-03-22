<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalLoans = Loan::count();
        $pendingLoans = Loan::where('status', 'pending')->count();
        $approvedLoans = Loan::where('status', 'approved')->count();
        $rejectedLoans = Loan::where('status', 'rejected')->count();
        $totalUsers = User::count();

        return view('admin.dashboard', compact('totalLoans', 'pendingLoans', 'approvedLoans', 'rejectedLoans', 'totalUsers'));
    }
}
