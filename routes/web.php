<?php

use App\Http\Controllers\LoanController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\AdminLoanController;
use Illuminate\Support\Facades\Route;


Route::get("/", function () {
    return view('welcome');
});

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
