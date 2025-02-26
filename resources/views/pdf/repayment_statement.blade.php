<!DOCTYPE html>
<html>
<head>
    <title>Repayment History</title>
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
        <h2>Repayment History</h2>
        <p><strong>Loan ID:</strong> {{ $loan->id }}</p>
        <p><strong>Loan Amount:</strong> ₦{{ number_format($loan->amount, 2) }}</p>
        <p><strong>Interest Rate:</strong> {{ $loan->interest_rate }}%</p>
        <p><strong>Total Paid:</strong> ₦{{ number_format($repayments->sum('amount_paid'), 2) }}</p>
        <p><strong>Total Late Fees:</strong> ₦{{ number_format($totalLateFees, 2) }}</p>
        <p><strong>Remaining Balance:</strong> ₦{{ number_format($remainingBalance, 2) }}</p>

        <table>
            <tr>
                <th>Payment Date</th>
                <th>Amount Paid (₦)</th>
                <th>Late Fee (₦)</th>
                <th>Payment Method</th>
            </tr>
            @foreach($repayments as $repayment)
            <tr>
                <td>{{ $repayment->payment_date }}</td>
                <td>{{ number_format($repayment->amount_paid, 2) }}</td>
                <td>{{ number_format($repayment->late_fee, 2) }}</td>
                <td>{{ $repayment->payment_method }}</td>
            </tr>
            @endforeach
        </table>
    </div>
</body>
</html>
