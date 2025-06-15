@extends('layouts.app')

@section('title', 'Apply for Loan')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">💰 Apply for Loan</h1>
        <p class="text-gray-600 mt-2">Complete the form below to submit your loan application</p>
    </div>

    <div class="max-w-4xl mx-auto">
        <!-- Loan Categories Info -->
        @if(isset($category))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-blue-900 mb-2">{{ $category->name }}</h2>
                <p class="text-blue-700 mb-4">{{ $category->description }}</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-blue-900">Interest Rate:</span>
                        <span class="text-blue-700">{{ $category->interest_rate }}%</span>
                    </div>
                    <div>
                        <span class="font-medium text-blue-900">Max Amount:</span>
                        <span class="text-blue-700">₦{{ number_format($category->max_amount) }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-blue-900">Max Duration:</span>
                        <span class="text-blue-700">{{ $category->max_duration }} months</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Loan Application Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Loan Application Details</h2>
            
            <form method="POST" action="{{ route('loans.store') }}" class="space-y-6">
                @csrf
                
                <!-- Loan Category Selection -->
                <div>
                    <label for="loan_category_id" class="block text-sm font-medium text-gray-700 mb-2">Loan Category</label>
                    <select id="loan_category_id" name="loan_category_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Loan Amount (NGN)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">₦</span>
                        <input type="number" 
                               id="amount" 
                               name="amount" 
                               value="{{ old('amount') }}"
                               min="5000" 
                               max="1000000" 
                               step="1000"
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter loan amount">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Minimum: ₦5,000 | Maximum: ₦1,000,000</p>
                    @error('amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Loan Duration -->
                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">Loan Duration (Months)</label>
                    <select id="duration" name="duration" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Loan Type</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="loan_type" value="fiat" {{ old('loan_type') == 'fiat' ? 'checked' : '' }} class="mr-2">
                            <span class="text-sm text-gray-700">Fiat (NGN)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="loan_type" value="crypto" {{ old('loan_type') == 'crypto' ? 'checked' : '' }} class="mr-2">
                            <span class="text-sm text-gray-700">Cryptocurrency</span>
                        </label>
                    </div>
                    @error('loan_type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Crypto Currency Selection (conditional) -->
                <div id="crypto_currency_section" class="hidden">
                    <label for="crypto_currency" class="block text-sm font-medium text-gray-700 mb-2">Cryptocurrency</label>
                    <select id="crypto_currency" name="crypto_currency" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                <div>
                    <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">Loan Purpose</label>
                    <textarea id="purpose" 
                              name="purpose" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Please describe what you plan to use this loan for">{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Terms and Conditions -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="flex items-start">
                        <input type="checkbox" name="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }} class="mr-2 mt-1">
                        <span class="text-sm text-gray-700">
                            I agree to the <a href="#" class="text-blue-600 hover:text-blue-800">Terms and Conditions</a> and 
                            <a href="#" class="text-blue-600 hover:text-blue-800">Privacy Policy</a>. I understand that this is a loan application 
                            and approval is subject to verification and credit assessment.
                        </span>
                    </label>
                    @error('terms_accepted')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('loans.categories') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>

        <!-- Application Tips -->
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-yellow-900 mb-2">💡 Application Tips</h3>
            <ul class="text-sm text-yellow-800 space-y-1">
                <li>• Ensure all your documents are up to date before applying</li>
                <li>• Provide accurate information to speed up the approval process</li>
                <li>• Keep your KYC verification current</li>
                <li>• Applications are typically processed within 24-48 hours</li>
            </ul>
        </div>
    </div>
</div>

<script>
// Show/hide crypto currency selection based on loan type
document.querySelectorAll('input[name="loan_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
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
    }
});
</script>
@endsection 