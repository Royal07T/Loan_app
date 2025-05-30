@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $category->name }}</h5>
                    <a href="{{ route('loans.categories') }}" class="btn btn-secondary">Back to Categories</a>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2">About This Loan</h6>
                                <p>{{ $category->description }}</p>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="border-bottom pb-2">Loan Details</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <strong>Amount Range:</strong><br>
                                            {{ number_format($category->min_amount, 2) }} - 
                                            {{ number_format($category->max_amount, 2) }}
                                        </li>
                                        <li class="mb-2">
                                            <strong>Interest Rate:</strong><br>
                                            {{ $category->interest_rate }}% per annum
                                        </li>
                                        <li class="mb-2">
                                            <strong>Maximum Term:</strong><br>
                                            {{ $category->max_term_months }} months
                                        </li>
                                        <li class="mb-2">
                                            <strong>Processing Fee:</strong><br>
                                            {{ $category->processing_fee }}% of loan amount
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="border-bottom pb-2">Additional Information</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <strong>Late Payment Fee:</strong><br>
                                            {{ number_format($category->late_payment_fee, 2) }}
                                        </li>
                                        <li class="mb-2">
                                            <strong>Collateral Required:</strong><br>
                                            {{ $category->requires_collateral ? 'Yes' : 'No' }}
                                        </li>
                                        <li class="mb-2">
                                            <strong>Required Documents:</strong><br>
                                            @if($category->required_documents)
                                                <ul class="list-unstyled ps-3">
                                                    @foreach($category->required_documents as $document)
                                                        <li>
                                                            <i class="bi bi-check2"></i>
                                                            {{ str_replace('_', ' ', Str::title($document)) }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">No specific documents required</span>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Loan Calculator</h6>
                                    <form id="loanCalculator">
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">Loan Amount</label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="amount" 
                                                   min="{{ $category->min_amount }}" 
                                                   max="{{ $category->max_amount }}" 
                                                   required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="term" class="form-label">Term (Months)</label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="term" 
                                                   min="1" 
                                                   max="{{ $category->max_term_months }}" 
                                                   required>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">Calculate</button>
                                    </form>

                                    <div id="calculationResults" class="mt-3 d-none">
                                        <h6 class="border-bottom pb-2">Estimated Payments</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong>Monthly Payment:</strong><br>
                                                <span id="monthlyPayment">-</span>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Processing Fee:</strong><br>
                                                <span id="processingFee">-</span>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Total Interest:</strong><br>
                                                <span id="totalInterest">-</span>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Total Repayment:</strong><br>
                                                <span id="totalRepayment">-</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            @if($category->is_active)
                                <div class="mt-3">
                                    <a href="{{ route('loans.apply', ['category' => $category->id]) }}" 
                                       class="btn btn-success w-100">
                                        Apply for This Loan
                                    </a>
                                </div>
                            @else
                                <div class="alert alert-warning mt-3">
                                    This loan category is currently unavailable.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calculator = document.getElementById('loanCalculator');
    const results = document.getElementById('calculationResults');
    
    calculator.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const amount = parseFloat(document.getElementById('amount').value);
        const term = parseInt(document.getElementById('term').value);
        const annualRate = {{ $category->interest_rate }};
        const monthlyRate = (annualRate / 100) / 12;
        const processingFeeRate = {{ $category->processing_fee }};
        
        // Calculate monthly payment using PMT formula
        const monthlyPayment = amount * 
            (monthlyRate * Math.pow(1 + monthlyRate, term)) / 
            (Math.pow(1 + monthlyRate, term) - 1);
            
        const totalRepayment = monthlyPayment * term;
        const totalInterest = totalRepayment - amount;
        const processingFee = amount * (processingFeeRate / 100);
        
        // Update results
        document.getElementById('monthlyPayment').textContent = 
            formatCurrency(monthlyPayment);
        document.getElementById('processingFee').textContent = 
            formatCurrency(processingFee);
        document.getElementById('totalInterest').textContent = 
            formatCurrency(totalInterest);
        document.getElementById('totalRepayment').textContent = 
            formatCurrency(totalRepayment);
            
        results.classList.remove('d-none');
    });
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }
});
</script>
@endpush 