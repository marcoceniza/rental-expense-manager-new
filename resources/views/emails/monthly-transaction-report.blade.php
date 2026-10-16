<html>
<head>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Monthly Transaction Report – {{ $month }}</h1>
    <p>Total Transactions: {{ $transactions->count() }}</p>
    <p>Recurring: {{ $recurring_count }}, Non‑Recurring: {{ $non_recurring_count }}</p>
    <h2>Totals by Type</h2>
    <ul>
        @foreach ($totals as $type => $sum)
            <li>{{ ucfirst($type) }}: ${{ number_format($sum, 2) }}</li>
        @endforeach
    </ul>
    <h2>Transactions</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Category</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Recurring</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $tx)
                <tr>
                    <td>{{ $tx->id }}</td>
                    <td>{{ $tx->transaction_date->format('Y-m-d') }}</td>
                    <td>{{ $tx->category ? $tx->category->name : 'N/A' }}</td>
                    <td>{{ ucfirst($tx->type) }}</td>
                    <td>${{ number_format($tx->amount, 2) }}</td>
                    <td>{{ $tx->is_recurring_generated ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
