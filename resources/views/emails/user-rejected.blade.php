<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Not Approved</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1 style="color: #dc3545; margin-top: 0;">Your Account Was Not Approved</h1>
        
        <p>Hello {{ $user->name }},</p>
        
        <p>We regret to inform you that your {{ $role }} account application was not approved at this time.</p>
        
        <p>We apologize for any inconvenience this may cause. If you believe this decision was made in error, or if you have additional information that might help us reconsider your application, please contact our support team.</p>
        
        <p>
            <strong>Next Steps:</strong><br>
            Please reach out to our support team for more information about why your application was not approved and to discuss potential next steps.
        </p>
        
        <p>
            <a href="{{ route('website.contact') }}" style="display: inline-block; padding: 12px 24px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; margin-top: 20px;">Contact Support</a>
        </p>
        
        <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px;">
            Best regards,<br>
            {{ config('app.name') }} Team
        </p>
    </div>
</body>
</html>

