<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daily Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0; }
        .section-title { font-weight: bold; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
<div class="header">
    <h1>HDP(K) LTD</h1>
    <p>Industrial Area, Kampala</p>
    <p>Contact: +256 789 000 000 | info@hdpk.com</p>
</div>

<div class="section-title">Daily Sales Report</div>
<p>Date: {{ $date }}</p>

<div class="section-title">Totals by Payment Method</div>
<table>
    <tbody>
    @foreach($totals_by_method as $method => $amount)
        <tr>
            <th style="width: 40%">{{ ucwords(str_replace('_', ' ', $method)) }}</th>
            <td>{{ number_format($amount, 2, '.', ',') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="section-title">Sales</div>
<table>
    <thead>
    <tr>
        <th style="width: 12%">Time</th>
        <th style="width: 18%">Invoice No</th>
        <th style="width: 25%">Customer</th>
        <th style="width: 15%">Total</th>
        <th>Payment Methods</th>
    </tr>
    </thead>
    <tbody>
    @forelse($rows as $row)
        <tr>
            <td>{{ $row['time'] }}</td>
            <td>{{ $row['invoice_no'] }}</td>
            <td>{{ $row['customer_name'] }}</td>
            <td>{{ number_format($row['total'], 2, '.', ',') }}</td>
            <td>{{ $row['payment_summary'] }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" style="text-align: center;">No sales recorded for this date.</td>
        </tr>
    @endforelse
    </tbody>
</table>
</body>
</html>
