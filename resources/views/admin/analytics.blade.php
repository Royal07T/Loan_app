@extends('layouts.admin')

@section('content')
<h2>Loan Analytics</h2>
{!! $loanChart->render() !!}
@endsection
