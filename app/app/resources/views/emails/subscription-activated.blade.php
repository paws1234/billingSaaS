<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .button { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Welcome to {{ config('app.name') }}!</h1>
        </div>
        <div class="content">
            <h2>Your subscription is now active</h2>
            <p>Hi {{ $subscription->user->name }},</p>
            <p>Thank you for subscribing! Your <strong>{{ $subscription->plan->name }}</strong> subscription is now active.</p>
            
            <h3>Plan Details:</h3>
            <ul>
                <li><strong>Plan:</strong> {{ $subscription->plan->name }}</li>
                <li><strong>Price:</strong> ${{ number_format($subscription->plan->amount / 100, 2) }} {{ $subscription->plan->currency }}/{{ $subscription->plan->interval }}</li>
                <li><strong>Status:</strong> Active</li>
                @if($subscription->trial_ends_at)
                <li><strong>Trial Ends:</strong> {{ $subscription->trial_ends_at->format('F j, Y') }}</li>
                @endif
            </ul>

            <h3>What's Included:</h3>
            <ul>
                @foreach($subscription->plan->metadata['features'] ?? [] as $feature)
                <li>{{ $feature }}</li>
                @endforeach
            </ul>

            <p style="text-align: center;">
                <a href="{{ config('app.frontend_url', config('app.url')) }}/dashboard" class="button">
                    Go to Dashboard
                </a>
            </p>

            <p>If you have any questions, feel free to contact our support team.</p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>You're receiving this email because you subscribed to our service.</p>
        </div>
    </div>
</body>
</html>
