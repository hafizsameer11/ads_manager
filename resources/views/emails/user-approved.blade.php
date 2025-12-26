<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Approved</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1 style="color: #28a745; margin-top: 0;">Your Account Has Been Approved</h1>
        
        <p>Hello {{ $user->name }},</p>
        
        <p>We are pleased to inform you that your {{ $role }} account has been approved and is now active.</p>
        
        <p>You can now:</p>
        <ul>
            <li>Access your full dashboard</li>
            @if($user->isPublisher())
            <li>Add websites and create ad units</li>
            <li>View earnings and analytics</li>
            <li>Request withdrawals</li>
            @elseif($user->isAdvertiser())
            <li>Create and manage campaigns</li>
            <li>Deposit funds</li>
            <li>View analytics and reports</li>
            @endif
        </ul>
        
        <p>
            <a href="{{ route('dashboard') }}" style="display: inline-block; padding: 12px 24px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; margin-top: 20px;">Access Dashboard</a>
        </p>
        
        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
        
        <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px;">
            Best regards,<br>
            {{ config('app.name') }} Team
        </p>
    </div>
</body>
</html>

