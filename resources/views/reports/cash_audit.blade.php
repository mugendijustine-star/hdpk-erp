<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Audit Variance Report</title>
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
        <h2>Cash Audit Variance Report</h2>
        <p>Date Range: {{ $date_from ?? 'All' }} to {{ $date_to ?? 'All' }}</p>
        <p>Branch: {{ $branch_id ?? 'All' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Branch</th>
                <th>Cashier</th>
                <th>Expected Cash</th>
                <th>Counted Cash</th>
                <th>Difference</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Submitted By</th>
                <th>Approved By</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['branch_id'] ?? 'N/A' }}</td>
                    <td>{{ $row['cashier_name'] ?? 'N/A' }}</td>
                    <td>{{ number_format($row['expected_cash'], 2) }}</td>
                    <td>{{ number_format($row['counted_cash'], 2) }}</td>
                    <td>{{ number_format($row['difference'], 2) }}</td>
                    <td>{{ $row['reason'] ?? '' }}</td>
                    <td>{{ ucfirst($row['status'] ?? '') }}</td>
                    <td>{{ $row['submitted_by_name'] ?? 'N/A' }}</td>
                    <td>{{ $row['approved_by_name'] ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">No cash audits found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Summary</h3>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Metric</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Expected Cash</td>
                <td>{{ number_format($totals['expected_cash'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Total Counted Cash</td>
                <td>{{ number_format($totals['counted_cash'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Total Difference</td>
                <td>{{ number_format($totals['difference'] ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
