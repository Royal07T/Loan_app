<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoanPdfController;
use App\Http\Controllers\AdminLoanController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\LoanReportController;
use App\Http\Controllers\RepaymentPdfController;
use App\Http\Controllers\LoanAnalyticsController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\LoanCategoryController;

// Public route
Route::get('/', fn() => view('welcome'));

// Auth routes
Auth::routes();

// Authenticated user routes
Route::middleware('auth')->group(function () {
    // Home Dashboard
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Loan Routes
    Route::get('/loans/apply', [LoanController::class, 'create'])->name('loans.apply');
    Route::post('/loans/store', [LoanController::class, 'store'])->name('loans.store');
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
    Route::get('/loans/categories', [LoanController::class, 'categories'])->name('loans.categories');
    Route::get('/loans/categories/{category}', [LoanController::class, 'categoryDetails'])->name('loans.category.details');

    // Repayment Routes
    Route::get('/repayments', [RepaymentController::class, 'index'])->name('repayments.index');
    Route::post('/repayments/{loan}', [RepaymentController::class, 'store'])->name('repayments.store');

    // PDF Exports
    Route::get('/export-loans-pdf', [LoanReportController::class, 'exportPDF'])->name('export.loans.pdf');
    Route::get('/loan-report/{user_id}', [LoanPdfController::class, 'generateLoanReport'])->name('loan.report');
    Route::get('/repayment-report/{loan_id}', [RepaymentPdfController::class, 'generateRepaymentReport'])->name('repayment.report');

    // Wallet/Crypto Routes (Unified)
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/info', [CryptoController::class, 'walletInfo'])->name('info');
        Route::get('/balance/{address}', [CryptoController::class, 'getBalance'])->name('balance');
        Route::post('/send', [CryptoController::class, 'sendCrypto'])->name('send');
        Route::get('/receive', [CryptoController::class, 'receiveCrypto'])->name('receive');
        Route::post('/receive-log', [CryptoController::class, 'logReceiveTransaction'])->name('receive.log');
        Route::get('/transactions', [CryptoController::class, 'transactionHistory'])->name('transactions');
    });

    // Admin-only Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/loans', [AdminLoanController::class, 'index'])->name('loans');
        Route::post('/loans/{loan}', [AdminLoanController::class, 'update'])->name('loans.update');
        Route::get('/analytics', [LoanAnalyticsController::class, 'index'])->name('analytics');
        
        // Loan Categories Management
        Route::resource('loan-categories', LoanCategoryController::class);
    });
});
