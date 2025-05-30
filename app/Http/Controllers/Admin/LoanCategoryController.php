<?php

namespace App\Http\Controllers\Admin;

use App\Models\LoanCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoanCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = LoanCategory::withCount('loans')
            ->orderBy('name')
            ->get();

        return view('admin.loan-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.loan-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);
        
        $category = LoanCategory::create($validated);

        return redirect()
            ->route('admin.loan-categories.index')
            ->with('success', 'Loan category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LoanCategory $loanCategory)
    {
        $loanCategory->load(['loans' => function($query) {
            $query->latest()->limit(10);
        }]);

        return view('admin.loan-categories.show', compact('loanCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LoanCategory $loanCategory)
    {
        return view('admin.loan-categories.edit', compact('loanCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LoanCategory $loanCategory)
    {
        $validated = $this->validateCategory($request);
        
        $loanCategory->update($validated);

        return redirect()
            ->route('admin.loan-categories.index')
            ->with('success', 'Loan category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LoanCategory $loanCategory)
    {
        if ($loanCategory->loans()->exists()) {
            return back()->with('error', 'Cannot delete category with existing loans.');
        }

        $loanCategory->delete();

        return redirect()
            ->route('admin.loan-categories.index')
            ->with('success', 'Loan category deleted successfully.');
    }

    protected function validateCategory(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'max_term_months' => 'required|integer|min:1|max:360',
            'late_payment_fee' => 'required|numeric|min:0',
            'processing_fee' => 'required|numeric|min:0|max:100',
            'requires_collateral' => 'required|boolean',
            'required_documents' => 'nullable|array',
            'required_documents.*' => 'string',
            'is_active' => 'required|boolean'
        ]);
    }
}
