@extends('layouts.app')

@section('title', 'Apply for Loan - FinTech Pro')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full mb-6 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Apply for Your Loan</h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">Complete the form below to submit your loan application. Our team will review it within 24-48 hours.</p>
        </div>

        <!-- Loan Categories Info -->
        @if(isset($category))
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 mb-8 text-white shadow-xl">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">{{ $category->name }}</h2>
                        <p class="text-blue-100">{{ $category->description }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                        <div class="text-blue-100 text-sm font-medium mb-1">Interest Rate</div>
                        <div class="text-2xl font-bold">{{ $category->interest_rate }}%</div>
                    </div>
                    <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                        <div class="text-blue-100 text-sm font-medium mb-1">Max Amount</div>
                        <div class="text-2xl font-bold">₦{{ number_format($category->max_amount) }}</div>
                    </div>
                    <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                        <div class="text-blue-100 text-sm font-medium mb-1">Max Duration</div>
                        <div class="text-2xl font-bold">{{ $category->max_duration }} months</div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Loan Application Form -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">Application Details</h2>
                <p class="text-gray-600 mt-1">Please fill in all required fields to complete your application</p>
            </div>
            
            <form method="POST" action="{{ route('loans.store') }}" class="p-8 space-y-8">
                @csrf
                
                <!-- Loan Category Selection -->
                <div class="space-y-2">
                    <label for="loan_category_id" class="block text-sm font-semibold text-gray-700">
                        Loan Category <span class="text-red-500">*</span>
                    </label>
                    <select id="loan_category_id" name="loan_category_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white">
                        <option value="">Select a loan category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" 
                                {{ (old('loan_category_id') == $cat->id || (isset($category) && $category->id == $cat->id)) ? 'selected' : '' }}>
                                {{ $cat->name }} - {{ $cat->interest_rate }}% interest
                            </option>
                        @endforeach
                    </select>
                    @error('loan_category_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Loan Amount -->
                <div class="space-y-2">
                    <label for="amount" class="block text-sm font-semibold text-gray-700">
                        Loan Amount (NGN) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500 text-lg">₦</span>
                        <input type="number" 
                               id="amount" 
                               name="amount" 
                               value="{{ old('amount') }}"
                               min="5000" 
                               max="1000000" 
                               step="1000"
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Enter loan amount">
                    </div>
                    <p class="text-sm text-gray-500">Minimum: ₦5,000 | Maximum: ₦1,000,000</p>
                    @error('amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Loan Duration -->
                <div class="space-y-2">
                    <label for="duration" class="block text-sm font-semibold text-gray-700">
                        Loan Duration (Months) <span class="text-red-500">*</span>
                    </label>
                    <select id="duration" name="duration" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white">
                        <option value="">Select duration</option>
                        @for($i = 1; $i <= 36; $i++)
                            <option value="{{ $i }}" {{ old('duration') == $i ? 'selected' : '' }}>
                                {{ $i }} {{ $i == 1 ? 'month' : 'months' }}
                            </option>
                        @endfor
                    </select>
                    @error('duration')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Loan Type -->
                <div class="space-y-4">
                    <label class="block text-sm font-semibold text-gray-700">
                        Loan Type <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="loan_type" value="fiat" {{ old('loan_type') == 'fiat' ? 'checked' : '' }} class="sr-only">
                            <div class="border-2 border-gray-200 rounded-xl p-4 hover:border-blue-300 transition-all duration-200 loan-type-option">
                                <div class="flex items-center">
                                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-3 flex items-center justify-center">
                                        <div class="w-2.5 h-2.5 bg-blue-600 rounded-full hidden radio-dot"></div>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Fiat (NGN)</div>
                                        <div class="text-sm text-gray-500">Traditional Nigerian Naira</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="loan_type" value="crypto" {{ old('loan_type') == 'crypto' ? 'checked' : '' }} class="sr-only">
                            <div class="border-2 border-gray-200 rounded-xl p-4 hover:border-purple-300 transition-all duration-200 loan-type-option">
                                <div class="flex items-center">
                                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-3 flex items-center justify-center">
                                        <div class="w-2.5 h-2.5 bg-purple-600 rounded-full hidden radio-dot"></div>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Cryptocurrency</div>
                                        <div class="text-sm text-gray-500">BTC, ETH, USDT</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('loan_type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Crypto Currency Selection (conditional) -->
                <div id="crypto_currency_section" class="hidden space-y-2">
                    <label for="crypto_currency" class="block text-sm font-semibold text-gray-700">
                        Cryptocurrency <span class="text-red-500">*</span>
                    </label>
                    <select id="crypto_currency" name="crypto_currency" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 bg-white">
                        <option value="">Select cryptocurrency</option>
                        <option value="BTC" {{ old('crypto_currency') == 'BTC' ? 'selected' : '' }}>Bitcoin (BTC)</option>
                        <option value="ETH" {{ old('crypto_currency') == 'ETH' ? 'selected' : '' }}>Ethereum (ETH)</option>
                        <option value="USDT" {{ old('crypto_currency') == 'USDT' ? 'selected' : '' }}>Tether (USDT)</option>
                    </select>
                    @error('crypto_currency')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Purpose -->
                <div class="space-y-2">
                    <label for="purpose" class="block text-sm font-semibold text-gray-700">
                        Loan Purpose <span class="text-red-500">*</span>
                    </label>
                    <textarea id="purpose" 
                              name="purpose" 
                              rows="4"
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none"
                              placeholder="Please describe what you plan to use this loan for...">{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Terms and Conditions -->
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6 border border-blue-200">
                    <label class="flex items-start cursor-pointer">
                        <input type="checkbox" name="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }} class="mt-1 mr-3 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700">
                            I agree to the <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Terms and Conditions</a> and 
                            <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Privacy Policy</a>. I understand that this is a loan application 
                            and approval is subject to verification and credit assessment.
                        </span>
                    </label>
                    @error('terms_accepted')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex flex-col sm:flex-row justify-end space-y-4 sm:space-y-0 sm:space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('loans.categories') }}" class="px-8 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 font-medium text-center">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>

        <!-- Application Tips -->
        <div class="mt-12 bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-center mb-6">
                <div class="w-12 h-12 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Application Tips</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-1">Complete Documentation</h4>
                        <p class="text-sm text-gray-600">Ensure all your documents are up to date before applying</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-1">Accurate Information</h4>
                        <p class="text-sm text-gray-600">Provide accurate information to speed up the approval process</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-1">KYC Verification</h4>
                        <p class="text-sm text-gray-600">Keep your KYC verification current for faster processing</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-1">Quick Processing</h4>
                        <p class="text-sm text-gray-600">Applications are typically processed within 24-48 hours</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced loan type selection
document.querySelectorAll('input[name="loan_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Reset all options
        document.querySelectorAll('.loan-type-option').forEach(option => {
            option.classList.remove('border-blue-500', 'border-purple-500', 'bg-blue-50', 'bg-purple-50');
            option.classList.add('border-gray-200');
            option.querySelector('.radio-dot').classList.add('hidden');
        });
        
        // Style selected option
        const selectedOption = this.closest('label').querySelector('.loan-type-option');
        if (this.value === 'fiat') {
            selectedOption.classList.remove('border-gray-200');
            selectedOption.classList.add('border-blue-500', 'bg-blue-50');
            selectedOption.querySelector('.radio-dot').classList.remove('hidden');
        } else if (this.value === 'crypto') {
            selectedOption.classList.remove('border-gray-200');
            selectedOption.classList.add('border-purple-500', 'bg-purple-50');
            selectedOption.querySelector('.radio-dot').classList.remove('hidden');
        }
        
        // Show/hide crypto currency selection
        const cryptoSection = document.getElementById('crypto_currency_section');
        const cryptoSelect = document.getElementById('crypto_currency');
        
        if (this.value === 'crypto') {
            cryptoSection.classList.remove('hidden');
            cryptoSelect.setAttribute('required', 'required');
        } else {
            cryptoSection.classList.add('hidden');
            cryptoSelect.removeAttribute('required');
            cryptoSelect.value = '';
        }
    });
});

// Initialize crypto section visibility on page load
document.addEventListener('DOMContentLoaded', function() {
    const selectedLoanType = document.querySelector('input[name="loan_type"]:checked');
    if (selectedLoanType && selectedLoanType.value === 'crypto') {
        document.getElementById('crypto_currency_section').classList.remove('hidden');
        document.getElementById('crypto_currency').setAttribute('required', 'required');
        
        // Style the selected option
        const selectedOption = selectedLoanType.closest('label').querySelector('.loan-type-option');
        selectedOption.classList.remove('border-gray-200');
        selectedOption.classList.add('border-purple-500', 'bg-purple-50');
        selectedOption.querySelector('.radio-dot').classList.remove('hidden');
    } else if (selectedLoanType && selectedLoanType.value === 'fiat') {
        const selectedOption = selectedLoanType.closest('label').querySelector('.loan-type-option');
        selectedOption.classList.remove('border-gray-200');
        selectedOption.classList.add('border-blue-500', 'bg-blue-50');
        selectedOption.querySelector('.radio-dot').classList.remove('hidden');
    }
});
</script>
@endsection 