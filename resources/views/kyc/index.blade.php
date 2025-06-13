@extends('layouts.app')

@section('title', 'KYC Verification')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">KYC Verification</h1>
        <p class="text-gray-600 mt-2">Complete your identity verification to access loan services</p>
    </div>

    <!-- KYC Status Card -->
    <kyc-status-card 
        :initial-status="'{{ Auth::user()->kyc_status ?? 'not_started' }}'"
        :initial-data='@json(Auth::user()->kyc_data ?? [])'
        @start-kyc="showKYCForm = true"
        @resubmit-kyc="showKYCForm = true"
        @status-changed="handleStatusChange"
    ></kyc-status-card>

    <!-- KYC Form Modal -->
    <div v-if="showKYCForm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Complete KYC Verification</h3>
                <button 
                    @click="showKYCForm = false"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <kyc-form 
                @verification-started="handleVerificationStarted"
            ></kyc-form>
        </div>
    </div>

    <!-- KYC Progress Modal -->
    <div v-if="showKYCProgress" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Verification Progress</h3>
                <button 
                    @click="showKYCProgress = false"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <kyc-progress 
                :initial-status="kycProgressStatus"
                :initial-progress="kycProgressPercentage"
                @retry-verification="retryVerification"
                @cancel-verification="cancelVerification"
                @view-results="viewResults"
            ></kyc-progress>
        </div>
    </div>

    <!-- Provider Selection Modal -->
    <div v-if="showProviderSelection" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-6xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Select KYC Provider</h3>
                <button 
                    @click="showProviderSelection = false"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <kyc-provider-selector 
                @provider-selected="handleProviderSelected"
                @proceed="proceedWithProvider"
            ></kyc-provider-selector>
        </div>
    </div>

    <!-- Information Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-blue-50 rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="font-semibold text-gray-900">Secure & Private</h4>
                    <p class="text-sm text-gray-600 mt-1">Your data is encrypted and protected</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="font-semibold text-gray-900">Quick Process</h4>
                    <p class="text-sm text-gray-600 mt-1">Usually completed within 5 minutes</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="font-semibold text-gray-900">Global Coverage</h4>
                    <p class="text-sm text-gray-600 mt-1">Supports 150+ countries worldwide</p>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Frequently Asked Questions</h2>
        <div class="space-y-4">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">What documents do I need?</h3>
                <p class="text-gray-600">You'll need a valid government-issued ID (passport, national ID, or driver's license) and may be asked to take a selfie for face verification.</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">How long does verification take?</h3>
                <p class="text-gray-600">Most verifications are completed within 2-5 minutes. In some cases, manual review may take up to 24 hours.</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Is my data secure?</h3>
                <p class="text-gray-600">Yes, we use bank-level encryption and security measures. Your data is only used for verification purposes and is never shared with third parties.</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">What if my verification fails?</h3>
                <p class="text-gray-600">If your verification fails, you'll receive detailed feedback on what went wrong and can resubmit with corrected information.</p>
            </div>
        </div>
    </div>
</div>

<script>
// Vue app data
window.kycAppData = {
    showKYCForm: false,
    showKYCProgress: false,
    showProviderSelection: false,
    kycProgressStatus: 'pending',
    kycProgressPercentage: 0,
    selectedProvider: null
};

// Global functions for Vue components
window.handleStatusChange = function(status) {
    console.log('KYC status changed:', status);
    if (status === 'verified') {
        // Redirect to dashboard or show success message
        window.location.href = '/home';
    }
};

window.handleVerificationStarted = function(data) {
    window.kycAppData.showKYCForm = false;
    window.kycAppData.showKYCProgress = true;
    window.kycAppData.kycProgressStatus = 'pending';
    window.kycAppData.kycProgressPercentage = 0;
    
    // Start progress simulation
    const progressInterval = setInterval(() => {
        if (window.kycAppData.kycProgressPercentage < 90) {
            window.kycAppData.kycProgressPercentage += Math.random() * 10;
        } else {
            clearInterval(progressInterval);
        }
    }, 2000);
};

window.retryVerification = function() {
    window.kycAppData.showKYCProgress = false;
    window.kycAppData.showKYCForm = true;
};

window.cancelVerification = function() {
    window.kycAppData.showKYCProgress = false;
    // Show confirmation or redirect
};

window.viewResults = function() {
    window.kycAppData.showKYCProgress = false;
    window.location.href = '/home';
};

window.handleProviderSelected = function(providerId) {
    window.kycAppData.selectedProvider = providerId;
};

window.proceedWithProvider = function(providerId) {
    window.kycAppData.showProviderSelection = false;
    window.kycAppData.showKYCForm = true;
    // Pass selected provider to form
};
</script>
@endsection 