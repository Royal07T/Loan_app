@extends('layouts.app')

@section('title', 'Loan Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">💰 Loan Details</h1>
        <p class="text-gray-600 mt-2">Loan #{{ $loan->id }}</p>
    </div>

    <div class="max-w-4xl mx-auto">
        <!-- Loan Status -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Status: {{ ucfirst($loan->status) }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Basic Information</h3>
                    <div class="space-y-2">
                        <p><span class="font-medium">Loan ID:</span> #{{ $loan->id }}</p>
                        <p><span class="font-medium">Amount:</span> 
                            @if($loan->loan_type === 'crypto')
                                {{ $loan->crypto_currency }} {{ number_format($loan->amount, 8) }}
                            @else
                                ₦{{ number_format($loan->amount) }}
                            @endif
                        </p>
                        <p><span class="font-medium">Type:</span> {{ ucfirst($loan->loan_type) }}</p>
                        <p><span class="font-medium">Duration:</span> {{ $loan->duration }} months</p>
                        <p><span class="font-medium">Interest Rate:</span> {{ $loan->interest_rate }}%</p>
                        <p><span class="font-medium">Applied:</span> {{ $loan->created_at->format('M d, Y') }}</p>
                        <p><span class="font-medium">Due Date:</span> {{ $loan->due_date->format('M d, Y') }}</p>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Purpose</h3>
                    <p class="text-gray-700">{{ $loan->purpose ?? 'Not specified' }}</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Actions</h2>
            <div class="flex flex-wrap gap-4">
                @if($loan->status === 'active')
                    <a href="{{ route('repayments.index') }}" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700">
                        Make Repayment
                    </a>
                @endif
                
                <a href="{{ route('loans.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700">
                    Back to Loans
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 