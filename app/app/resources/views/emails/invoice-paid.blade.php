<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .invoice-box { background: white; border: 1px solid #ddd; padding: 20px; margin: 20px 0; }
        .button { display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Payment Received</h1>
        </div>
        <div class="content">
            <p>Hi {{ $invoice->user->name }},</p>
            <p>We've successfully processed your payment. Thank you!</p>
            
            <div class="invoice-box">
                <h3>Invoice #{{ $invoice->id }}</h3>
                <p><strong>Date:</strong> {{ $invoice->created_at->format('F j, Y') }}</p>
                <p><strong>Amount:</strong> ${{ number_format($invoice->amount / 100, 2) }} {{ $invoice->currency }}</p>
                <p><strong>Status:</strong> <span style="color: #28a745;">Paid</span></p>
                @if($invoice->subscription)
                <p><strong>Plan:</strong> {{ $invoice->subscription->plan->name }}</p>
                @endif
            </div>

            <p style="text-align: center;">
                <a href="{{ config('app.frontend_url', config('app.url')) }}/invoices" class="button">
                    View Invoice
                </a>
            </p>

            <p>A receipt has been generated and is available in your account.</p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
