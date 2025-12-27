# Realistic Testing Guide - Publisher Landing Pages

## 🎯 Overview

I've created **two realistic publisher landing pages** that simulate real websites where ads would be embedded. These pages look and feel like actual publisher sites, not testing interfaces.

## 📄 Available Test Pages

### 1. **Demo Blog Site** (`demo-site.html`)
- Modern blog layout with articles
- Multiple ad placements (banner, square, vertical)
- Sidebar with widgets
- Configuration panel for easy ad setup

### 2. **Tech News Site** (`demo-landing.html`)
- Professional news website layout
- Hero section with featured content
- Article layout with inline ads
- Sidebar with popular articles

## 🚀 Quick Start

### Step 1: Start Laravel Server
```bash
cd /Users/macbookpro/ads_manager
php artisan serve
```

### Step 2: Open Test Page
Open in your browser:
- **Blog Demo**: `http://localhost:8000/demo-site.html`
- **News Demo**: `http://localhost:8000/demo-landing.html`

### Step 3: Configure Ads
1. Click the **"⚙️ Config"** or **"⚙️ Configure Ads"** button (bottom right)
2. Enter your **API URL** (default: `http://localhost:8000`)
3. Enter your **Ad Unit Codes** (16 characters each)
4. Click **"Load All Ads"** or **"Load Ads"**

## 📋 Testing Workflow

### 1. Create Test Data (One Time Setup)

#### A. Create Publisher Account
```
1. Go to: http://localhost:8000/register
2. Register as "Publisher"
3. Login as admin → Approve publisher
```

#### B. Add Website
```
1. Login as publisher
2. Go to: /dashboard/publisher/sites
3. Add website: "example.com"
4. Copy verification code
5. Login as admin → Approve website
```

#### C. Create Ad Units
```
1. As publisher, go to website detail page
2. Create multiple ad units:
   - Banner Ad (728x90) - for top/bottom
   - Square Ad (300x250) - for middle/sidebar
   - Vertical Ad (300x600) - for sidebar
3. Copy the Unit Codes (16 characters each)
```

#### D. Create Campaign
```
1. Register/Login as Advertiser
2. Create campaign:
   - Type: Banner
   - Pricing: CPC or CPM
   - Budget: 100.00
   - Bid: 0.10
   - Upload ad image or use placeholder URL
3. Admin approves campaign
```

### 2. Test on Real Landing Pages

#### On Demo Blog Site (`demo-site.html`):
1. Open the page
2. Click **"⚙️ Config"** button
3. Enter your unit codes:
   - Banner Top: Your banner ad unit code
   - Square Middle: Your square ad unit code
   - Banner Bottom: Your banner ad unit code
   - Sidebar Vertical: Your vertical ad unit code
   - Sidebar Square: Your square ad unit code
4. Click **"Load All Ads"**
5. Ads will appear in their respective positions
6. Scroll the page to trigger impression tracking
7. Click on ads to test click tracking

#### On Tech News Site (`demo-landing.html`):
1. Open the page
2. Click **"⚙️ Configure Ads"** button
3. Enter your unit codes for:
   - Article Top Banner
   - Article Middle Rectangle
   - Article Bottom Banner
   - Sidebar Skyscraper
   - Sidebar Rectangle
4. Click **"Load Ads"**
5. Test scrolling and clicking

## ✅ What to Test

### Ad Display
- [ ] Ads load in correct positions
- [ ] Ads display correct images/content
- [ ] Ads have proper dimensions
- [ ] Multiple ads load simultaneously
- [ ] Ads don't break page layout

### Impression Tracking
- [ ] Open browser console (F12)
- [ ] Scroll page to make ads visible
- [ ] Check console for "Impression tracked" messages
- [ ] Verify in database:
  ```sql
  SELECT * FROM impressions ORDER BY id DESC LIMIT 5;
  ```

### Click Tracking
- [ ] Click on an ad
- [ ] Check console for "Click tracked" messages
- [ ] Verify redirect to target URL
- [ ] Check database:
  ```sql
  SELECT * FROM clicks ORDER BY id DESC LIMIT 5;
  ```

### Multiple Ad Units
- [ ] Test different ad units on same page
- [ ] Verify each ad tracks independently
- [ ] Check that impressions/clicks are attributed correctly

### Responsive Design
- [ ] Resize browser window
- [ ] Test on mobile viewport
- [ ] Verify ads adapt to screen size

## 🔍 Debugging

### Browser Console
Open browser console (F12) to see:
- SDK loading status
- Ad initialization messages
- Impression/click tracking logs
- Any errors

### Network Tab
Check Network tab (F12 → Network) for:
- SDK script loading (`ads-network.js`)
- Ad serve API calls (`/api/ad/{unitCode}`)
- Impression tracking (`/api/ad/impression`)
- Click tracking (`/api/ad/click`)

### Common Issues

**Ads Not Loading:**
- Check unit codes are correct (16 characters)
- Verify API URL is correct
- Check browser console for errors
- Verify ad units are active
- Verify campaigns are approved

**Impressions Not Tracking:**
- Scroll to make ads visible (50% viewport)
- Check CORS settings
- Verify API endpoint is accessible
- Check browser console for errors

**Clicks Not Working:**
- Check click rate limits
- Verify fraud detection not blocking
- Check campaign balance
- Verify target URL is set

## 📊 Verify Results

### Check Publisher Earnings
```sql
SELECT balance, total_earnings 
FROM publishers 
WHERE id = YOUR_PUBLISHER_ID;
```

### Check Advertiser Spending
```sql
SELECT balance, total_spent 
FROM advertisers 
WHERE id = YOUR_ADVERTISER_ID;
```

### Check Impressions
```sql
SELECT 
    COUNT(*) as impressions,
    SUM(publisher_earning) as publisher_earnings,
    SUM(admin_profit) as admin_profit
FROM impressions 
WHERE ad_unit_id = YOUR_AD_UNIT_ID
AND is_bot = false;
```

### Check Clicks
```sql
SELECT 
    COUNT(*) as clicks,
    SUM(publisher_earning) as publisher_earnings,
    SUM(admin_profit) as admin_profit
FROM clicks 
WHERE ad_unit_id = YOUR_AD_UNIT_ID
AND is_fraud = false
AND is_bot = false;
```

## 🎨 Customization

### Change API URL
Both pages allow you to change the API URL in the config panel. This is useful for:
- Testing on different environments
- Testing with production API
- Testing with staging server

### Add More Ad Placements
You can easily add more ad containers by:
1. Adding HTML container divs
2. Adding input fields in config panel
3. Adding initialization code in JavaScript

### Styling
Both pages use clean, modern CSS that you can customize to match your needs.

## 💡 Tips

1. **Use Multiple Browsers**: Test in Chrome, Firefox, Safari
2. **Test Different Devices**: Use browser dev tools to simulate mobile
3. **Check Console**: Always keep browser console open during testing
4. **Verify Database**: Check database after each test to verify tracking
5. **Test Different Ad Types**: Try banner, square, and vertical ads
6. **Test Different Pricing Models**: Test CPM, CPC, and CPA campaigns

## 🚀 Next Steps

1. Test with real ad creatives
2. Test with multiple campaigns
3. Test targeting filters
4. Test frequency capping
5. Test conversion tracking (CPA)
6. Test on actual mobile devices

---

**Happy Testing!** These pages simulate real publisher sites, so you can test exactly how ads will work on actual websites! 🎉

