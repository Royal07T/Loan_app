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
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\KYCController;

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
    Route::prefix('wallet')->name('wallet.')->middleware('rate.limit:30,1')->group(function () {
        Route::get('/info', [CryptoController::class, 'walletInfo'])->name('info');
        Route::get('/balance/{address}', [CryptoController::class, 'getBalance'])->name('balance');
        Route::post('/connect', [CryptoController::class, 'connectMetaMask'])->name('connect');
        Route::post('/disconnect', [CryptoController::class, 'disconnectWallet'])->name('disconnect');
        Route::get('/receive', [CryptoController::class, 'receiveCrypto'])->name('receive');
        Route::post('/receive-log', [CryptoController::class, 'logReceiveTransaction'])->name('receive.log');
        Route::get('/transactions', [CryptoController::class, 'transactionHistory'])->name('transactions');
        Route::get('/exchange-rates', [CryptoController::class, 'getExchangeRates'])->name('exchange-rates');
        Route::get('/status', [CryptoController::class, 'getWalletStatus'])->name('status');
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

    // Payment Routes
    Route::prefix('payments')->name('payments.')->middleware('rate.limit:30,1')->group(function () {
        Route::post('/initialize', [PaymentController::class, 'initializePayment'])->name('initialize');
        Route::post('/verify', [PaymentController::class, 'verifyPayment'])->name('verify');
        Route::get('/history', [PaymentController::class, 'paymentHistory'])->name('history');
        Route::get('/gateways', [PaymentController::class, 'getGateways'])->name('gateways');
    });

    // Payment Callbacks (no rate limiting for webhooks)
    Route::prefix('payment-callback')->name('payment.callback.')->group(function () {
        Route::post('/paystack', [PaymentController::class, 'paymentCallback'])->name('paystack');
        Route::post('/stripe', [PaymentController::class, 'paymentCallback'])->name('stripe');
    });

    // Document Management Routes
    Route::prefix('documents')->name('documents.')->middleware('rate.limit:30,1')->group(function () {
        Route::post('/upload', [DocumentController::class, 'upload'])->name('upload');
        Route::get('/my', [DocumentController::class, 'myDocuments'])->name('my');
        Route::get('/download/{document}', [DocumentController::class, 'download'])->name('download');
    });

    // Admin document review
    Route::middleware('admin')->prefix('admin/documents')->name('admin.documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'adminList'])->name('list');
        Route::post('/review/{document}', [DocumentController::class, 'review'])->name('review');
    });

    // KYC Routes
    Route::prefix('kyc')->name('kyc.')->middleware('rate.limit:30,1')->group(function () {
        Route::post('/initialize', [KYCController::class, 'initialize'])->name('initialize');
        Route::get('/status', [KYCController::class, 'getStatus'])->name('status');
        Route::get('/info', [KYCController::class, 'info'])->name('info');
        Route::get('/providers', [KYCController::class, 'providers'])->name('providers');
        Route::post('/resubmit', [KYCController::class, 'resubmit'])->name('resubmit');
    });

    // KYC Callbacks (no rate limiting for webhooks)
    Route::prefix('kyc-callback')->name('kyc.callback.')->group(function () {
        Route::post('/shuftipro', [KYCController::class, 'callback'])->name('shuftipro');
        Route::post('/smile-identity', [KYCController::class, 'callback'])->name('smile_identity');
        Route::post('/jumio', [KYCController::class, 'callback'])->name('jumio');
        Route::post('/onfido', [KYCController::class, 'callback'])->name('onfido');
        Route::post('/sumsub', [KYCController::class, 'callback'])->name('sumsub');
        Route::post('/veriff', [KYCController::class, 'callback'])->name('veriff');
    });

    // KYC Redirect
    Route::get('/kyc-redirect', [KYCController::class, 'redirect'])->name('kyc.redirect');

    // KYC Result Pages
    Route::get('/kyc/success', function () {
        return view('kyc.success');
    })->name('kyc.success');

    Route::get('/kyc/error', function () {
        return view('kyc.error');
    })->name('kyc.error');

    Route::get('/kyc/cancelled', function () {
        return view('kyc.cancelled');
    })->name('kyc.cancelled');

    Route::get('/kyc/pending', function () {
        return view('kyc.pending');
    })->name('kyc.pending');

    // KYC Main Page
    Route::get('/kyc', function () {
        return view('kyc.index');
    })->name('kyc.index');

    // KYC Vue Demo Page
    Route::get('/kyc/demo', function () {
        return view('kyc.vue-demo');
    })->name('kyc.demo');

    // Admin KYC Management Routes
    Route::middleware('admin')->prefix('admin/kyc')->name('admin.kyc.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\KYCAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/', [App\Http\Controllers\Admin\KYCAdminController::class, 'index'])->name('index');
        Route::get('/export', [App\Http\Controllers\Admin\KYCAdminController::class, 'export'])->name('export');
        Route::get('/{user}', [App\Http\Controllers\Admin\KYCAdminController::class, 'show'])->name('show');
        Route::post('/{user}/approve', [App\Http\Controllers\Admin\KYCAdminController::class, 'approve'])->name('approve');
        Route::post('/{user}/reject', [App\Http\Controllers\Admin\KYCAdminController::class, 'reject'])->name('reject');
        Route::post('/{user}/reset', [App\Http\Controllers\Admin\KYCAdminController::class, 'reset'])->name('reset');
        Route::post('/bulk-action', [App\Http\Controllers\Admin\KYCAdminController::class, 'bulkAction'])->name('bulk-action');
    });
});
