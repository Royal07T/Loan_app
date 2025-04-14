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

// Public route
Route::get("/", fn() => view('welcome'));

// Auth routes
Auth::routes();

// Authenticated User Routes
Route::middleware('auth')->group(function () {
    // Loan routes
    Route::get('/loans/apply', [LoanController::class, 'create'])->name('loans.apply');
    Route::post('/loans/store', [LoanController::class, 'store'])->name('loans.store');
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');

    // Repayment routes
    Route::get('/repayments', [RepaymentController::class, 'index'])->name('repayments.index');
    Route::post('/repayments/{loan}', [RepaymentController::class, 'store'])->name('repayments.store');

    // PDF Exports
    Route::get('/export-loans-pdf', [LoanReportController::class, 'exportPDF'])->name('export.loans.pdf');
    Route::get('/loan-report/{user_id}', [LoanPdfController::class, 'generateLoanReport'])->name('loan.report');
    Route::get('/repayment-report/{loan_id}', [RepaymentPdfController::class, 'generateRepaymentReport'])->name('repayment.report');

    // Crypto routes 🪙
    Route::prefix('crypto')->name('crypto.')->group(function () {
        Route::get('/balance/{address}', [CryptoController::class, 'getBalance'])->name('balance');
        Route::get('/wallet', [CryptoController::class, 'walletInfo'])->name('wallet'); // user wallet view
        Route::post('/send', [CryptoController::class, 'sendCrypto'])->name('send');
        Route::post('/receive', [CryptoController::class, 'receiveCrypto'])->name('receive');
        Route::get('/transactions', [CryptoController::class, 'transactionHistory'])->name('transactions');
    });
});

// Admin-only routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/loans', [AdminLoanController::class, 'index'])->name('admin.loans');
    Route::post('/admin/loans/{loan}', [AdminLoanController::class, 'update'])->name('admin.loans.update');
    Route::get('/admin/analytics', [LoanAnalyticsController::class, 'index'])->name('admin.analytics');
});

// Home dashboard
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
