<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Field Sales Rep Performance Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; margin-bottom: 5px; }
        h2 { text-align: center; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h1>HDP(K) LTD</h1>
    <h2>Field Sales Rep Performance Report</h2>
    <p><strong>Date Range:</strong> {{ $date_from ?? 'Start' }} to {{ $date_to ?? 'Today' }}</p>

    <table>
        <thead>
            <tr>
                <th>Rep</th>
                <th>Region</th>
                <th>Visits</th>
                <th>Leads</th>
                <th>Converted</th>
                <th>Orders</th>
                <th>Dispatched</th>
                <th>Sales Total</th>
                <th>Target</th>
                <th>Achievement %</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['rep'] }}</td>
                    <td>{{ $row['region'] }}</td>
                    <td>{{ $row['visits_count'] }}</td>
                    <td>{{ $row['leads_count'] }}</td>
                    <td>{{ $row['leads_converted'] }}</td>
                    <td>{{ $row['orders_count'] }}</td>
                    <td>{{ $row['orders_dispatched_count'] }}</td>
                    <td>{{ number_format($row['sales_total'], 2) }}</td>
                    <td>{{ number_format($row['target_amount'], 2) }}</td>
                    <td>{{ number_format($row['achievement_percent'], 2) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">No data available for the selected range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
