<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Sheet</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1, h2, h3 { margin: 0 0 10px 0; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
        td.numeric, th.numeric { text-align: right; }
        tfoot td { font-weight: bold; }
        .summary { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HDP(K) LTD</h1>
        <h2>Balance Sheet</h2>
        <p>As at {{ $as_at }}</p>
    </div>

    <h3>Assets</h3>
    <table>
        <thead>
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th class="numeric">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($assets as $account)
                <tr>
                    <td>{{ $account['account_code'] }}</td>
                    <td>{{ $account['account_name'] }}</td>
                    <td class="numeric">{{ number_format($account['balance'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">No asset balances found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total Assets</td>
                <td class="numeric">{{ number_format($totals['total_assets'] ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <h3>Liabilities</h3>
    <table>
        <thead>
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th class="numeric">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($liabilities as $account)
                <tr>
                    <td>{{ $account['account_code'] }}</td>
                    <td>{{ $account['account_name'] }}</td>
                    <td class="numeric">{{ number_format($account['balance'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">No liability balances found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total Liabilities</td>
                <td class="numeric">{{ number_format($totals['total_liabilities'] ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <h3>Equity</h3>
    <table>
        <thead>
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th class="numeric">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($equity as $account)
                <tr>
                    <td>{{ $account['account_code'] }}</td>
                    <td>{{ $account['account_name'] }}</td>
                    <td class="numeric">{{ number_format($account['balance'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">No equity balances found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total Equity</td>
                <td class="numeric">{{ number_format($totals['total_equity'] ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th class="numeric">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Assets</td>
                    <td class="numeric">{{ number_format($totals['total_assets'] ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Total Liabilities + Equity</td>
                    <td class="numeric">{{ number_format($totals['liabilities_and_equity'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>Balanced</td>
                    <td class="numeric">{{ ($totals['is_balanced'] ?? false) ? 'Yes' : 'No' }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
