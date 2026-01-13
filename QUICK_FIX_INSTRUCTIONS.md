# Quick Fix Instructions for Production

## ✅ Git Tracking Fixed!

I've fixed the Git tracking - all files are now tracked as `advertiser` (lowercase) instead of `Advertiser` (capital A).

## Next Steps:

### 1. Commit and Push (Local)

```bash
git commit -m "Fix: Rename Advertiser directory to advertiser (lowercase) for case-sensitive Linux"
git push
```

### 2. On Production Server (SSH Required)

After you push and pull on production, run these commands:

```bash
# Navigate to your Laravel project
cd /var/www/html  # or your actual project path

# Pull latest changes
git pull

# If the directory is still capital A (shouldn't happen, but just in case):
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

### 3. Test

Visit: `https://adnetwork.hmstech.org/dashboard/advertiser/home`

The error should be fixed! 🎉

## Why the Fix Script Gave 404

The fix script (`fix-advertiser-directory.php`) gave a 404 because:
- It's not in the `public` directory (Laravel routes all requests through `public/index.php`)
- You can't access root-level PHP files directly via browser in Laravel
- The proper way is to use SSH to run it, or fix via Git (which we just did)

## What We Fixed

- ✅ Git now tracks `advertiser` (lowercase) instead of `Advertiser` (capital A)
- ✅ When you push and pull on production, the directory will be correct
- ✅ No more case-sensitivity issues between Windows and Linux

