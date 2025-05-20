<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 12px;
            line-height: 1.5;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }
        .header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        .company-info {
            float: left;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 5px;
        }
        .invoice-details {
            float: right;
            text-align: right;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 10px;
        }
        .customer-details {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
        }
        .billing-info, .shipping-info {
            width: 48%;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #4e73df;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fc;
            font-weight: bold;
            color: #4e73df;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            width: 300px;
            margin-left: auto;
            margin-bottom: 30px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .grand-total {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #4e73df;
            border-bottom: 2px solid #4e73df;
            padding: 10px 0;
            margin-top: 10px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #777;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-completed {
            background-color: #1cc88a;
            color: white;
        }
        .status-pending {
            background-color: #f6c23e;
            color: #333;
        }
        .status-cancelled {
            background-color: #e74a3b;
            color: white;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header clearfix">
            <div class="company-info">
                <div class="company-name">{{ config('app.name', 'Stock Management System') }}</div>
                <div>123 Business Street, City</div>
                <div>Phone: +1 (123) 456-7890</div>
                <div>Email: info@stocksystem.com</div>
            </div>
            <div class="invoice-details">
                <div class="invoice-title">INVOICE</div>
                <div><strong>Invoice #:</strong> {{ $sale->invoice_number }}</div>
                <div><strong>Date:</strong> {{ $sale->created_at->format('M d, Y') }}</div>
                <div>
                    <strong>Status:</strong> 
                    <span class="status-badge status-{{ strtolower($sale->status) }}">
                        {{ ucfirst($sale->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="customer-details clearfix">
            <div class="billing-info">
                <div class="section-title">BILL TO</div>
                <div><strong>{{ $sale->customer->name ?? 'Walk-in Customer' }}</strong></div>
                @if($sale->customer)
                <div>{{ $sale->customer->address ?? '' }}</div>
                <div>{{ $sale->customer->city ?? '' }}{{ $sale->customer->state ? ', '.$sale->customer->state : '' }} {{ $sale->customer->postal_code ?? '' }}</div>
                <div>{{ $sale->customer->country ?? '' }}</div>
                <div>Phone: {{ $sale->customer->phone ?? 'N/A' }}</div>
                <div>Email: {{ $sale->customer->email ?? 'N/A' }}</div>
                @endif
            </div>
            <div class="shipping-info">
                <div class="section-title">PAYMENT INFORMATION</div>
                <div><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</div>
                <div><strong>Payment Status:</strong> {{ ucfirst($sale->payment_status) }}</div>
                <div><strong>Amount Paid:</strong> ${{ number_format($sale->paid_amount, 2) }}</div>
                <div><strong>Amount Due:</strong> ${{ number_format($sale->total_amount - $sale->paid_amount, 2) }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Product</th>
                    <th style="width: 80px;" class="text-right">Price</th>
                    <th style="width: 80px;" class="text-center">Quantity</th>
                    <th style="width: 100px;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name }}</strong>
                        @if($item->product->code)
                        <br><small>SKU: {{ $item->product->code }}</small>
                        @endif
                    </td>
                    <td class="text-right">${{ number_format($item->price, 2) }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->quantity * $item->price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <div>Subtotal:</div>
                <div>${{ number_format($sale->total_amount - ($sale->tax ?? 0), 2) }}</div>
            </div>
            @if($sale->tax > 0)
            <div class="total-row">
                <div>Tax:</div>
                <div>${{ number_format($sale->tax, 2) }}</div>
            </div>
            @endif
            @if($sale->discount > 0)
            <div class="total-row">
                <div>Discount:</div>
                <div>-${{ number_format($sale->discount, 2) }}</div>
            </div>
            @endif
            <div class="total-row grand-total">
                <div>TOTAL:</div>
                <div>${{ number_format($sale->total_amount, 2) }}</div>
            </div>
        </div>

        @if($sale->notes)
        <div style="margin-bottom: 30px;">
            <div class="section-title">NOTES</div>
            <div style="padding: 10px; background-color: #f8f9fc; border-radius: 4px;">
                {{ $sale->notes }}
            </div>
        </div>
        @endif

        <div style="margin-top: 50px; text-align: center;">
            <p>Thank you for your business!</p>
        </div>

        <div class="footer">
            <p>This is a computer-generated document. No signature is required.</p>
            <p>{{ config('app.name', 'Stock Management System') }} &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html> 