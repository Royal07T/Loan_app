<!DOCTYPE html>
<html>
<head>
    <title>Loan Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 100%; margin: auto; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background: #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Loan Report</h2>
        <p><strong>User:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>

        <table>
            <tr>
                <th>Loan ID</th>
                <th>Amount (₦)</th>
                <th>Duration (Months)</th>
                <th>Interest Rate (%)</th>
                <th>Status</th>
            </tr>
            @foreach($loans as $loan)
            <tr>
                <td>{{ $loan->id }}</td>
                <td>{{ number_format($loan->amount, 2) }}</td>
                <td>{{ $loan->duration }}</td>
                <td>{{ $loan->interest_rate }}</td>
                <td>{{ ucfirst($loan->status) }}</td>
            </tr>
            @endforeach
        </table>
    </div>
</body>
</html>
