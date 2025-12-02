<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Loss & Damage Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1, h2, h3 { margin: 0 0 10px 0; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
        .summary { margin-top: 10px; }
        .summary table { width: auto; }
        .summary td { padding: 4px 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HDP(K) LTD</h1>
        <h2>Stock Loss &amp; Damage Report</h2>
        <p>Date Range: {{ $date_from ?? 'All' }} to {{ $date_to ?? 'All' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Branch</th>
                <th>Product</th>
                <th>SKU</th>
                <th>Loss Type</th>
                <th>Qty Lost</th>
                <th>Value Lost</th>
                <th>Responsible</th>
                <th>Manager Comment</th>
                <th>Admin Comment</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['branch'] }}</td>
                    <td>{{ $row['product'] }}</td>
                    <td>{{ $row['sku'] }}</td>
                    <td>{{ $row['loss_type'] }}</td>
                    <td>{{ number_format($row['qty_lost'], 2) }}</td>
                    <td>{{ number_format($row['value_lost'], 2) }}</td>
                    <td>{{ $row['responsible'] }}</td>
                    <td>{{ $row['manager_comment'] }}</td>
                    <td>{{ $row['admin_comment'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">No stock losses found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td><strong>Total Quantity Lost:</strong></td>
                <td>{{ number_format($total_qty_lost, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Value Lost:</strong></td>
                <td>{{ number_format($total_value_lost, 2) }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
