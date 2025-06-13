@extends('layouts.app')

@section('title', 'KYC Vue Components Demo')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">KYC Vue Components Demo</h1>
        <p class="text-gray-600 mt-2">Interactive demonstration of Vue.js components for KYC verification</p>
    </div>

    <!-- Component Showcase -->
    <div class="space-y-8">
        <!-- KYC Status Card -->
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">1. KYC Status Card</h2>
            <kyc-status-card 
                :initial-status="'{{ Auth::user()->kyc_status ?? 'not_started' }}'"
                :initial-data='@json(Auth::user()->kyc_data ?? [])'
                @start-kyc="showKYCForm = true"
                @resubmit-kyc="showKYCForm = true"
                @status-changed="handleStatusChange"
            ></kyc-status-card>
        </div>

        <!-- KYC Provider Selector -->
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">2. KYC Provider Selector</h2>
            <kyc-provider-selector 
                @provider-selected="handleProviderSelected"
                @proceed="handleProviderProceed"
            ></kyc-provider-selector>
        </div>

        <!-- KYC Progress -->
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">3. KYC Progress Tracker</h2>
            <kyc-progress 
                :initial-status="'pending'"
                :initial-progress="25"
                @retry-verification="handleRetry"
                @cancel-verification="handleCancel"
                @view-results="handleViewResults"
            ></kyc-progress>
        </div>

        <!-- KYC Form -->
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">4. KYC Form (Modal)</h2>
            <button 
                @click="showKYCForm = true"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
                Open KYC Form
            </button>
        </div>
    </div>

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

    <!-- Component Controls -->
    <div class="mt-12 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Component Controls</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <button 
                @click="simulateStatusChange('pending')"
                class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700"
            >
                Set Status: Pending
            </button>
            <button 
                @click="simulateStatusChange('verified')"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
            >
                Set Status: Verified
            </button>
            <button 
                @click="simulateStatusChange('rejected')"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
            >
                Set Status: Rejected
            </button>
            <button 
                @click="simulateStatusChange('not_started')"
                class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
            >
                Set Status: Not Started
            </button>
        </div>
    </div>

    <!-- Event Log -->
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Event Log</h3>
        <div class="bg-gray-50 rounded-md p-4 h-64 overflow-y-auto">
            <div v-for="(event, index) in eventLog" :key="index" class="text-sm text-gray-700 mb-2">
                <span class="text-gray-500">{{ event.timestamp }}</span> - {{ event.message }}
            </div>
        </div>
        <button 
            @click="clearEventLog"
            class="mt-4 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
        >
            Clear Log
        </button>
    </div>
</div>

<script>
// Demo app data
window.demoAppData = {
    showKYCForm: false,
    eventLog: []
};

// Global functions for Vue components
window.handleStatusChange = function(status) {
    addEventLog(`KYC status changed to: ${status}`);
};

window.handleProviderSelected = function(providerId) {
    addEventLog(`Provider selected: ${providerId}`);
};

window.handleProviderProceed = function(providerId) {
    addEventLog(`Proceeding with provider: ${providerId}`);
    window.demoAppData.showKYCForm = true;
};

window.handleVerificationStarted = function(data) {
    addEventLog(`Verification started: ${JSON.stringify(data)}`);
    window.demoAppData.showKYCForm = false;
};

window.handleRetry = function() {
    addEventLog('Retry verification requested');
};

window.handleCancel = function() {
    addEventLog('Verification cancelled');
};

window.handleViewResults = function() {
    addEventLog('View results requested');
};

window.simulateStatusChange = function(status) {
    addEventLog(`Simulating status change to: ${status}`);
    // In a real app, this would update the backend
};

window.addEventLog = function(message) {
    const timestamp = new Date().toLocaleTimeString();
    window.demoAppData.eventLog.unshift({
        timestamp: timestamp,
        message: message
    });
    
    // Keep only last 50 events
    if (window.demoAppData.eventLog.length > 50) {
        window.demoAppData.eventLog = window.demoAppData.eventLog.slice(0, 50);
    }
};

window.clearEventLog = function() {
    window.demoAppData.eventLog = [];
};

// Initialize with some demo events
document.addEventListener('DOMContentLoaded', function() {
    addEventLog('Vue components demo loaded');
    addEventLog('All components are interactive and functional');
});
</script>
@endsection 