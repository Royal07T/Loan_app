<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LoanController extends Controller
{
    /**
     * Display a listing of the user's loans.
     */
    public function index(): JsonResponse
    {
        $loans = Auth::user()->loans()
            ->with(['category', 'repayments'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $loans,
            'message' => 'Loans retrieved successfully'
        ]);
    }

    /**
     * Store a newly created loan application.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'loan_category_id' => 'required|exists:loan_categories,id',
            'amount' => 'required|numeric|min:5000|max:1000000',
            'duration' => 'required|integer|min:1|max:36',
            'currency' => ['required', Rule::in(['NGN', 'BTC', 'ETH', 'USDT'])],
            'collateral_info' => 'nullable|array',
            'documents' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user has pending loan
        if (Auth::user()->loans()->where('status', 'pending')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending loan application'
            ], 400);
        }

        // Validate loan amount against category limits
        $category = LoanCategory::find($request->loan_category_id);
        if (!$category->isAmountAllowed($request->amount)) {
            return response()->json([
                'success' => false,
                'message' => 'Loan amount is outside the allowed range for this category'
            ], 400);
        }

        $loan = Loan::create([
            'user_id' => Auth::id(),
            'loan_category_id' => $request->loan_category_id,
            'amount' => $request->amount,
            'duration' => $request->duration,
            'currency' => $request->currency,
            'interest_rate' => $category->interest_rate,
            'status' => 'pending',
            'collateral_info' => $request->collateral_info,
            'documents' => $request->documents,
        ]);

        return response()->json([
            'success' => true,
            'data' => $loan->load('category'),
            'message' => 'Loan application submitted successfully'
        ], 201);
    }

    /**
     * Display the specified loan.
     */
    public function show(Loan $loan): JsonResponse
    {
        // Ensure user owns the loan
        if ($loan->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $loan->load(['category', 'repayments']);

        return response()->json([
            'success' => true,
            'data' => $loan,
            'message' => 'Loan details retrieved successfully'
        ]);
    }

    /**
     * Get loan categories.
     */
    public function categories(): JsonResponse
    {
        $categories = LoanCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Loan categories retrieved successfully'
        ]);
    }

    /**
     * Get loan payment schedule.
     */
    public function paymentSchedule(Loan $loan): JsonResponse
    {
        // Ensure user owns the loan
        if ($loan->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $schedule = $loan->getPaymentSchedule();

        return response()->json([
            'success' => true,
            'data' => $schedule,
            'message' => 'Payment schedule retrieved successfully'
        ]);
    }

    /**
     * Get loan statistics for the authenticated user.
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();
        
        $statistics = [
            'total_loans' => $user->loans()->count(),
            'active_loans' => $user->loans()->whereIn('status', ['approved', 'partial'])->count(),
            'pending_loans' => $user->loans()->where('status', 'pending')->count(),
            'paid_loans' => $user->loans()->where('status', 'paid')->count(),
            'total_outstanding' => $user->getTotalOutstandingLoans(),
            'has_overdue' => $user->hasOverdueLoans(),
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics,
            'message' => 'Loan statistics retrieved successfully'
        ]);
    }
}
