<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cashbook Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1, h2, h3 { margin: 0 0 10px 0; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
        .summary-table td, .summary-table th { text-align: right; }
        .summary-table td:first-child, .summary-table th:first-child { text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HDP(K) LTD</h1>
        <h2>Cashbook Report</h2>
        <p>Date Range: {{ $date_from }} to {{ $date_to }}</p>
    </div>

    <h3>Receipts</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Source</th>
                <th>Description</th>
                <th>Method</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($receipts as $receipt)
                <tr>
                    <td>{{ $receipt['date'] }}</td>
                    <td>{{ $receipt['source'] }}</td>
                    <td>{{ $receipt['description'] }}</td>
                    <td>{{ $receipt['method'] }}</td>
                    <td>{{ number_format($receipt['amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No receipts found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Payments</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Destination</th>
                <th>Description</th>
                <th>Method</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                <tr>
                    <td>{{ $payment['date'] }}</td>
                    <td>{{ $payment['destination'] }}</td>
                    <td>{{ $payment['description'] }}</td>
                    <td>{{ $payment['method'] }}</td>
                    <td>{{ number_format($payment['amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No payments found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Summary</h3>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Method</th>
                <th>Total Receipts</th>
                <th>Total Payments</th>
                <th>Net</th>
            </tr>
        </thead>
        <tbody>
            @php
                $methods = array_unique(array_merge(array_keys($totals_receipts_by_method ?? []), array_keys($totals_payments_by_method ?? []), ['cash','till','kt_mobile','nat','equity','coop']));
            @endphp
            @forelse ($methods as $method)
                <tr>
                    <td>{{ $method }}</td>
                    <td>{{ number_format($totals_receipts_by_method[$method] ?? 0, 2) }}</td>
                    <td>{{ number_format($totals_payments_by_method[$method] ?? 0, 2) }}</td>
                    <td>{{ number_format($net_by_method[$method] ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No summary available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
