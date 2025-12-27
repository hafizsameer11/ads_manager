<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Form Submission</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1 style="color: #007bff; margin-top: 0;">New Contact Form Submission</h1>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 4px; margin: 20px 0;">
            <p><strong>Name:</strong> {{ $submission->name }}</p>
            <p><strong>Email:</strong> <a href="mailto:{{ $submission->email }}">{{ $submission->email }}</a></p>
            <p><strong>Subject:</strong> {{ $submission->subject }}</p>
            <p><strong>Submitted:</strong> {{ $submission->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
        
        <div style="margin: 20px 0;">
            <h2 style="color: #333; font-size: 18px;">Message:</h2>
            <div style="background: #fff; padding: 15px; border-left: 4px solid #007bff; margin-top: 10px;">
                {{ $submission->message }}
            </div>
        </div>
        
        <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px;">
            This is an automated notification from {{ config('app.name') }}.<br>
            Submission ID: #{{ $submission->id }}
        </p>
    </div>
</body>
</html>
