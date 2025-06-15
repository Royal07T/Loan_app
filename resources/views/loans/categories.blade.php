@extends('layouts.app')

@section('title', 'Loan Categories - FinTech Pro')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full mb-6 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Choose Your Loan</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">Explore our flexible loan options designed to meet your financial needs. Select the perfect loan category that fits your requirements.</p>
        </div>

        <!-- Loan Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($categories as $category)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden card-hover {{ $category->is_active ? '' : 'opacity-75' }}">
                    <!-- Category Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold">{{ $category->name }}</h3>
                            @if(!$category->is_active)
                                <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm font-medium backdrop-blur-sm">
                                    Unavailable
                                </span>
                            @endif
                        </div>
                        <p class="text-blue-100 text-sm">{{ $category->description }}</p>
                    </div>

                    <!-- Category Details -->
                    <div class="p-6">
                        <div class="space-y-4 mb-6">
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600 font-medium">Interest Rate</span>
                                <span class="text-lg font-bold text-green-600">{{ $category->interest_rate }}%</span>
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600 font-medium">Max Duration</span>
                                <span class="text-lg font-semibold text-gray-900">{{ $category->max_term_months }} months</span>
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600 font-medium">Amount Range</span>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500">₦{{ number_format($category->min_amount) }}</div>
                                    <div class="text-lg font-bold text-gray-900">₦{{ number_format($category->max_amount) }}</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-gray-600 font-medium">Processing Fee</span>
                                <span class="text-lg font-semibold text-orange-600">{{ $category->processing_fee }}%</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        @if($category->is_active)
                            <div class="space-y-3">
                                <a href="{{ route('loans.apply', ['category' => $category->id]) }}" 
                                   class="w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Apply Now
                                </a>
                                <a href="{{ route('loans.category.details', $category) }}" 
                                   class="w-full inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 font-medium">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    More Details
                                </a>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500 text-sm">This loan category is currently unavailable</p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
                        <div class="w-24 h-24 bg-gradient-to-r from-gray-200 to-gray-300 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">No Loan Categories Available</h3>
                        <p class="text-gray-600 mb-8 max-w-md mx-auto">We're currently updating our loan offerings. Please check back later for available loan categories.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Why Choose Us Section -->
        <div class="mt-16 bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Choose FinTech Pro?</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">We provide fast, secure, and flexible loan solutions with competitive rates and excellent customer service.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-600 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Fast Processing</h3>
                    <p class="text-gray-600 text-sm">Quick approval and disbursement within 24-48 hours</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Secure & Safe</h3>
                    <p class="text-gray-600 text-sm">Bank-level security and encryption for your data</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Competitive Rates</h3>
                    <p class="text-gray-600 text-sm">Low interest rates and transparent fee structure</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">24/7 Support</h3>
                    <p class="text-gray-600 text-sm">Round-the-clock customer support and assistance</p>
                </div>
            </div>
        </div>

        <!-- Application Process -->
        <div class="mt-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 text-white">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-4">Simple Application Process</h2>
                <p class="text-xl text-blue-100 max-w-2xl mx-auto">Get your loan in just a few simple steps</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                        <span class="text-2xl font-bold">1</span>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Choose Category</h3>
                    <p class="text-blue-100 text-sm">Select the loan category that fits your needs</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                        <span class="text-2xl font-bold">2</span>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Fill Application</h3>
                    <p class="text-blue-100 text-sm">Complete the simple application form</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                        <span class="text-2xl font-bold">3</span>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Get Approved</h3>
                    <p class="text-blue-100 text-sm">Quick review and approval process</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                        <span class="text-2xl font-bold">4</span>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Receive Funds</h3>
                    <p class="text-blue-100 text-sm">Get your money within 24-48 hours</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 