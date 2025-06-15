@extends('layouts.app')

@section('title', 'Loan Analytics')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Loan Analytics</h1>
        <p class="text-gray-600 mt-2">View detailed loan statistics and trends</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        @if(isset($loanChart))
            {!! $loanChart->render() !!}
        @else
            <div class="text-center py-8">
                <p class="text-gray-500">No loan data available for analytics.</p>
            </div>
        @endif
    </div>
</div>
@endsection
