@extends('layouts.admin')

@section('content')
<h2>Admin Dashboard</h2>
<div>
    <h3>Loan Summary</h3>
    <p>Total Loans: {{ $totalLoans }}</p>
    <p>Pending Loans: {{ $pendingLoans }}</p>
    <p>Approved Loans: {{ $approvedLoans }}</p>
    <p>Rejected Loans: {{ $rejectedLoans }}</p>
</div>

<div>
    <h3>User Summary</h3>
    <p>Total Users: {{ $totalUsers }}</p>
</div>
@endsection
