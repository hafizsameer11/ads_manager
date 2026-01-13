# Deploy Fix to Production Server

## Quick Fix Instructions

The advertiser dashboard error on production is caused by a case-sensitivity issue. The directory is named `Advertiser` (capital A) but the code expects `advertiser` (lowercase).

## Method 1: SSH Commands (Fastest - Recommended)

SSH into your production server and run these commands:

```bash
# Navigate to your Laravel project
cd /var/www/html

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

## Method 2: Use the Fix Script (Automated)

1. **Upload the fix script** to your production server root directory:
   - File: `fix-advertiser-directory.php`
   - Location: `/var/www/html/fix-advertiser-directory.php`

2. **Run the script via SSH:**
   ```bash
   cd /var/www/html
   php fix-advertiser-directory.php
   ```

3. **Or access via browser** (if web server allows):
   - Visit: `https://adnetwork.hmstech.org/fix-advertiser-directory.php`
   - **IMPORTANT:** Delete the script immediately after use for security!

4. **Delete the script:**
   ```bash
   rm /var/www/html/fix-advertiser-directory.php
   ```

## Method 3: Manual File Upload

1. **On your local machine:** The directory `resources/views/dashboard/advertiser/` is already correct (lowercase)

2. **Upload to production:**
   - Use FTP/SFTP or cPanel File Manager
   - Upload the entire `resources/views/dashboard/advertiser/` directory
   - Ensure it's named `advertiser` (lowercase), not `Advertiser`

3. **Delete old directory:**
   - Remove `resources/views/dashboard/Advertiser/` from production (if it exists separately)

4. **Clear caches** using Method 1 commands

## Verification

After applying the fix:

1. Visit: `https://adnetwork.hmstech.org/dashboard/advertiser/home`
2. Login as an advertiser
3. The dashboard should load without the "View [dashboard.advertiser.index] not found" error

## What the Fix Does

- Renames `Advertiser` → `advertiser` (fixes case-sensitivity)
- Clears all Laravel caches (view, config, route, optimize)
- Rebuilds all caches for optimal performance

## Why This Happened

- **Windows (local):** Case-insensitive filesystem - `Advertiser` = `advertiser` ✅
- **Linux (production):** Case-sensitive filesystem - `Advertiser` ≠ `advertiser` ❌
- **Laravel:** Uses dot notation `dashboard.advertiser.index` which expects lowercase `advertiser`

## Prevention

Always use **lowercase** for directory names in Laravel views to avoid case-sensitivity issues between Windows and Linux.

