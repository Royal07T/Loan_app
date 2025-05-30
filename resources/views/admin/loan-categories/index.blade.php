@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Loan Categories</h5>
                    <a href="{{ route('admin.loan-categories.create') }}" class="btn btn-primary">
                        Add New Category
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Interest Rate</th>
                                    <th>Term (Months)</th>
                                    <th>Amount Range</th>
                                    <th>Active Loans</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->interest_rate }}%</td>
                                        <td>{{ $category->max_term_months }}</td>
                                        <td>
                                            {{ number_format($category->min_amount, 2) }} - 
                                            {{ number_format($category->max_amount, 2) }}
                                        </td>
                                        <td>{{ $category->loans_count }}</td>
                                        <td>
                                            <span class="badge bg-{{ $category->is_active ? 'success' : 'danger' }}">
                                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.loan-categories.show', $category) }}" 
                                                   class="btn btn-info btn-sm">
                                                    View
                                                </a>
                                                <a href="{{ route('admin.loan-categories.edit', $category) }}" 
                                                   class="btn btn-warning btn-sm">
                                                    Edit
                                                </a>
                                                @if($category->loans_count == 0)
                                                    <form action="{{ route('admin.loan-categories.destroy', $category) }}" 
                                                          method="POST" 
                                                          class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-danger btn-sm" 
                                                                onclick="return confirm('Are you sure you want to delete this category?')">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No loan categories found.</td>
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
@endsection 