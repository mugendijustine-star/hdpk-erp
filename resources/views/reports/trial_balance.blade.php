<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Balance</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1, h2, h3 { margin: 0 0 10px 0; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
        td.numeric, th.numeric { text-align: right; }
        tfoot td { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HDP(K) LTD</h1>
        <h2>Trial Balance</h2>
        <p>Date Range: {{ $date_from }} to {{ $date_to }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th>Type</th>
                <th class="numeric">Debit</th>
                <th class="numeric">Credit</th>
                <th class="numeric">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($accounts as $account)
                <tr>
                    <td>{{ $account['account_code'] }}</td>
                    <td>{{ $account['account_name'] }}</td>
                    <td>{{ $account['type'] }}</td>
                    <td class="numeric">{{ number_format($account['total_debit'], 2) }}</td>
                    <td class="numeric">{{ number_format($account['total_credit'], 2) }}</td>
                    <td class="numeric">{{ number_format($account['balance'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No records found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Totals</td>
                <td class="numeric">{{ number_format($totals['total_debit'] ?? 0, 2) }}</td>
                <td class="numeric">{{ number_format($totals['total_credit'] ?? 0, 2) }}</td>
                <td class="numeric">{{ number_format(($totals['balance'] ?? 0), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
