# Step-by-Step Fix for Production Server

## What You Need to Do Right Now:

### Step 1: Connect to Your Production Server
SSH into your production server. You need terminal/command line access.

### Step 2: Navigate to Your Laravel Project
```bash
cd /var/www/html
```
(Or wherever your Laravel project is located on the server)

### Step 3: Check Current Directory Name
```bash
ls -la resources/views/dashboard/
```
You will see either:
- `Advertiser` (capital A) - THIS IS THE PROBLEM
- `advertiser` (lowercase) - This is correct

### Step 4: Rename the Directory (if it shows "Advertiser")
```bash
mv resources/views/dashboard/Advertiser resources/views/dashboard/advertiser_temp
mv resources/views/dashboard/advertiser_temp resources/views/dashboard/advertiser
```

### Step 5: Clear All Caches
```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear
```

### Step 6: Rebuild Caches
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 7: Test
Visit: `https://adnetwork.hmstech.org/dashboard/advertiser/home`

The error should be gone!

---

## Alternative: If You Don't Have SSH Access

### Option A: Use FTP/File Manager
1. Connect to your server via FTP or cPanel File Manager
2. Navigate to: `resources/views/dashboard/`
3. Rename folder: `Advertiser` → `advertiser` (lowercase)
4. Use your hosting control panel to run cache clear commands

### Option B: Use the Fix Script
1. Upload `fix-advertiser-directory.php` to your Laravel root directory
2. Access it via browser: `https://adnetwork.hmstech.org/fix-advertiser-directory.php`
3. It will automatically fix everything
4. **DELETE the script after use for security!**

---

## Quick Copy-Paste Commands (All at Once)

If you have SSH access, copy and paste all these commands:

```bash
cd /var/www/html
mv resources/views/dashboard/Advertiser resources/views/dashboard/advertiser_temp 2>/dev/null || true
mv resources/views/dashboard/advertiser_temp resources/views/dashboard/advertiser 2>/dev/null || true
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
echo "Done! Test your advertiser dashboard now."
```

