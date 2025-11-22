<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .warning-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        .button { display: inline-block; padding: 12px 24px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Payment Failed</h1>
        </div>
        <div class="content">
            <p>Hi {{ $subscription->user->name }},</p>
            <p>We were unable to process your recent payment for the <strong>{{ $subscription->plan->name }}</strong> subscription.</p>
            
            <div class="warning-box">
                <h3>Action Required</h3>
                <p>Your subscription is currently <strong>past due</strong>. To avoid service interruption, please update your payment method.</p>
                <p>We'll automatically retry the payment, but updating your payment method now will ensure uninterrupted service.</p>
            </div>

            <p style="text-align: center;">
                <a href="{{ config('app.frontend_url', config('app.url')) }}/billing/portal" class="button">
                    Update Payment Method
                </a>
            </p>

            <h3>Common Reasons for Payment Failure:</h3>
            <ul>
                <li>Insufficient funds</li>
                <li>Expired card</li>
                <li>Incorrect card details</li>
                <li>Bank declined the transaction</li>
            </ul>

            <p>If you have questions or need assistance, please contact our support team.</p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
