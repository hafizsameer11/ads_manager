# Testing Guide - Ads Network Platform

## Quick Start Testing

### 1. Start the Laravel Server

```bash
cd /Users/macbookpro/ads_manager
php artisan serve
```

The server will start at `http://localhost:8000`

### 2. Open Test Page

Open your browser and navigate to:
```
http://localhost:8000/test-ads.html
```

## Testing Workflow

### Step 1: Create Test Data

#### A. Create a Publisher Account
1. Go to `http://localhost:8000/register`
2. Register as a **Publisher**
3. Login as admin and approve the publisher account

#### B. Create a Website
1. Login as publisher
2. Go to `/dashboard/publisher/sites`
3. Add a website (e.g., `example.com`)
4. Copy the verification code
5. Login as admin and approve the website

#### C. Create an Ad Unit
1. As publisher, go to the website detail page
2. Click "Create Ad Unit"
3. Choose type: **Banner**
4. Set size: **300x250**
5. Copy the **Unit Code** (16 characters)

#### D. Create an Advertiser Account
1. Register as an **Advertiser**
2. Login as admin and approve the advertiser

#### E. Create a Campaign
1. Login as advertiser
2. Go to `/dashboard/advertiser/create-campaign`
3. Fill in campaign details:
   - **Name**: Test Campaign
   - **Type**: Banner
   - **Pricing Model**: CPC (or CPM, CPA)
   - **Budget**: 100.00
   - **Bid Amount**: 0.10
   - **Target URL**: https://example.com
   - **Ad Content**: 
     ```json
     {
       "title": "Test Ad",
       "image_url": "https://via.placeholder.com/300x250",
       "description": "Test ad description"
     }
     ```
4. Submit and wait for admin approval
5. Login as admin and approve the campaign

### Step 2: Test Ad Serving

1. Open `http://localhost:8000/test-ads.html`
2. Enter your **Unit Code** (16 characters)
3. Set API URL: `http://localhost:8000`
4. Click **"Load Ad"**
5. The ad should appear in the banner container
6. Check the console output for any errors

### Step 3: Test Tracking

#### Test Impression Tracking
1. Click **"Test Impression Tracking"** button
2. Check console for success message
3. Verify in database:
   ```sql
   SELECT * FROM impressions ORDER BY id DESC LIMIT 1;
   ```

#### Test Click Tracking
1. Click on the ad (or use **"Test Click Tracking"** button)
2. Check console for success message
3. Verify in database:
   ```sql
   SELECT * FROM clicks ORDER BY id DESC LIMIT 1;
   ```

### Step 4: Test Conversion Tracking (CPA)

#### Create a CPA Campaign
1. Create a new campaign with **Pricing Model: CPA**
2. Set **Bid Amount**: 5.00 (this is the conversion value)
3. Approve the campaign

#### Test Conversion
1. In test page, enter the **Campaign ID**
2. Set conversion type: `purchase`
3. Click **"Track Conversion"**
4. Verify in database:
   ```sql
   SELECT * FROM conversions ORDER BY id DESC LIMIT 1;
   ```

#### Test Conversion Pixel
1. Click **"Test Conversion Pixel"**
2. A 1x1 pixel image will be loaded
3. Conversion should be tracked automatically

## Testing Checklist

### ✅ Ad Serving
- [ ] Ad loads successfully
- [ ] Ad displays correct image/content
- [ ] Ad has correct dimensions
- [ ] Ad is clickable

### ✅ Impression Tracking
- [ ] Impression is tracked when ad is visible
- [ ] Impression appears in database
- [ ] Publisher balance increases (for CPM campaigns)
- [ ] Advertiser balance decreases

### ✅ Click Tracking
- [ ] Click is tracked when ad is clicked
- [ ] Click appears in database
- [ ] Publisher balance increases (for CPC campaigns)
- [ ] Advertiser balance decreases
- [ ] Target URL opens correctly

### ✅ Conversion Tracking (CPA)
- [ ] Conversion is tracked via API
- [ ] Conversion is tracked via pixel
- [ ] Conversion appears in database
- [ ] Publisher balance increases
- [ ] Advertiser balance decreases
- [ ] Postback URL is called (if configured)

### ✅ Fraud Detection
- [ ] Bot impressions are not counted
- [ ] Click rate limiting works
- [ ] VPN/Proxy detection works (check logs)

