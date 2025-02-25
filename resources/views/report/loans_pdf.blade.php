
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Loan Report</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Amount</th>
                <th>Interest Rate</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Late Fee</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($loans as $loan)
                <tr>
                    <td>{{ $loan->id }}</td>
                    <td>{{ $loan->user_id }}</td>
                    <td>₦{{ number_format($loan->amount, 2) }}</td>
                    <td>{{ $loan->interest_rate }}%</td>
                    <td>{{ $loan->duration }} months</td>
                    <td>{{ ucfirst($loan->status) }}</td>
                    <td>{{ $loan->due_date }}</td>
                    <td>₦{{ number_format($loan->late_fee, 2) }}</td>
                    <td>{{ $loan->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
