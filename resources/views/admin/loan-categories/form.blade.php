@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ isset($loanCategory) ? 'Edit Loan Category' : 'Create New Loan Category' }}</h5>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ isset($loanCategory) 
                        ? route('admin.loan-categories.update', $loanCategory) 
                        : route('admin.loan-categories.store') }}" 
                          method="POST">
                        @csrf
                        @if(isset($loanCategory))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Category Name</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $loanCategory->name ?? '') }}" 
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3">{{ old('description', $loanCategory->description ?? '') }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="min_amount" class="form-label">Minimum Amount</label>
                                            <input type="number" 
                                                   class="form-control @error('min_amount') is-invalid @enderror" 
                                                   id="min_amount" 
                                                   name="min_amount" 
                                                   step="0.01" 
                                                   value="{{ old('min_amount', $loanCategory->min_amount ?? '') }}" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="max_amount" class="form-label">Maximum Amount</label>
                                            <input type="number" 
                                                   class="form-control @error('max_amount') is-invalid @enderror" 
                                                   id="max_amount" 
                                                   name="max_amount" 
                                                   step="0.01" 
                                                   value="{{ old('max_amount', $loanCategory->max_amount ?? '') }}" 
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="interest_rate" class="form-label">Interest Rate (%)</label>
                                            <input type="number" 
                                                   class="form-control @error('interest_rate') is-invalid @enderror" 
                                                   id="interest_rate" 
                                                   name="interest_rate" 
                                                   step="0.01" 
                                                   value="{{ old('interest_rate', $loanCategory->interest_rate ?? '') }}" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="max_term_months" class="form-label">Maximum Term (Months)</label>
                                            <input type="number" 
                                                   class="form-control @error('max_term_months') is-invalid @enderror" 
                                                   id="max_term_months" 
                                                   name="max_term_months" 
                                                   value="{{ old('max_term_months', $loanCategory->max_term_months ?? '') }}" 
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="late_payment_fee" class="form-label">Late Payment Fee</label>
                                            <input type="number" 
                                                   class="form-control @error('late_payment_fee') is-invalid @enderror" 
                                                   id="late_payment_fee" 
                                                   name="late_payment_fee" 
                                                   step="0.01" 
                                                   value="{{ old('late_payment_fee', $loanCategory->late_payment_fee ?? '0') }}" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="processing_fee" class="form-label">Processing Fee (%)</label>
                                            <input type="number" 
                                                   class="form-control @error('processing_fee') is-invalid @enderror" 
                                                   id="processing_fee" 
                                                   name="processing_fee" 
                                                   step="0.01" 
                                                   value="{{ old('processing_fee', $loanCategory->processing_fee ?? '0') }}" 
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="requires_collateral" 
                                               name="requires_collateral" 
                                               value="1" 
                                               {{ old('requires_collateral', $loanCategory->requires_collateral ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_collateral">
                                            Requires Collateral
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1" 
                                               {{ old('is_active', $loanCategory->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active Category
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="required_documents" class="form-label">Required Documents</label>
                            <select class="form-select @error('required_documents') is-invalid @enderror" 
                                    id="required_documents" 
                                    name="required_documents[]" 
                                    multiple>
                                @php
                                    $documents = [
                                        'id_proof' => 'ID Proof',
                                        'address_proof' => 'Address Proof',
                                        'income_proof' => 'Income Proof',
                                        'bank_statement' => 'Bank Statement',
                                        'employment_letter' => 'Employment Letter',
                                        'collateral_documents' => 'Collateral Documents'
                                    ];
                                    $selectedDocs = old('required_documents', $loanCategory->required_documents ?? []);
                                @endphp
                                @foreach($documents as $value => $label)
                                    <option value="{{ $value }}" 
                                            {{ in_array($value, $selectedDocs) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Hold Ctrl/Cmd to select multiple documents</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.loan-categories.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                {{ isset($loanCategory) ? 'Update Category' : 'Create Category' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add min amount validation
        const minAmountInput = document.getElementById('min_amount');
        const maxAmountInput = document.getElementById('max_amount');

        maxAmountInput.addEventListener('input', function() {
            minAmountInput.max = this.value;
        });

        minAmountInput.addEventListener('input', function() {
            maxAmountInput.min = this.value;
        });
    });
</script>
@endpush 