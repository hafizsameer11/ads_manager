<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Abuse Report</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1 style="color: #dc3545; margin-top: 0;">⚠️ New Abuse Report</h1>
        
        <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
            <p><strong>Report Type:</strong> <span style="text-transform: capitalize;">{{ $report->type }}</span></p>
            <p><strong>Status:</strong> <span style="text-transform: capitalize;">{{ $report->status }}</span></p>
            <p><strong>Submitted:</strong> {{ $report->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 4px; margin: 20px 0;">
            <p><strong>Reporter Email:</strong> <a href="mailto:{{ $report->email }}">{{ $report->email }}</a></p>
            @if($report->url)
            <p><strong>Reported URL:</strong> <a href="{{ $report->url }}" target="_blank">{{ $report->url }}</a></p>
            @endif
        </div>
        
        <div style="margin: 20px 0;">
            <h2 style="color: #333; font-size: 18px;">Description:</h2>
            <div style="background: #fff; padding: 15px; border-left: 4px solid #dc3545; margin-top: 10px;">
                {{ $report->description }}
            </div>
        </div>
        
        <p style="margin-top: 30px;">
            <a href="{{ route('dashboard.admin.abuse-reports') }}" style="display: inline-block; padding: 12px 24px; background: #dc3545; color: #fff; text-decoration: none; border-radius: 4px;">Review Report</a>
        </p>
        
        <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px;">
            This is an automated notification from {{ config('app.name') }}.<br>
            Report ID: #{{ $report->id }}
        </p>
    </div>
</body>
</html>
