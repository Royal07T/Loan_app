<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Enforce authentication middleware.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the dashboard based on user role.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Role-based redirection
        if ($user->hasRole('admin')) {
            return view('admin.dashboard', compact('user'));
        }

        return view('home', compact('user'));
    }
}
