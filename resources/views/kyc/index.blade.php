<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Verification - Loan Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <h1 class="text-2xl font-bold text-gray-900">KYC Verification</h1>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </header>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- KYC Status Card -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Verification Status</h2>
                        <p class="text-gray-600 mt-1">Complete your identity verification to access loan services</p>
                    </div>
                    <div class="text-right">
                        <div id="kyc-status" class="text-lg font-medium">
                            <!-- Status will be loaded here -->
                        </div>
                        <div id="kyc-attempts" class="text-sm text-gray-500">
                            <!-- Attempts will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- KYC Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Start KYC Verification</h3>
                
                <form id="kyc-form" class="space-y-6">
                    @csrf
                    
                    <!-- Country Selection -->
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                            Country of Residence <span class="text-red-500">*</span>
                        </label>
                        <select id="country" name="country" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select your country</option>
                            <option value="NG">Nigeria</option>
                            <option value="GH">Ghana</option>
                            <option value="KE">Kenya</option>
                            <option value="ZA">South Africa</option>
                            <option value="US">United States</option>
                            <option value="GB">United Kingdom</option>
                            <option value="CA">Canada</option>
                            <option value="AU">Australia</option>
                        </select>
                    </div>

                    <!-- Language Selection -->
                    <div>
                        <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                            Preferred Language
                        </label>
                        <select id="language" name="language" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="EN">English</option>
                            <option value="FR">French</option>
                            <option value="ES">Spanish</option>
                            <option value="AR">Arabic</option>
                        </select>
                    </div>

                    <!-- Verification Mode -->
                    <div>
                        <label for="verification_mode" class="block text-sm font-medium text-gray-700 mb-2">
                            Verification Type
                        </label>
                        <select id="verification_mode" name="verification_mode" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="any">Complete Verification (Recommended)</option>
                            <option value="document">Document Only</option>
                            <option value="face">Face Verification Only</option>
                            <option value="background">Background Check Only</option>
                        </select>
                    </div>

                    <!-- Supported Document Types -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Supported Documents
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="id_card" name="supported_types[]" value="id_card" checked
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="id_card" class="ml-2 text-sm text-gray-700">National ID Card</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="passport" name="supported_types[]" value="passport" checked
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="passport" class="ml-2 text-sm text-gray-700">Passport</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="driving_license" name="supported_types[]" value="driving_license"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="driving_license" class="ml-2 text-sm text-gray-700">Driving License</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="utility_bill" name="supported_types[]" value="utility_bill"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="utility_bill" class="ml-2 text-sm text-gray-700">Utility Bill</label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" id="submit-btn"
                                class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Start Verification
                        </button>
                    </div>
                </form>
            </div>

            <!-- Information Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div class="bg-blue-50 rounded-lg p-6">
                    <div class="flex items-center">
                        <i class="fas fa-shield-alt text-blue-600 text-2xl mr-3"></i>
                        <div>
                            <h4 class="font-semibold text-gray-900">Secure & Private</h4>
                            <p class="text-sm text-gray-600 mt-1">Your data is encrypted and protected</p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 rounded-lg p-6">
                    <div class="flex items-center">
                        <i class="fas fa-clock text-green-600 text-2xl mr-3"></i>
                        <div>
                            <h4 class="font-semibold text-gray-900">Quick Process</h4>
                            <p class="text-sm text-gray-600 mt-1">Usually completed within 5 minutes</p>
                        </div>
                    </div>
                </div>

                <div class="bg-purple-50 rounded-lg p-6">
                    <div class="flex items-center">
                        <i class="fas fa-globe text-purple-600 text-2xl mr-3"></i>
                        <div>
                            <h4 class="font-semibold text-gray-900">Global Coverage</h4>
                            <p class="text-sm text-gray-600 mt-1">Supports 150+ countries worldwide</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loading-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-sm mx-4">
            <div class="flex items-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Processing...</h3>
                    <p class="text-sm text-gray-600">Initializing KYC verification</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load KYC status on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadKYCStatus();
        });

        // Handle form submission
        document.getElementById('kyc-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('submit-btn');
            const loadingModal = document.getElementById('loading-modal');

            // Show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            loadingModal.classList.remove('hidden');
            loadingModal.classList.add('flex');

            fetch('{{ route("kyc.initialize") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to verification URL if provided
                    if (data.data.verification_url) {
                        window.location.href = data.data.verification_url;
                    } else {
                        alert('KYC verification initiated successfully! Check your status.');
                        loadKYCStatus();
                    }
                } else {
                    alert('Error: ' + (data.message || 'Failed to initialize KYC verification'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            })
            .finally(() => {
                // Hide loading
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-shield-alt mr-2"></i>Start Verification';
                loadingModal.classList.add('hidden');
                loadingModal.classList.remove('flex');
            });
        });

        function loadKYCStatus() {
            fetch('{{ route("kyc.info") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const statusElement = document.getElementById('kyc-status');
                    const attemptsElement = document.getElementById('kyc-attempts');
                    const submitBtn = document.getElementById('submit-btn');
                    const form = document.getElementById('kyc-form');

                    const status = data.data.kyc_status;
                    const attempts = data.data.kyc_attempts;
                    const maxAttempts = data.data.max_attempts;
                    const canRetry = data.data.can_retry;

                    // Update status display
                    let statusText = '';
                    let statusColor = '';
                    
                    switch (status) {
                        case 'verified':
                            statusText = 'Verified';
                            statusColor = 'text-green-600';
                            break;
                        case 'pending':
                            statusText = 'Pending';
                            statusColor = 'text-yellow-600';
                            break;
                        case 'rejected':
                            statusText = 'Rejected';
                            statusColor = 'text-red-600';
                            break;
                        case 'expired':
                            statusText = 'Expired';
                            statusColor = 'text-orange-600';
                            break;
                        default:
                            statusText = 'Not Started';
                            statusColor = 'text-gray-600';
                    }

                    statusElement.innerHTML = `<span class="${statusColor}">${statusText}</span>`;
                    attemptsElement.textContent = `Attempts: ${attempts}/${maxAttempts}`;

                    // Disable form if user can't retry
                    if (!canRetry && status !== 'not_started') {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-ban mr-2"></i>Max Attempts Reached';
                        form.style.opacity = '0.5';
                    } else if (status === 'pending') {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-clock mr-2"></i>Verification in Progress';
                    }
                }
            })
            .catch(error => {
                console.error('Error loading KYC status:', error);
            });
        }
    </script>
</body>
</html> 