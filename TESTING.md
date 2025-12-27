# Testing Guide - Real Publisher Integration

## 🎯 Quick Start

### 1. Start Server
```bash
php artisan serve
```

### 2. Open Test Page
```
http://localhost:8000/test.html
```

### 3. Get Your Unit Codes
1. Login as publisher: `http://localhost:8000/login`
2. Go to: `/dashboard/publisher/sites/{website}/ad-units`
3. Click on an ad unit
4. Copy the embed code (or just copy the unit code)

### 4. Replace Unit Codes
- Open `public/test.html` in a text editor
- Find `YOUR_UNIT_CODE_1`, `YOUR_UNIT_CODE_2`, etc.
- Replace with your actual 16-character unit codes
- Save the file

### 5. Test
- Refresh the browser
- Ads will load automatically
- Check browser console (F12) for logs
- Scroll to trigger impressions
- Click ads to test clicks

## ✅ How It Works

**No API URL Configuration Needed!**

When the script loads from:
```
http://localhost:8000/js/ads-network.js
```

The SDK automatically:
1. Detects the script source
2. Extracts base URL: `http://localhost:8000`
3. Uses it for API calls automatically

**Publishers just copy-paste embed code - that's it!**

## 📋 Test Checklist

- [ ] Ads load in correct positions
- [ ] Ads display correctly
- [ ] Impressions track when scrolling
- [ ] Clicks track when clicking ads
- [ ] Multiple ads work simultaneously
- [ ] Check browser console for errors
- [ ] Verify in database (impressions/clicks tables)

## 🔍 Verify Results

```sql
-- Check impressions
SELECT * FROM impressions ORDER BY id DESC LIMIT 5;

-- Check clicks  
SELECT * FROM clicks ORDER BY id DESC LIMIT 5;

-- Check publisher balance
SELECT balance, total_earnings FROM publishers WHERE id = YOUR_ID;
```

---

**That's it!** This is exactly how real publishers integrate ads. 🚀

