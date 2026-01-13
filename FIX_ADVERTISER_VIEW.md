# Fix Advertiser View Error on Production

## Problem
The error `View [dashboard.advertiser.index] not found` occurs because:
- The view directory was named `Advertiser` (capital A) but controllers reference `dashboard.advertiser.index` (lowercase)
- Windows (local) is case-insensitive, so it works locally
- Linux (production) is case-sensitive, so it fails on production

## Solution

### Option 1: Fix via SSH (Recommended)

SSH into your production server and run these commands:

```bash
# Navigate to your Laravel project directory
cd /path/to/your/laravel/project

# Rename the directory (using temporary name first)
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

### Option 2: Fix via Web Route (If you have access)

Visit this URL on your production server (make sure to secure it or remove after use):
```
https://yourdomain.com/fix-advertiser-view
```

This will automatically:
1. Rename the directory if it exists
2. Clear all caches
3. Return a success message

**IMPORTANT:** Remove or secure this route after fixing the issue!

### Option 3: Manual File Upload

1. Upload the renamed `advertiser` directory (lowercase) to `resources/views/dashboard/`
2. Delete the old `Advertiser` directory (capital A) from production
3. Clear caches using the commands in Option 1

## Verification

After applying the fix, test by:
1. Logging in as an advertiser
2. Accessing the advertiser dashboard
3. The error should be resolved

## Prevention

Always use lowercase for directory names in Laravel to avoid case-sensitivity issues between Windows and Linux.

