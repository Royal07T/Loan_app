@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4"> Transaction History</h2>

    @if(count($transactions))
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>To/From</th>
                    <th>Amount</th>
                    <th>TX Hash</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $tx)
                    <tr>
                        <td>{{ ucfirst($tx->type) }}</td>
                        <td>{{ $tx->counterparty }}</td>
                        <td>{{ $tx->amount }} ETH</td>
                        <td><a href="https://etherscan.io/tx/{{ $tx->hash }}" target="_blank">{{ Str::limit($tx->hash, 15) }}</a></td>
                        <td>{{ $tx->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No transactions found.</p>
    @endif
</div>
@endsection
