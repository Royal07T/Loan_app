<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Repayment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RepaymentController extends Controller
{
    /**
     * Display a listing of the user's repayments.
     */
    public function index(): JsonResponse
    {
        $repayments = Auth::user()->repayments()
            ->with(['loan.category'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $repayments,
            'message' => 'Repayments retrieved successfully'
        ]);
    }

    /**
     * Store a newly created repayment.
     */
    public function store(Request $request, Loan $loan): JsonResponse
    {
        // Ensure user owns the loan
        if ($loan->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'amount_paid' => 'required|numeric|min:1',
            'payment_method' => ['required', Rule::in(['bank', 'crypto', 'card'])],
            'crypto_currency' => ['nullable', Rule::requiredIf($request->payment_method === 'crypto'), Rule::in(['BTC', 'ETH', 'USDT'])],
            'transaction_reference' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if loan is approved
        if ($loan->status !== 'approved' && $loan->status !== 'partial') {
            return response()->json([
                'success' => false,
                'message' => 'Loan is not approved for repayment'
            ], 400);
        }

        // Get remaining balance
        $remainingBalance = $loan->getRemainingAmount();

        if ($request->amount_paid > $remainingBalance) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount exceeds remaining balance'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $repayment = Repayment::create([
                'loan_id' => $loan->id,
                'user_id' => Auth::id(),
                'amount_paid' => $request->amount_paid,
                'payment_method' => $request->payment_method,
                'crypto_currency' => $request->crypto_currency,
                'transaction_reference' => $request->transaction_reference,
                'status' => 'completed',
            ]);

            // Update loan status
            $loan->updateLoanStatus();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $repayment->load('loan'),
                'message' => 'Repayment processed successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process repayment'
            ], 500);
        }
    }

    /**
     * Display the specified repayment.
     */
    public function show(Repayment $repayment): JsonResponse
    {
        // Ensure user owns the repayment
        if ($repayment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $repayment->load(['loan.category']);

        return response()->json([
            'success' => true,
            'data' => $repayment,
            'message' => 'Repayment details retrieved successfully'
        ]);
    }

    /**
     * Get repayment history for a specific loan.
     */
    public function loanRepayments(Loan $loan): JsonResponse
    {
        // Ensure user owns the loan
        if ($loan->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $repayments = $loan->repayments()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $repayments,
            'message' => 'Loan repayments retrieved successfully'
        ]);
    }

    /**
     * Get repayment statistics for the authenticated user.
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();
        
        $statistics = [
            'total_repayments' => $user->repayments()->count(),
            'total_amount_paid' => $user->repayments()->sum('amount_paid'),
            'this_month_payments' => $user->repayments()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount_paid'),
            'payment_methods' => $user->repayments()
                ->selectRaw('payment_method, COUNT(*) as count')
                ->groupBy('payment_method')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics,
            'message' => 'Repayment statistics retrieved successfully'
        ]);
    }
}
