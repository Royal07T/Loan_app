@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h1>
        <p class="text-gray-600 mt-2">Here's an overview of your account</p>
    </div>

    <!-- KYC Status Card -->
    <div class="mb-8">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">KYC Verification Status</h3>
            </div>
            <div class="p-6">
                @php
                    $kycStatus = Auth::user()->kyc_status ?? 'not_started';
                    $kycStatusWithExpiry = Auth::user()->getKYCStatusWithExpiry();
                @endphp

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center
                                @if($kycStatus === 'verified') bg-green-100 text-green-600
                                @elseif($kycStatus === 'pending') bg-yellow-100 text-yellow-600
                                @elseif($kycStatus === 'rejected') bg-red-100 text-red-600
                                @else bg-gray-100 text-gray-600
                                @endif">
                                @if($kycStatus === 'verified')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @elseif($kycStatus === 'pending')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @elseif($kycStatus === 'rejected')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-medium text-gray-900">
                                @if($kycStatus === 'verified')
                                    KYC Verified
                                @elseif($kycStatus === 'pending')
                                    KYC Under Review
                                @elseif($kycStatus === 'rejected')
                                    KYC Rejected
                                @else
                                    KYC Not Started
                                @endif
                            </h4>
                            <p class="text-sm text-gray-500">
                                @if($kycStatus === 'verified')
                                    Your identity has been verified successfully
                                    @if($kycStatusWithExpiry['expires_at'])
                                        <br>Expires: {{ $kycStatusWithExpiry['expires_at']->format('M d, Y') }}
                                    @endif
                                @elseif($kycStatus === 'pending')
                                    Your verification is being reviewed by our team
                                @elseif($kycStatus === 'rejected')
                                    Your verification was not approved. Please check the reason and resubmit.
                                @else
                                    Complete your KYC verification to access all features
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        @if($kycStatus === 'not_started' || $kycStatus === 'rejected')
                            <a href="{{ route('kyc.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                @if($kycStatus === 'rejected')
                                    Resubmit KYC
                                @else
                                    Start KYC
                                @endif
                            </a>
                        @elseif($kycStatus === 'pending')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                In Progress
                            </span>
                        @elseif($kycStatus === 'verified')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Verified
                            </span>
                        @endif
                    </div>
                </div>

                @if($kycStatus === 'rejected' && Auth::user()->kyc_data && isset(Auth::user()->kyc_data['admin_rejection']))
                    <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
                        <h5 class="text-sm font-medium text-red-800 mb-2">Rejection Reason</h5>
                        <p class="text-sm text-red-700">{{ Auth::user()->kyc_data['admin_rejection']['reason'] }}</p>
                        @if(Auth::user()->kyc_data['admin_rejection']['notes'])
                            <p class="text-sm text-red-600 mt-1">{{ Auth::user()->kyc_data['admin_rejection']['notes'] }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Loans</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ Auth::user()->loans()->where('status', 'active')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Repayments</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ Auth::user()->repayments()->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Wallet Balance</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        @if(Auth::user()->wallet)
                            {{ number_format(Auth::user()->wallet->balance, 2) }} ETH
                        @else
                            N/A
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <a href="{{ route('loans.apply') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Apply for Loan</h3>
                    <p class="text-sm text-gray-500">Start a new loan application</p>
                </div>
            </div>
        </a>

        <a href="{{ route('loans.index') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">My Loans</h3>
                    <p class="text-sm text-gray-500">View your loan history</p>
                </div>
            </div>
        </a>

        <a href="{{ route('wallet.info') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Wallet</h3>
                    <p class="text-sm text-gray-500">Manage your crypto wallet</p>
                </div>
            </div>
        </a>

        <a href="{{ route('documents.my') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Documents</h3>
                    <p class="text-sm text-gray-500">Manage your documents</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
        </div>
        <div class="p-6">
            @php
                $recentLoans = Auth::user()->loans()->latest()->take(5)->get();
                $recentRepayments = Auth::user()->repayments()->latest()->take(5)->get();
            @endphp

            @if($recentLoans->count() > 0 || $recentRepayments->count() > 0)
                <div class="space-y-4">
                    @foreach($recentLoans as $loan)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-2 rounded-full bg-blue-100 text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Loan Application</p>
                                    <p class="text-sm text-gray-500">Amount: ${{ number_format($loan->amount, 2) }} - {{ ucfirst($loan->status) }}</p>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $loan->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @endforeach

                    @foreach($recentRepayments as $repayment)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-2 rounded-full bg-green-100 text-green-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Repayment</p>
                                    <p class="text-sm text-gray-500">Amount: ${{ number_format($repayment->amount, 2) }}</p>
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
@endsection
