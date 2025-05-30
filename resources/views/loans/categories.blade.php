@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Available Loan Types</h5>
                </div>

                <div class="card-body">
                    <div class="row">
                        @forelse($categories as $category)
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 {{ $category->is_active ? '' : 'bg-light' }}">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $category->name }}</h5>
                                        <p class="card-text text-muted">{{ $category->description }}</p>
                                        
                                        <ul class="list-unstyled">
                                            <li><strong>Interest Rate:</strong> {{ $category->interest_rate }}%</li>
                                            <li><strong>Term:</strong> Up to {{ $category->max_term_months }} months</li>
                                            <li><strong>Amount:</strong> 
                                                {{ number_format($category->min_amount, 2) }} - 
                                                {{ number_format($category->max_amount, 2) }}
                                            </li>
                                            <li><strong>Processing Fee:</strong> {{ $category->processing_fee }}%</li>
                                        </ul>

                                        @if($category->is_active)
                                            <div class="mt-3">
                                                <a href="{{ route('loans.apply', ['category' => $category->id]) }}" 
                                                   class="btn btn-primary">
                                                    Apply Now
                                                </a>
                                                <a href="{{ route('loans.category.details', $category) }}" 
                                                   class="btn btn-outline-secondary">
                                                    More Details
                                                </a>
                                            </div>
                                        @else
                                            <div class="mt-3">
                                                <span class="badge bg-secondary">Currently Unavailable</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    No loan categories are available at the moment.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 