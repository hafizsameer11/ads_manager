# PowerShell script to fix Git tracking of Advertiser directory
# This script renames the directory in Git from Advertiser to advertiser

Write-Host "=== Fixing Git Tracking for Advertiser Directory ===" -ForegroundColor Cyan
Write-Host ""

# Check if we're in a git repository
if (-not (Test-Path .git)) {
    Write-Host "ERROR: Not in a Git repository!" -ForegroundColor Red
    exit 1
}

# Get all files in the Advertiser directory
$files = git ls-files resources/views/dashboard/Advertiser/

if ($files.Count -eq 0) {
    Write-Host "No files found in Advertiser directory in Git" -ForegroundColor Yellow
    exit 0
}

Write-Host "Found $($files.Count) files tracked with 'Advertiser' (capital A)" -ForegroundColor Yellow
Write-Host "Renaming to 'advertiser' (lowercase) in Git..." -ForegroundColor Yellow
Write-Host ""

# Use git mv to rename each file (this preserves history)
$renamed = 0
foreach ($file in $files) {
    $newFile = $file -replace '/Advertiser/', '/advertiser/'
    
    # Check if target already exists
    if (Test-Path $newFile) {
        Write-Host "  Skipping: $newFile already exists" -ForegroundColor Gray
        continue
    }
    
    # Use git mv with force to handle case-only rename
    git mv -f $file $newFile 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        $renamed++
        Write-Host "  ✓ Renamed: $file -> $newFile" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Failed: $file" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "=== Summary ===" -ForegroundColor Cyan
Write-Host "Renamed $renamed files in Git" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Review changes: git status" -ForegroundColor White
Write-Host "2. Commit: git add -A && git commit -m 'Fix: Rename Advertiser directory to advertiser (lowercase)'" -ForegroundColor White
Write-Host "3. Push: git push" -ForegroundColor White
Write-Host "4. On production server, run: php artisan view:clear && php artisan cache:clear && php artisan config:clear" -ForegroundColor White

