@extends('layouts.app')

@section('title', 'Dashboard - FinTech Pro')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="flex flex-col items-center text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full mb-4 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-1">Welcome, {{ Auth::user()->name }}!</h1>
            <p class="text-lg text-gray-600 max-w-xl">Your personal dashboard gives you a snapshot of your loans, wallet, and account status.</p>
        </div>

        <!-- KYC Status Card -->
        <div class="mb-8 flex justify-center">
            <div class="w-full md:w-2/3">
                <kyc-status-card 
                    :initial-status="'{{ Auth::user()->kyc_status ?? 'not_started' }}'"
                    :initial-data='@json(Auth::user()->kyc_data ?? [])'
                    @start-kyc="redirectToKYC"
                    @resubmit-kyc="redirectToKYC"
                    @status-changed="handleKYCStatusChange"
                ></kyc-status-card>
            </div>
        </div>

        <!-- Stats & Actions Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <!-- Stats Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8 flex flex-col justify-between h-full">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    Account Overview
                </h2>
                <div class="space-y-6 divide-y divide-gray-100">
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-gray-600 font-medium">Active Loans</span>
                        <span class="text-2xl font-bold text-blue-700">{{ Auth::user()->loans()->where('status', 'active')->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-4">
                        <span class="text-gray-600 font-medium">Total Repayments</span>
                        <span class="text-2xl font-bold text-green-700">{{ Auth::user()->repayments()->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-4">
                        <span class="text-gray-600 font-medium">Wallet Balance</span>
                        <span class="text-2xl font-bold text-purple-700">
                            @if(Auth::user()->wallet)
                                {{ number_format(Auth::user()->wallet->balance, 2) }} ETH
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            <!-- Quick Actions Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8 flex flex-col justify-between h-full">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Quick Actions
                </h2>
                <div class="grid grid-cols-1 gap-4">
                    <a href="{{ route('loans.apply') }}" class="flex items-center px-5 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold shadow hover:from-blue-700 hover:to-purple-700 transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Apply for Loan
                    </a>
                    <a href="{{ route('loans.index') }}" class="flex items-center px-5 py-3 rounded-xl bg-gradient-to-r from-green-500 to-emerald-500 text-white font-semibold shadow hover:from-green-600 hover:to-emerald-600 transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        My Loans
                    </a>
                    <a href="{{ route('wallet.info') }}" class="flex items-center px-5 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold shadow hover:from-purple-600 hover:to-pink-600 transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        Wallet
                    </a>
                    <a href="{{ route('documents.my') }}" class="flex items-center px-5 py-3 rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-semibold shadow hover:from-yellow-500 hover:to-orange-600 transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Documents
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h2a4 4 0 014 4v2M9 17H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4"></path>
                </svg>
                Recent Activity
            </h2>
            @php
                $recentLoans = Auth::user()->loans()->latest()->take(5)->get();
                $recentRepayments = Auth::user()->repayments()->latest()->take(5)->get();
            @endphp
            @if($recentLoans->count() > 0 || $recentRepayments->count() > 0)
                <div class="space-y-4">
                    @foreach($recentLoans as $loan)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-bold text-gray-900">Loan Application</p>
                                    <p class="text-sm text-gray-500">Amount: ₦{{ number_format($loan->amount) }} - {{ ucfirst($loan->status) }}</p>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $loan->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @endforeach
                    @foreach($recentRepayments as $repayment)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 text-green-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-bold text-gray-900">Repayment</p>
                                    <p class="text-sm text-gray-500">Amount: ₦{{ number_format($repayment->amount) }}</p>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $repayment->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No recent activity</p>
            @endif
        </div>
    </div>
</div>

<script>
window.redirectToKYC = function() {
    window.location.href = '/kyc';
};
window.handleKYCStatusChange = function(status) {
    if (status === 'verified') {
        showNotification('KYC verification completed successfully!', 'success');
    }
};
</script>
@endsection
