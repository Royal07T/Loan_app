@extends('layouts.app')

@section('title', 'Wallet Info')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">🪙 Wallet Info</h1>
        <p class="text-gray-600 mt-2">View your crypto and fiat balances</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-blue-50 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Crypto Balance (ETH)</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($balanceInEth ?? 0, 6) }} ETH</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Fiat Balance (NGN)</p>
                        <p class="text-2xl font-semibold text-gray-900">₦{{ number_format($fiatBalance ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($walletStatus) && $walletStatus['is_connected'])
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Wallet Details</h3>
                <p class="text-sm text-gray-600">
                    <strong>Address:</strong> 
                    <span class="font-mono text-xs">{{ $walletStatus['address'] }}</span>
                </p>
                <p class="text-sm text-gray-600">
                    <strong>Type:</strong> {{ ucfirst($walletStatus['wallet_type']) }}
                </p>
            </div>
        @else
            <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                <h3 class="text-lg font-medium text-yellow-900 mb-2">No Wallet Connected</h3>
                <p class="text-sm text-yellow-700">Connect your MetaMask wallet to start using crypto features.</p>
                <button class="mt-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Connect Wallet
                </button>
            </div>
        @endif
    </div>
</div>
@endsection
