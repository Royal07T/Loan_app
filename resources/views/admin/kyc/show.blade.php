@extends('layouts.app')

@section('title', 'KYC Verification Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">KYC Verification Details</h1>
                <p class="text-gray-600 mt-2">User: {{ $user->name }} ({{ $user->email }})</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.kyc.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Back to List
                </a>
                @if($user->kyc_status === 'pending')
                    <button onclick="approveKYC()" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Approve
                    </button>
                    <button onclick="rejectKYC()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Reject
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- User Information -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">User Information</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0 h-16 w-16">
                            <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-xl font-medium text-gray-700">
                                    {{ substr($user->name, 0, 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-medium text-gray-900">{{ $user->name }}</h4>
                            <p class="text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>

                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User ID</dt>
                            <dd class="text-sm text-gray-900">{{ $user->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Registration Date</dt>
                            <dd class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Login</dt>
                            <dd class="text-sm text-gray-900">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- KYC Status -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">KYC Status</h3>
                </div>
                <div class="p-6">
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-medium text-gray-900">Current Status</h4>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($kycStatus['status'] === 'verified') bg-green-100 text-green-800
                                @elseif($kycStatus['status'] === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($kycStatus['status'] === 'rejected') bg-red-100 text-red-800
                                @elseif($kycStatus['status'] === 'expired') bg-gray-100 text-gray-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($kycStatus['status']) }}
                            </span>
                        </div>

                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">KYC Provider</dt>
                                <dd class="text-sm text-gray-900">{{ $user->kyc_provider ? ucfirst(str_replace('_', ' ', $user->kyc_provider)) : 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Reference ID</dt>
                                <dd class="text-sm text-gray-900">{{ $user->kyc_reference ?: 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Submitted At</dt>
                                <dd class="text-sm text-gray-900">{{ $user->kyc_submitted_at ? $user->kyc_submitted_at->format('M d, Y H:i') : 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Verified At</dt>
                                <dd class="text-sm text-gray-900">{{ $user->kyc_verified_at ? $user->kyc_verified_at->format('M d, Y H:i') : 'N/A' }}</dd>
                            </div>
                            @if($kycStatus['expires_at'])
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Expires At</dt>
                                    <dd class="text-sm text-gray-900">{{ $kycStatus['expires_at']->format('M d, Y H:i') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    @if($kycStatus['status'] === 'verified' && $kycStatus['expires_at'])
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">KYC Expiration Warning</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>This KYC verification will expire on {{ $kycStatus['expires_at']->format('M d, Y H:i') }}.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- KYC Data -->
    @if(!empty($kycData))
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">KYC Data</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if(isset($kycData['personal_info']))
                            <div>
                                <h4 class="text-md font-medium text-gray-900 mb-3">Personal Information</h4>
                                <dl class="space-y-2">
                                    @foreach($kycData['personal_info'] as $key => $value)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                            <dd class="text-sm text-gray-900">{{ $value }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        @endif

                        @if(isset($kycData['document_info']))
                            <div>
                                <h4 class="text-md font-medium text-gray-900 mb-3">Document Information</h4>
                                <dl class="space-y-2">
                                    @foreach($kycData['document_info'] as $key => $value)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                            <dd class="text-sm text-gray-900">{{ $value }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        @endif

                        @if(isset($kycData['verification_results']))
                            <div class="md:col-span-2">
                                <h4 class="text-md font-medium text-gray-900 mb-3">Verification Results</h4>
                                <dl class="space-y-2">
                                    @foreach($kycData['verification_results'] as $key => $value)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                            <dd class="text-sm text-gray-900">
                                                @if(is_bool($value))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $value ? 'Yes' : 'No' }}
                                                    </span>
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Provider Details -->
    @if($providerDetails)
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Provider Details</h3>
                </div>
                <div class="p-6">
                    <pre class="bg-gray-50 p-4 rounded-md overflow-x-auto text-sm">{{ json_encode($providerDetails, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    @endif

    <!-- Admin Actions History -->
    @if(isset($kycData['admin_approval']) || isset($kycData['admin_rejection']))
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Admin Actions</h3>
                </div>
                <div class="p-6">
                    @if(isset($kycData['admin_approval']))
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                            <h4 class="text-md font-medium text-green-800 mb-2">Approval</h4>
                            <dl class="space-y-1">
                                <div>
                                    <dt class="text-sm font-medium text-green-700">Approved By</dt>
                                    <dd class="text-sm text-green-900">{{ $kycData['admin_approval']['approved_by'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-green-700">Approved At</dt>
                                    <dd class="text-sm text-green-900">{{ $kycData['admin_approval']['approved_at'] }}</dd>
                                </div>
                                @if(isset($kycData['admin_approval']['notes']))
                                    <div>
                                        <dt class="text-sm font-medium text-green-700">Notes</dt>
                                        <dd class="text-sm text-green-900">{{ $kycData['admin_approval']['notes'] }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    @if(isset($kycData['admin_rejection']))
                        <div class="p-4 bg-red-50 border border-red-200 rounded-md">
                            <h4 class="text-md font-medium text-red-800 mb-2">Rejection</h4>
                            <dl class="space-y-1">
                                <div>
                                    <dt class="text-sm font-medium text-red-700">Rejected By</dt>
                                    <dd class="text-sm text-red-900">{{ $kycData['admin_rejection']['rejected_by'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-red-700">Rejected At</dt>
                                    <dd class="text-sm text-red-900">{{ $kycData['admin_rejection']['rejected_at'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-red-700">Reason</dt>
                                    <dd class="text-sm text-red-900">{{ $kycData['admin_rejection']['reason'] }}</dd>
                                </div>
                                @if(isset($kycData['admin_rejection']['notes']))
                                    <div>
                                        <dt class="text-sm font-medium text-red-700">Notes</dt>
                                        <dd class="text-sm text-red-900">{{ $kycData['admin_rejection']['notes'] }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Approve KYC Verification</h3>
            <form id="approvalForm">
                <div class="mb-4">
                    <label for="approvalNotes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea id="approvalNotes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeApprovalModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                        Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject KYC Verification</h3>
            <form id="rejectionForm">
                <div class="mb-4">
                    <label for="rejectionReason" class="block text-sm font-medium text-gray-700 mb-2">Reason *</label>
                    <select id="rejectionReason" name="reason" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select a reason</option>
                        <option value="invalid_document">Invalid Document</option>
                        <option value="poor_quality">Poor Quality Image</option>
                        <option value="mismatch">Information Mismatch</option>
                        <option value="expired">Expired Document</option>
                        <option value="fraudulent">Suspected Fraud</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="rejectionNotes" class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                    <textarea id="rejectionNotes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectionModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approveKYC() {
    document.getElementById('approvalModal').classList.remove('hidden');
}

function rejectKYC() {
    document.getElementById('rejectionModal').classList.remove('hidden');
}

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
    document.getElementById('approvalForm').reset();
}

function closeRejectionModal() {
    document.getElementById('rejectionModal').classList.add('hidden');
    document.getElementById('rejectionForm').reset();
}

document.getElementById('approvalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`/admin/kyc/{{ $user->id }}/approve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            notes: formData.get('notes')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeApprovalModal();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
});

document.getElementById('rejectionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`/admin/kyc/{{ $user->id }}/reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            reason: formData.get('reason'),
            notes: formData.get('notes')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeRejectionModal();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
});
</script>
@endsection 