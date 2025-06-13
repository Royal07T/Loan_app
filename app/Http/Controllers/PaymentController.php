<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Repayment;
use App\Services\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    /**
     * Initialize payment for loan repayment
     */
    public function initializePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|in:NGN,USD,EUR,GBP',
            'payment_method' => 'required|in:card,bank,crypto',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $loan = Loan::findOrFail($request->loan_id);
        
        // Ensure user owns the loan
        if ($loan->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Check if loan is approved
        if ($loan->status !== 'approved' && $loan->status !== 'partial') {
            return response()->json([
                'success' => false,
                'message' => 'Loan is not approved for repayment'
            ], 400);
        }

        // Check if amount is valid
        $remainingBalance = $loan->getRemainingAmount();
        if ($request->amount > $remainingBalance) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount exceeds remaining balance'
            ], 400);
        }

        try {
            $paymentData = [
                'user_id' => Auth::id(),
                'loan_id' => $loan->id,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'email' => Auth::user()->email,
                'payment_type' => 'loan_repayment',
                'callback_url' => route('payment.callback'),
            ];

            $result = $this->paymentManager->initializePayment($paymentData);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Payment initialized successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Payment initialization failed'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Payment initialization error', [
                'user_id' => Auth::id(),
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed'
            ], 500);
        }
    }

    /**
     * Verify payment
     */
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->paymentManager->verifyPayment($request->reference);

            if ($result['success'] && $result['status'] === 'success') {
                // Process successful payment
                $this->processSuccessfulPayment($request->reference, $result);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Payment verification completed'
            ]);

        } catch (\Exception $e) {
            Log::error('Payment verification error', [
                'reference' => $request->reference,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed'
            ], 500);
        }
    }

    /**
     * Payment callback/webhook
     */
    public function paymentCallback(Request $request, string $gateway = 'paystack')
    {
        Log::info('Payment callback received', [
            'gateway' => $gateway,
            'data' => $request->all()
        ]);

        try {
            $reference = $request->input('reference') ?? $request->input('data.reference');
            
            if (!$reference) {
                return response()->json(['error' => 'No reference provided'], 400);
            }

            $result = $this->paymentManager->verifyPayment($reference, $gateway);

            if ($result['success'] && $result['status'] === 'success') {
                // Process successful payment
                $this->processSuccessfulPayment($reference, $result);
                
                return response()->json(['status' => 'success']);
            }

            return response()->json(['status' => 'failed']);

        } catch (\Exception $e) {
            Log::error('Payment callback error', [
                'gateway' => $gateway,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Callback processing failed'], 500);
        }
    }

    /**
     * Get payment history
     */
    public function paymentHistory()
    {
        $transactions = $this->paymentManager->getUserPaymentHistory(Auth::id());

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'message' => 'Payment history retrieved successfully'
        ]);
    }

    /**
     * Get available payment gateways
     */
    public function getGateways()
    {
        $gateways = $this->paymentManager->getAvailableGateways();

        return response()->json([
            'success' => true,
            'data' => $gateways,
            'message' => 'Available gateways retrieved successfully'
        ]);
    }

    /**
     * Process successful payment
     */
    protected function processSuccessfulPayment(string $reference, array $paymentData)
    {
        DB::beginTransaction();

        try {
            $transaction = $this->paymentManager->getTransaction($reference);
            
            if (!$transaction) {
                throw new \Exception('Transaction not found');
            }

            $loan = $transaction->loan;
            
            if (!$loan) {
                throw new \Exception('Loan not found');
            }

            // Create repayment record
            Repayment::create([
                'loan_id' => $loan->id,
                'user_id' => $transaction->user_id,
                'amount_paid' => $transaction->amount,
                'payment_method' => $paymentData['payment_method'] ?? 'card',
                'transaction_reference' => $reference,
                'status' => 'completed',
            ]);

            // Update loan status
            $loan->updateLoanStatus();

            DB::commit();

            Log::info('Payment processed successfully', [
                'reference' => $reference,
                'loan_id' => $loan->id,
                'amount' => $transaction->amount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing error', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
