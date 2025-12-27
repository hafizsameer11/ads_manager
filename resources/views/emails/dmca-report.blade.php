<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New DMCA Takedown Request</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1 style="color: #dc3545; margin-top: 0;">⚖️ New DMCA Takedown Request</h1>
        
        <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
            <p><strong>Status:</strong> <span style="text-transform: capitalize;">{{ $report->status }}</span></p>
            <p><strong>Submitted:</strong> {{ $report->created_at->format('F j, Y \a\t g:i A') }}</p>
            <p><strong>Accuracy Confirmed:</strong> {{ $report->accuracy_confirmed ? 'Yes' : 'No' }}</p>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 4px; margin: 20px 0;">
            <p><strong>Copyright Owner:</strong> {{ $report->copyright_owner }}</p>
            <p><strong>Contact Name:</strong> {{ $report->contact_name }}</p>
            <p><strong>Contact Email:</strong> <a href="mailto:{{ $report->contact_email }}">{{ $report->contact_email }}</a></p>
            <p><strong>Contact Phone:</strong> {{ $report->contact_phone }}</p>
            <p><strong>Infringing URL:</strong> <a href="{{ $report->infringing_url }}" target="_blank">{{ $report->infringing_url }}</a></p>
        </div>
        
        <div style="margin: 20px 0;">
            <h2 style="color: #333; font-size: 18px;">Original Work:</h2>
            <div style="background: #fff; padding: 15px; border-left: 4px solid #007bff; margin-top: 10px;">
                {{ $report->original_work }}
            </div>
        </div>
        
        <div style="margin: 20px 0;">
            <h2 style="color: #333; font-size: 18px;">Statement:</h2>
            <div style="background: #fff; padding: 15px; border-left: 4px solid #dc3545; margin-top: 10px;">
                {{ $report->statement }}
            </div>
        </div>
        
        <p style="margin-top: 30px;">
            <a href="{{ route('dashboard.admin.dmca-reports') }}" style="display: inline-block; padding: 12px 24px; background: #dc3545; color: #fff; text-decoration: none; border-radius: 4px;">Review DMCA Request</a>
        </p>
        
        <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px;">
            <strong>⚠️ IMPORTANT:</strong> This is a legal notice. Please review and process according to DMCA procedures.<br><br>
            This is an automated notification from {{ config('app.name') }}.<br>
            DMCA Request ID: #{{ $report->id }}
        </p>
    </div>
</body>
</html>
