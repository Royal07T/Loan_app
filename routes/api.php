<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\RepaymentController;
use App\Http\Controllers\Api\WalletController;
// use App\Http\Controllers\Api\KYCController; // Commented out - controller doesn't exist

// Public routes
Route::get('/health', function () {
    return response()->json(['status' => 'healthy', 'timestamp' => now()]);
});

// Protected API routes
Route::middleware(['auth:sanctum', 'rate.limit:60,1'])->group(function () {
    // User info
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
            'message' => 'User information retrieved successfully'
        ]);
    });

    // Loan routes
    Route::prefix('loans')->name('api.loans.')->group(function () {
        Route::get('/', [LoanController::class, 'index'])->name('index');
        Route::post('/', [LoanController::class, 'store'])->name('store');
        Route::get('/categories', [LoanController::class, 'categories'])->name('categories');
        Route::get('/statistics', [LoanController::class, 'statistics'])->name('statistics');
        Route::get('/{loan}', [LoanController::class, 'show'])->name('show');
        Route::get('/{loan}/payment-schedule', [LoanController::class, 'paymentSchedule'])->name('payment-schedule');
    });

    // Repayment routes
    Route::prefix('repayments')->name('api.repayments.')->group(function () {
        Route::get('/', [RepaymentController::class, 'index'])->name('index');
        Route::post('/{loan}', [RepaymentController::class, 'store'])->name('store');
        Route::get('/statistics', [RepaymentController::class, 'statistics'])->name('statistics');
        Route::get('/{repayment}', [RepaymentController::class, 'show'])->name('show');
        Route::get('/loan/{loan}', [RepaymentController::class, 'loanRepayments'])->name('loan-repayments');
    });

    // Wallet routes
    Route::prefix('wallet')->name('api.wallet.')->group(function () {
        Route::get('/info', [WalletController::class, 'info'])->name('info');
        Route::post('/connect', [WalletController::class, 'connect'])->name('connect');
        Route::delete('/disconnect', [WalletController::class, 'disconnect'])->name('disconnect');
        Route::get('/balance', [WalletController::class, 'balance'])->name('balance');
        Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');
    });

    // KYC routes - Commented out until API controller is created
    // Route::prefix('kyc')->name('api.kyc.')->group(function () {
    //     Route::post('/initialize', [KYCController::class, 'initialize'])->name('initialize');
    //     Route::get('/status', [KYCController::class, 'status'])->name('status');
    //     Route::get('/info', [KYCController::class, 'info'])->name('info');
    //     Route::get('/providers', [KYCController::class, 'providers'])->name('providers');
    //     Route::post('/resubmit', [KYCController::class, 'resubmit'])->name('resubmit');
    // });
});
