# Fix Advertiser Dashboard Error on Production

## The Problem
- **Local (Windows):** Works fine because Windows is case-insensitive
- **Production (Linux):** Fails because Linux is case-sensitive
- The directory on production is named `Advertiser` (capital A)
- But the code looks for `advertiser` (lowercase)

## Quick Fix - Choose One Method:

### Method 1: SSH Commands (Fastest - Recommended)

SSH into your production server and run these commands:

```bash
# Navigate to your Laravel project
cd /var/www/html  # or wherever your Laravel app is located

# Rename the directory (using temporary name for case-sensitive systems)
mv resources/views/dashboard/Advertiser resources/views/dashboard/advertiser_temp
mv resources/views/dashboard/advertiser_temp resources/views/dashboard/advertiser

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

### Method 2: Use the Fix Script

1. Upload `fix-advertiser-directory.php` to your Laravel root directory on production
2. SSH into your server and run:
   ```bash
   php fix-advertiser-directory.php
   ```
3. Delete the script after use

### Method 3: Manual File Upload

1. **On your local machine:** The directory is already renamed to `advertiser` (lowercase)
2. **Upload to production:** Upload the entire `resources/views/dashboard/advertiser/` directory to your production server
3. **Delete old directory:** Delete `resources/views/dashboard/Advertiser/` from production
4. **Clear caches:** Run the cache clearing commands from Method 1

## Verify the Fix

After applying the fix:
1. Visit: `https://adnetwork.hmstech.org/dashboard/advertiser/home`
2. Login as an advertiser
3. The dashboard should load without errors

## Why This Happened

- Windows file systems are **case-insensitive**: `Advertiser` = `advertiser`
- Linux file systems are **case-sensitive**: `Advertiser` ≠ `advertiser`
- Laravel views use dot notation: `dashboard.advertiser.index` expects lowercase `advertiser`

## Prevention

Always use **lowercase** for directory names in Laravel to avoid this issue.

