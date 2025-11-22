<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
        }
        .invoice-title {
            font-size: 24px;
            margin-top: 10px;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-row {
            margin: 5px 0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th {
            background-color: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .total-section {
            text-align: right;
            margin-top: 20px;
            font-size: 18px;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-paid {
            background-color: #28a745;
            color: white;
        }
        .status-pending {
            background-color: #ffc107;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ config('app.name') }}</div>
        <div class="invoice-title">INVOICE</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Invoice Number:</span>
            <span>#{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Invoice Date:</span>
            <span>{{ $invoice->created_at->format('F d, Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="status-badge status-{{ $invoice->status }}">
                {{ strtoupper($invoice->status) }}
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Payment Method:</span>
            <span>{{ ucfirst($invoice->provider) }}</span>
        </div>
    </div>

    <div class="info-section">
        <h3>Bill To:</h3>
        <div class="info-row">{{ $user->name }}</div>
        <div class="info-row">{{ $user->email }}</div>
        @if($user->billing_address)
            <div class="info-row">{{ $user->billing_address }}</div>
            <div class="info-row">
                {{ $user->billing_city }}
                @if($user->billing_postal_code), {{ $user->billing_postal_code }}@endif
            </div>
            <div class="info-row">{{ $user->billing_country }}</div>
        @endif
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Period</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if($plan)
                        {{ $plan->name }} Subscription
                    @else
                        Subscription Payment
                    @endif
                </td>
                <td>
                    @if($subscription)
                        {{ ucfirst($plan->interval ?? 'monthly') }}
                    @else
                        -
                    @endif
                </td>
                <td style="text-align: right;">
                    {{ $invoice->currency }} {{ number_format($invoice->amount / 100, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <div>Subtotal: {{ $invoice->currency }} {{ number_format($invoice->amount / 100, 2) }}</div>
        <div>Tax: {{ $invoice->currency }} 0.00</div>
        <div class="total-amount">
            Total: {{ $invoice->currency }} {{ number_format($invoice->amount / 100, 2) }}
        </div>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>{{ config('app.url') }}</p>
        @if($invoice->provider_invoice_id)
            <p style="font-size: 10px;">Provider Invoice ID: {{ $invoice->provider_invoice_id }}</p>
        @endif
    </div>
</body>
</html>
