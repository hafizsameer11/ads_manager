# Fix Advertiser Directory for Git Deployment

## The Problem

When you deploy via Git, the directory is tracked as `Advertiser` (capital A) in Git, but your code expects `advertiser` (lowercase). On Linux (production), this causes a 404 error.

## Solution: Fix Git Tracking First

### Step 1: Fix Git Tracking (Local)

Run the PowerShell script to fix Git tracking:

```powershell
.\fix-git-advertiser-directory.ps1
```

Or manually fix it:

```powershell
# Get all files in Advertiser directory
git ls-files resources/views/dashboard/Advertiser/ | ForEach-Object {
    $newFile = $_ -replace '/Advertiser/', '/advertiser/'
    git mv -f $_ $newFile
}
```

### Step 2: Commit and Push

```bash
git add -A
git commit -m "Fix: Rename Advertiser directory to advertiser (lowercase)"
git push
```

### Step 3: On Production Server (After Git Pull)

SSH into your production server and run:

```bash
# Navigate to your Laravel project
cd /var/www/html  # or your project path

# Pull the latest changes
git pull

# The directory should now be lowercase, but if it's still capital A, rename it:
mv resources/views/dashboard/Advertiser resources/views/dashboard/advertiser_temp 2>/dev/null || true
mv resources/views/dashboard/advertiser_temp resources/views/dashboard/advertiser 2>/dev/null || true

# Clear all caches
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Alternative: Quick Fix on Production Only

If you can't fix Git right now, SSH into production and run:

```bash
cd /var/www/html
mv resources/views/dashboard/Advertiser resources/views/dashboard/advertiser_temp
mv resources/views/dashboard/advertiser_temp resources/views/dashboard/advertiser
php artisan view:clear && php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan optimize
```

**Note:** This fix will be lost on the next `git pull` if the Git repository still has `Advertiser` (capital A). You should fix Git tracking to make it permanent.

## Why This Happens

- **Git on Windows:** Doesn't track case-only renames properly
- **Linux (Production):** Case-sensitive filesystem requires exact match
- **Laravel:** Expects `dashboard.advertiser.index` (lowercase)

## Prevention

After fixing, always use lowercase for directory names in Laravel views to avoid this issue.

