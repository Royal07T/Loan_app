@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">🪙 Wallet Info</h2>
    <div class="card">
        <div class="card-body">
            <p><strong>Crypto Balance (ETH):</strong> {{ number_format($balanceInEth, 6) }} ETH</p>
            <p><strong>Fiat Balance (NGN):</strong> ₦{{ number_format($fiatBalance, 2) }}</p>
        </div>
    </div>
</div>
@endsection
