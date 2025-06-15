@extends('layouts.app')

@section('title', 'My Documents')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">📄 My Documents</h1>
        <p class="text-gray-600 mt-2">Manage your uploaded documents and track their status</p>
    </div>

    <!-- Upload Document Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Upload New Document</h2>
        <form id="documentUploadForm" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="document_type" class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                    <select id="document_type" name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select document type</option>
                        <option value="identity">Identity Document (ID Card/Passport)</option>
                        <option value="proof_of_address">Proof of Address</option>
                        <option value="bank_statement">Bank Statement</option>
                        <option value="payslip">Payslip</option>
                        <option value="tax_document">Tax Document</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label for="document_file" class="block text-sm font-medium text-gray-700 mb-2">Document File</label>
                    <input type="file" id="document_file" name="document" accept=".jpg,.jpeg,.png,.pdf" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Accepted formats: JPG, PNG, PDF (Max 5MB)</p>
                </div>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium">
                Upload Document
            </button>
        </form>
    </div>

    <!-- Documents List -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Uploaded Documents</h2>
        </div>
        <div class="p-6">
            @if($documents->count() > 0)
                <div class="space-y-4">
                    @foreach($documents as $document)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full 
                                    @if($document->status === 'approved') bg-green-100 text-green-600
                                    @elseif($document->status === 'rejected') bg-red-100 text-red-600
                                    @elseif($document->status === 'pending') bg-yellow-100 text-yellow-600
                                    @else bg-gray-100 text-gray-600
                                    @endif">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $document->type)) }}</p>
                                    <p class="text-sm text-gray-500">{{ $document->original_name }}</p>
                                    <p class="text-xs text-gray-400">Uploaded: {{ $document->created_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($document->status === 'approved') bg-green-100 text-green-800
                                    @elseif($document->status === 'rejected') bg-red-100 text-red-800
                                    @elseif($document->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($document->status) }}
                                </span>
                                <a href="{{ route('documents.download', $document) }}" class="text-blue-600 hover:text-blue-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No documents uploaded</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by uploading your first document above.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.getElementById('documentUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("documents.upload") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Document uploaded successfully!');
            location.reload();
        } else {
            alert('Upload failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Upload failed. Please try again.');
    });
});
</script>
@endsection 