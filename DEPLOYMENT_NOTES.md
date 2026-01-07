# Deployment Notes - Blog Images

## Steps to Fix Image Display on Live Server

### 1. Create Storage Symlink
Run this command on your live server:
```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`

### 2. Verify File Permissions
Ensure the storage directory has proper permissions:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 3. Check .env Configuration
Make sure your `.env` file has the correct `APP_URL`:
```env
APP_URL=https://yourdomain.com
```

### 4. Verify Image Files Exists
Check if images are uploaded to:
```
storage/app/public/blog-images/
```

### 5. Clear Cache (if needed)
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 6. Check Web Server Configuration
For Apache, ensure `.htaccess` in `public` directory allows symlinks:
```apache
Options +FollowSymLinks
```

For Nginx, ensure proper configuration to serve files from `public/storage`

### 7. Test Image URL
After deployment, test by accessing an image directly:
```
https://yourdomain.com/storage/blog-images/filename.jpg
```

If this URL works, the symlink is correct. If not, recreate it.

### 8. Alternative: Use Full URL
If symlinks don't work on your server, you can modify the Blog model to use absolute URLs:
```php
return url('storage/' . $this->featured_image);
```

## Troubleshooting

### Images show 404 errors:
- Symlink not created → Run `php artisan storage:link`
- Wrong permissions → Check folder permissions
- Files not uploaded → Verify files exist in `storage/app/public/blog-images/`

### Images show broken image icon:
- Check browser console for errors
- Verify APP_URL in .env matches your domain
- Check if files were actually uploaded

### Images work locally but not on live:
- Most common: Missing symlink on live server
- Different APP_URL configuration
- File permissions issue
- Different storage path configuration
