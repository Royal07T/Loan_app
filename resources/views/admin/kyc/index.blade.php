@extends('layouts.app')

@section('title', 'KYC Verifications')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">KYC Verifications</h1>
        <p class="text-gray-600 mt-2">Manage all KYC verification requests</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Filters</h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('admin.kyc.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="provider" class="block text-sm font-medium text-gray-700 mb-2">Provider</label>
                    <select id="provider" name="provider" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Providers</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider }}" {{ request('provider') == $provider ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $provider)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                           placeholder="Search by name, email, or reference" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2 flex items-end space-x-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.kyc.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Verifications</h3>
                <div class="flex items-center space-x-3">
                    <select id="bulkAction" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Bulk Actions</option>
                        <option value="approve">Approve Selected</option>
                        <option value="reject">Reject Selected</option>
                        <option value="reset">Reset Selected</option>
                    </select>
                    <button onclick="applyBulkAction()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Apply
                    </button>
                </div>
            </div>
        </div>

        <!-- Verifications Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Provider
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reference
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Submitted
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($verifications as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="user-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $user->id }}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ substr($user->name, 0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($user->kyc_status === 'verified') bg-green-100 text-green-800
                                    @elseif($user->kyc_status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($user->kyc_status === 'rejected') bg-red-100 text-red-800
                                    @elseif($user->kyc_status === 'expired') bg-gray-100 text-gray-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($user->kyc_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $user->kyc_provider ? ucfirst(str_replace('_', ' ', $user->kyc_provider)) : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $user->kyc_reference ?: 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->kyc_submitted_at ? $user->kyc_submitted_at->format('M d, Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.kyc.show', $user) }}" class="text-blue-600 hover:text-blue-900">
                                        View
                                    </a>
                                    @if($user->kyc_status === 'pending')
                                        <button onclick="approveKYC({{ $user->id }})" class="text-green-600 hover:text-green-900">
                                            Approve
                                        </button>
                                        <button onclick="rejectKYC({{ $user->id }})" class="text-red-600 hover:text-red-900">
                                            Reject
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No verifications found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($verifications->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $verifications->links() }}
            </div>
        @endif
    </div>
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
let currentUserId = null;

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

function approveKYC(userId) {
    currentUserId = userId;
    document.getElementById('approvalModal').classList.remove('hidden');
}

function rejectKYC(userId) {
    currentUserId = userId;
    document.getElementById('rejectionModal').classList.remove('hidden');
}

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
    document.getElementById('approvalForm').reset();
    currentUserId = null;
}

function closeRejectionModal() {
    document.getElementById('rejectionModal').classList.add('hidden');
    document.getElementById('rejectionForm').reset();
    currentUserId = null;
}

function applyBulkAction() {
    const action = document.getElementById('bulkAction').value;
    const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
    
    if (!action) {
        alert('Please select an action');
        return;
    }
    
    if (selectedUsers.length === 0) {
        alert('Please select at least one user');
        return;
    }
    
    if (confirm(`Are you sure you want to ${action} ${selectedUsers.length} verification(s)?`)) {
        fetch('/admin/kyc/bulk-action', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: action,
                user_ids: selectedUsers
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

document.getElementById('approvalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`/admin/kyc/${currentUserId}/approve`, {
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
    
    fetch(`/admin/kyc/${currentUserId}/reject`, {
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