<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Capital Movement Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 30px; }
        h1, h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        .summary { margin-top: 20px; }
        .summary div { margin-bottom: 6px; }
    </style>
</head>
<body>
    <h1>HDP(K) LTD</h1>
    <h2>Capital Movement Report</h2>

    <div class="summary">
        <div><strong>Date Range:</strong> {{ $date_from }} to {{ $date_to }}</div>
        <div><strong>Opening Balance:</strong> {{ number_format($opening_balance, 2) }}</div>
        <div><strong>Closing Balance:</strong> {{ number_format($closing_balance, 2) }}</div>
    </div>

    <table>
        <tbody>
            <tr>
                <th>Capital from Opening Stock</th>
                <td>{{ number_format($capital_from_opening_stock, 2) }}</td>
            </tr>
            <tr>
                <th>Capital-funded Purchases</th>
                <td>{{ number_format($capital_from_purchases, 2) }}</td>
            </tr>
            <tr>
                <th>Total Contributions</th>
                <td>{{ number_format($capital_contributions, 2) }}</td>
            </tr>
            <tr>
                <th>Withdrawals</th>
                <td>{{ number_format($capital_withdrawals, 2) }} (placeholder)</td>
            </tr>
            <tr>
                <th>Closing Balance</th>
                <td>{{ number_format($closing_balance, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
