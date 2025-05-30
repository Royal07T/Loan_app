@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $loanCategory->name }}</h5>
                    <div>
                        <a href="{{ route('admin.loan-categories.edit', $loanCategory) }}" 
                           class="btn btn-warning">
                            Edit Category
                        </a>
                        <a href="{{ route('admin.loan-categories.index') }}" 
                           class="btn btn-secondary">
                            Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Basic Information</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-{{ $loanCategory->is_active ? 'success' : 'danger' }}">
                                        {{ $loanCategory->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Description</dt>
                                <dd class="col-sm-8">{{ $loanCategory->description ?? 'N/A' }}</dd>

                                <dt class="col-sm-4">Amount Range</dt>
                                <dd class="col-sm-8">
                                    {{ number_format($loanCategory->min_amount, 2) }} - 
                                    {{ number_format($loanCategory->max_amount, 2) }}
                                </dd>

                                <dt class="col-sm-4">Interest Rate</dt>
                                <dd class="col-sm-8">{{ $loanCategory->interest_rate }}%</dd>

                                <dt class="col-sm-4">Maximum Term</dt>
                                <dd class="col-sm-8">{{ $loanCategory->max_term_months }} months</dd>
                            </dl>
                        </div>

                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Fees and Requirements</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Processing Fee</dt>
                                <dd class="col-sm-8">{{ $loanCategory->processing_fee }}%</dd>

                                <dt class="col-sm-4">Late Payment Fee</dt>
                                <dd class="col-sm-8">{{ number_format($loanCategory->late_payment_fee, 2) }}</dd>

                                <dt class="col-sm-4">Collateral Required</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-{{ $loanCategory->requires_collateral ? 'info' : 'secondary' }}">
                                        {{ $loanCategory->requires_collateral ? 'Yes' : 'No' }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Required Documents</dt>
                                <dd class="col-sm-8">
                                    @if($loanCategory->required_documents)
                                        <ul class="list-unstyled mb-0">
                                            @foreach($loanCategory->required_documents as $document)
                                                <li>
                                                    <i class="bi bi-check2"></i>
                                                    {{ str_replace('_', ' ', Str::title($document)) }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">No documents required</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="border-bottom pb-2">Recent Loans</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Borrower</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loanCategory->loans as $loan)
                                        <tr>
                                            <td>{{ $loan->id }}</td>
                                            <td>{{ $loan->user->name }}</td>
                                            <td>{{ number_format($loan->amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $loan->status === 'paid' ? 'success' : 'warning' }}">
                                                    {{ Str::title($loan->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $loan->due_date->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('admin.loans.show', $loan) }}" 
                                                   class="btn btn-sm btn-info">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No loans found in this category.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 