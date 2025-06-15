<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Loan;
use App\Models\LoanCategory;

class LoanController extends Controller
{
    /**
     * Display user's loans.
     */
    public function index()
    {
        $loans = Loan::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('loans.index', compact('loans'));
    }

    /**
     * Show loan application form.
     */
    public function create(Request $request)
    {
        $category = null;
        if ($request->has('category')) {
            $category = LoanCategory::findOrFail($request->category);
        }
        
        $categories = LoanCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('loans.create', compact('categories', 'category'));
    }

    /**
     * Store a new loan application.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:5000|max:1000000',
            'duration' => 'required|integer|min:1|max:36',
            'loan_type' => 'required|in:fiat,crypto',
            'crypto_currency' => 'nullable|required_if:loan_type,crypto|in:BTC,ETH,USDT',
            'purpose' => 'required|string|max:500',
            'terms_accepted' => 'required|accepted',
            'loan_category_id' => 'required|exists:loan_categories,id',
        ]);

        // Prevent duplicate loan applications
        if (Loan::where(['user_id' => Auth::id(), 'status' => 'pending'])->exists()) {
            return back()->with('error', 'You already have a pending loan application.');
        }

        // Get loan category for interest rate
        $category = LoanCategory::findOrFail($validated['loan_category_id']);

        // Fetch exchange rate if loan type is crypto
        $exchangeRate = $validated['loan_type'] === 'crypto' ? $this->fetchCryptoExchangeRate($validated['crypto_currency']) : null;
        if ($validated['loan_type'] === 'crypto' && !$exchangeRate) {
            return back()->with('error', 'Invalid cryptocurrency selection.');
        }

        // Create loan
        Loan::create([
            'user_id' => Auth::id(),
            'loan_category_id' => $validated['loan_category_id'],
            'amount' => $validated['amount'],
            'duration' => $validated['duration'],
            'interest_rate' => $category->interest_rate,
            'status' => 'pending',
            'due_date' => now()->addMonths($validated['duration']),
            'loan_type' => $validated['loan_type'],
            'crypto_currency' => $validated['crypto_currency'] ?? null,
            'exchange_rate' => $exchangeRate,
            'purpose' => $validated['purpose'],
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan request submitted successfully!');
    }

    /**
     * Display a specific loan.
     */
    public function show(Loan $loan)
    {
        // Ensure user can only view their own loans
        if ($loan->user_id !== Auth::id()) {
            abort(403);
        }

        return view('loans.show', compact('loan'));
    }

    /**
     * Fetch crypto exchange rate (Mock API - Replace with real API)
     */
    private function fetchCryptoExchangeRate($crypto)
    {
        return [
            'BTC' => 62000000,
            'ETH' => 4200000,
            'USDT' => 1500,
        ][$crypto] ?? null;
    }

    /**
     * Display available loan categories
     */
    public function categories()
    {
        $categories = LoanCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('loans.categories', compact('categories'));
    }

    /**
     * Display detailed information about a loan category
     */
    public function categoryDetails(LoanCategory $category)
    {
        return view('loans.category-details', compact('category'));
    }
}