### ✅ Email Functionality
- [ ] Contact form sends email
- [ ] Abuse report sends email
- [ ] DMCA report sends email
- [ ] Forms save to database

### ✅ Deposit Approval
- [ ] Advertiser can request deposit
- [ ] Admin can see pending deposits
- [ ] Admin can approve deposit
- [ ] Advertiser balance updates
- [ ] Referral earnings processed (if applicable)

## Database Queries for Testing

### Check Ad Unit
```sql
SELECT * FROM ad_units WHERE unit_code = 'YOUR_UNIT_CODE';
```

### Check Campaign
```sql
SELECT * FROM campaigns WHERE id = YOUR_CAMPAIGN_ID;
```

### Check Impressions
```sql
SELECT COUNT(*) as total, 
       SUM(publisher_earning) as publisher_earnings,
       SUM(admin_profit) as admin_profit
FROM impressions 
WHERE ad_unit_id = YOUR_AD_UNIT_ID;
```

### Check Clicks
```sql
SELECT COUNT(*) as total,
       SUM(publisher_earning) as publisher_earnings,
       SUM(admin_profit) as admin_profit
FROM clicks 
WHERE ad_unit_id = YOUR_AD_UNIT_ID 
AND is_fraud = false;
```

### Check Conversions
```sql
SELECT * FROM conversions 
WHERE campaign_id = YOUR_CAMPAIGN_ID 
ORDER BY created_at DESC;
```

### Check Publisher Balance
```sql
SELECT balance, total_earnings, pending_balance 
FROM publishers 
WHERE id = YOUR_PUBLISHER_ID;
```

### Check Advertiser Balance
```sql
SELECT balance, total_spent 
FROM advertisers 
WHERE id = YOUR_ADVERTISER_ID;
```

## Common Issues & Solutions

### Issue: Ad Not Loading
**Solutions:**
1. Check unit code is correct (16 characters)
2. Verify ad unit status is `active`
3. Verify website status is `approved`
4. Verify campaign is `active` and `approved`
5. Check browser console for errors
6. Verify API URL is correct

### Issue: Impression Not Tracking
**Solutions:**
1. Check ad is 50% visible in viewport
2. Verify browser console for API errors
3. Check CORS settings in `config/cors.php`
4. Verify impression rate limits not exceeded

### Issue: Click Not Tracking
**Solutions:**
1. Check click rate limits
2. Verify fraud detection not blocking
3. Check browser console for errors
4. Verify campaign has sufficient balance

### Issue: Conversion Not Tracking
**Solutions:**
1. Verify campaign pricing model is `CPA`
2. Check campaign is active
3. Verify advertiser has sufficient balance
4. Check conversion API endpoint is accessible

## API Testing with cURL

### Test Ad Serve
```bash
curl http://localhost:8000/api/ad/YOUR_UNIT_CODE
```

### Test Impression
```bash
curl -X POST http://localhost:8000/api/ad/impression \
  -H "Content-Type: application/json" \
  -d '{
    "campaign_id": 1,
    "ad_unit_id": 1,
    "impression_id": "test_123",
    "visitor_info": {}
  }'
```

### Test Click
```bash
curl -X POST http://localhost:8000/api/ad/click \
  -H "Content-Type: application/json" \
  -d '{
    "campaign_id": 1,
    "ad_unit_id": 1,
    "impression_id": "test_123",
    "visitor_info": {}
  }'
```

### Test Conversion
```bash
curl -X POST http://localhost:8000/api/conversion/track \
  -H "Content-Type: application/json" \
  -d '{
    "campaign_id": 1,
    "conversion_type": "purchase",
    "conversion_value": 10.00
  }'
```

## Browser Console Testing

Open browser console (F12) and test directly:

```javascript
// Set API URL
window.ADS_NETWORK_API_URL = 'http://localhost:8000';
window.ADS_NETWORK_DEBUG = true;

// Initialize ad
const adManager = window.AdsNetwork.init('YOUR_UNIT_CODE', '#container', {
    type: 'banner'
});

// Check ad manager
console.log(adManager);
```

## Next Steps

1. Test all ad types (banner, popup, popunder)
2. Test different pricing models (CPM, CPC, CPA)
3. Test targeting filters
4. Test frequency capping
5. Test fraud detection
6. Test email notifications
7. Test deposit/withdrawal workflows

---

**Happy Testing!** 🚀

