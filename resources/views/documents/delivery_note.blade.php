<div style="border: 4px solid orange; padding: 20px; font-family: Arial, sans-serif;">
    <header style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">HDP(K) LTD</h2>
        <div>Pate Road, P.O Box 7684-00100 Nairobi</div>
        <div>Email: deliveries@hdpk.co.ke | Phone: 0111100000</div>
        <h3 style="margin: 15px 0 0 0;">DELIVERY NOTE</h3>
    </header>

    <section style="margin-bottom: 20px;">
        <div><strong>Delivery Note #:</strong> {{ $sale->id }}</div>
        <div><strong>Date:</strong> {{ optional($sale->date_time)->format('d M Y, h:i A') ?? $sale->date_time }}</div>
        @php
            $customer = $sale->customer ?? null;
        @endphp
        @if($customer)
            <div style="margin-top: 10px;">
                <div><strong>Customer:</strong> {{ $customer->name ?? 'N/A' }}</div>
                @if(!empty($customer->contact))
                    <div><strong>Contact:</strong> {{ $customer->contact }}</div>
                @endif
                @if(!empty($customer->email))
                    <div><strong>Email:</strong> {{ $customer->email }}</div>
                @endif
            </div>
        @endif
    </section>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="border-bottom: 2px solid orange; text-align: left; padding: 8px;">#</th>
                <th style="border-bottom: 2px solid orange; text-align: left; padding: 8px;">Description</th>
                <th style="border-bottom: 2px solid orange; text-align: right; padding: 8px;">Qty</th>
                <th style="border-bottom: 2px solid orange; text-align: right; padding: 8px;">Unit Price</th>
                <th style="border-bottom: 2px solid orange; text-align: right; padding: 8px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sale->lines ?? [] as $index => $line)
                @php
                    $qty = $line->quantity_decoded ?? $line->quantity ?? 0;
                    $unit = $line->unit_price_decoded ?? $line->unit_price ?? 0;
                    $lineTotal = $line->total_decoded ?? $line->total ?? ($qty * $unit);
                @endphp
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $index + 1 }}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $line->description ?? '' }}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right;">{{ number_format($qty, 2) }}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right;">{{ number_format($unit, 2) }}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right;">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding: 12px; text-align: center;">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <footer style="margin-top: 30px; text-align: center;">
        <div style="border-top: 1px solid orange; padding-top: 10px;">
            Thank you for doing business with HDP(K) LTD.
        </div>
    </footer>
</div>
