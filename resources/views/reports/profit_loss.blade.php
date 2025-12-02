<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit &amp; Loss Statement</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1, h2, h3 { margin: 0 0 10px 0; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
        td.numeric, th.numeric { text-align: right; }
        tfoot td { font-weight: bold; }
        .net-profit { font-size: 14px; font-weight: bold; text-align: right; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HDP(K) LTD</h1>
        <h2>Profit &amp; Loss Statement</h2>
        <p>Date Range: {{ $date_from }} to {{ $date_to }}</p>
    </div>

    <h3>Income</h3>
    <table>
        <thead>
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th class="numeric">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($income_accounts as $account)
                <tr>
                    <td>{{ $account['account_code'] }}</td>
                    <td>{{ $account['name'] }}</td>
                    <td class="numeric">{{ number_format($account['amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">No income records found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total Income</td>
                <td class="numeric">{{ number_format($total_income, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <h3>Expenses</h3>
    <table>
        <thead>
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th class="numeric">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($expense_accounts as $account)
                <tr>
                    <td>{{ $account['account_code'] }}</td>
                    <td>{{ $account['name'] }}</td>
                    <td class="numeric">{{ number_format($account['amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">No expense records found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total Expenses</td>
                <td class="numeric">{{ number_format($total_expenses, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="net-profit">
        Net Profit: {{ number_format($net_profit, 2) }}
    </div>
</body>
</html>
