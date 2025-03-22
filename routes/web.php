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



Route::get("/", function () {
    return view('welcome');
});

Auth::routes();

// Loan Routes (User Access)
Route::middleware('auth')->group(function () {
    Route::get('/loans/apply', [LoanController::class, 'create'])->name('loans.apply');
    Route::post('/loans/store', [LoanController::class, 'store'])->name('loans.store');
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');

    // Loan Repayment Routes
    Route::get('/repayments', [RepaymentController::class, 'index'])->name('repayments.index');
    Route::post('/repayments/{loan}', [RepaymentController::class, 'store'])->name('repayments.store');
});



// Admin Loan Management (Only Admins)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/loans', [AdminLoanController::class, 'index'])->name('admin.loans');
    Route::post('/admin/loans/{loan}', [AdminLoanController::class, 'update'])->name('admin.loans.update');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

Route::get('/admin/analytics', [LoanAnalyticsController::class, 'index'])->middleware('admin')->name('admin.analytics');


Route::middleware('auth')->group(function () {
    Route::get('/export-loans-pdf', [LoanReportController::class, 'exportPDF'])->name('export.loans.pdf');
});

// Generate Loan Report PDF
Route::get('/loan-report/{user_id}', [LoanPdfController::class, 'generateLoanReport'])
    ->middleware('auth')
    ->name('loan.report');

// Generate Repayment History PDF
Route::get('/repayment-report/{loan_id}', [RepaymentPdfController::class, 'generateRepaymentReport'])
    ->middleware('auth')
    ->name('repayment.report');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
