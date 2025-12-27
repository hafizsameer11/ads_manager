# Complete Publisher Flow Testing Guide

## Prerequisites

1. **Run Migrations**
   ```bash
   php artisan migrate
   ```

2. **Create Test Accounts**
   - Admin account (if not exists)
   - Publisher account (register or create via seeder)

3. **Start Development Server**
   ```bash
   php artisan serve
   ```

---

## Testing Flow

### Step 1: Test Publisher Registration/Login

**Action:**
- Go to `/register`
- Register as Publisher
- Or login if account exists

**Expected:**
- Account created with `status = pending`
- Redirected to pending approval page

---

### Step 2: Test Publisher Approval (Admin Side)

**Action (as Admin):**
- Login as admin
- Go to `/dashboard/admin/users`
- Find the publisher
- Click "Approve"

**Expected:**
- Publisher status changes to `approved`
- Publisher can now access dashboard

---

### Step 3: Test Adding Website

**Action (as Publisher):**
- Login as publisher
- Go to `/dashboard/publisher/sites`
- Click "Add New Website"
- Fill form:
  - **Name:** Test Website
  - **Domain:** example.com (or use localhost for testing)
  - **Verification Method:** Meta Tag
- Submit

**Expected:**
- Website created with `status = pending`
- Verification code generated
- Success message shown
- Website appears in list with "Pending" badge

**Check Database:**
```sql
SELECT * FROM websites WHERE domain = 'example.com';
-- Should show: status='pending', verification_code is set
```

---

### Step 4: Test Website Verification (Meta Tag Method)

**Option A: Test with Real Website**
- Add meta tag to your website's `<head>`:
  ```html
  <meta name="ads-network-verification" content="YOUR_VERIFICATION_CODE">
  ```
- Go to website detail page
- Click "Verify Website" button

**Option B: Test Locally (Mock)**
- Create a simple HTML file:
  ```html
  <!DOCTYPE html>
  <html>
  <head>
      <meta name="ads-network-verification" content="YOUR_VERIFICATION_CODE">
      <title>Test Site</title>
  </head>
  <body>
      <h1>Test Website</h1>
  </body>
  </html>
  ```
- Host it locally or on a test server
- Update website domain to match
- Click "Verify Website"

**Expected:**
- Verification service checks website
- If meta tag found: `verification_status = verified`, `verified_at` set
- Success message: "Website verified successfully!"
- If not found: Error message shown

**Check Database:**
```sql
SELECT verification_status, verified_at FROM websites WHERE id = YOUR_WEBSITE_ID;
-- Should show: verification_status='verified'
```

---

### Step 5: Test Admin Website Approval

**Action (as Admin):**
- Login as admin
- Go to `/dashboard/admin/websites`
- Find the pending website
- Click "Approve"

**Expected:**
- Website status: `pending` → `approved`
- `approved_at` timestamp set
- Publisher receives notification
- Website shows "Approved" badge

**Check Database:**
```sql
SELECT status, approved_at FROM websites WHERE id = YOUR_WEBSITE_ID;
-- Should show: status='approved', approved_at is set
```

---

### Step 6: Test Creating Ad Unit

**Action (as Publisher):**
- Go to website detail page
- Click "Ad Units" or "Create Ad Unit"
- Fill form:
  - **Name:** Test Banner
  - **Type:** Banner
  - **Size:** 300x250
- Submit

**Expected:**
- Ad unit created
- `unit_code` generated (16 characters)
- Status = `active`
- Width = 300, Height = 250
- Success message

**Check Database:**
```sql
SELECT * FROM ad_units WHERE website_id = YOUR_WEBSITE_ID;
-- Should show: unit_code, status='active', width=300, height=250
```

**Test Rejection:**
- Try creating ad unit for a `pending` website
- Should be blocked with error: "Website must be approved before creating ad units"

---

### Step 7: Test Getting Embed Code

**Action (as Publisher):**
- Go to ad unit detail page
- View "Embed Code" section

**Expected:**
- Embed code displayed
- Contains:
  - Container div with ID
  - JavaScript initialization code
  - SDK loading script

**Copy the embed code** - you'll use it in next step

---

### Step 8: Test Embedding Ad on Website

**Action:**
- Create a test HTML page:
  ```html
  <!DOCTYPE html>
  <html>
  <head>
      <title>Test Ad Page</title>
  </head>
  <body>
      <h1>My Test Website</h1>
      
      <!-- Paste embed code here -->
      <div id="ads-network-UNIT_CODE" style="width: 300px; height: 250px; margin: 0 auto;"></div>
      <script>
      (function() {
          if (!window.AdsNetwork) {
              var script = document.createElement('script');
              script.src = 'http://localhost:8000/js/ads-network.js';
              script.async = true;
              document.head.appendChild(script);
              script.onload = function() {
                  if (window.AdsNetwork) {
                      window.AdsNetwork.init('UNIT_CODE', '#ads-network-UNIT_CODE', {type: 'banner'});
                  }
              };
          } else {
              window.AdsNetwork.init('UNIT_CODE', '#ads-network-UNIT_CODE', {type: 'banner'});
          }
      })();
      </script>
  </body>
  </html>
  ```
- Replace `UNIT_CODE` with actual unit code
- Replace `http://localhost:8000` with your app URL
- Open in browser

**Expected:**
- SDK script loads
- Ad container appears
- JavaScript makes API call to `/api/ad/{unitCode}`

**Check Browser Console (F12):**
- Should see network request to `/api/ad/{unitCode}`
- Check response

---

### Step 9: Test Ad Serving API

**Action:**
- Open browser console on test page
- Check Network tab
- Look for request to `/api/ad/{unitCode}`

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "campaign_id": 1,
    "ad_unit_id": 1,
    "type": "banner",
    "target_url": "https://advertiser.com",
    "title": "Ad Title",
    "image_url": "https://example.com/ad.jpg",
    "width": 300,
    "height": 250
  }
}
```

**If No Ad Available:**
- Response: `{"success": false, "message": "No ad available"}`
- This is normal if no active campaigns exist

---

### Step 10: Test Creating Campaign (For Ad Serving)

**Action (as Advertiser or Admin):**
- Create an advertiser account
- Create a campaign:
  - **Name:** Test Campaign
  - **Type:** Banner
  - **Target URL:** https://example.com
  - **Ad Content:** 
    ```json
    {
      "title": "Test Ad",
      "image_url": "https://via.placeholder.com/300x250",
      "description": "Test ad description"
    }
    ```
  - **Budget:** 100
  - **Bid Amount:** 0.10
  - **Status:** Active
- Admin approves campaign

**Expected:**
- Campaign created and approved
- Now ads should be available

---

### Step 11: Test Ad Display

**Action:**
- Refresh test HTML page
- Check if ad displays

**Expected:**
- Ad image/banner appears in container
- Ad is clickable
- Console shows no errors

---

### Step 12: Test Impression Tracking

**Action:**
- View test page with ad
- Wait for ad to be visible (50% in viewport)
- Check browser console Network tab

**Expected:**
- Request to `POST /api/ad/impression`
- Request body:
  ```json
  {
    "campaign_id": 1,
    "ad_unit_id": 1,
    "impression_id": "imp_...",
    "visitor_info": {...}
  }
  ```
- Response: `{"success": true, "impression_id": 123}`

**Check Database:**
```sql
SELECT * FROM impressions WHERE ad_unit_id = YOUR_AD_UNIT_ID;
-- Should show new impression record
```

**Check Publisher Balance:**
```sql
SELECT balance, total_earnings FROM publishers WHERE id = YOUR_PUBLISHER_ID;
-- Balance should increase (if CPM campaign)
```

---

### Step 13: Test Click Tracking

**Action:**
- Click on the ad
- Check browser console Network tab

**Expected:**
- Request to `POST /api/ad/click`
- Request body:
  ```json
  {
    "campaign_id": 1,
    "ad_unit_id": 1,
    "impression_id": "imp_...",
    "visitor_info": {...}
  }
  ```
- Response: `{"success": true, "click_id": 123, "target_url": "https://..."}`
- New tab/window opens with target URL

**Check Database:**
```sql
SELECT * FROM clicks WHERE ad_unit_id = YOUR_AD_UNIT_ID;
-- Should show new click record
```

**Check Publisher Balance:**
```sql
SELECT balance, total_earnings FROM publishers WHERE id = YOUR_PUBLISHER_ID;
-- Balance should increase (if CPC campaign)
```

---

### Step 14: Test Publisher Dashboard Stats

**Action (as Publisher):**
- Go to `/dashboard/publisher/statistics`
- Go to `/dashboard/publisher/earnings`

**Expected:**
- Statistics show impressions and clicks
- Earnings show balance and total earnings
- Data matches database records

---

### Step 15: Test Website Status Restrictions

**Test 1: Pending Website**
- Change website status to `pending` (via admin)
- Try to create ad unit → Should be blocked
- Try to view embed code → Should show warning

**Test 2: Rejected Website**
- Admin rejects website
- All ad units should be paused automatically
- Try to create new ad unit → Should be blocked
- Try to view embed code → Should show warning

**Test 3: Disabled Website**
- Admin disables website
- All ad units should be paused automatically
- Try to create new ad unit → Should be blocked

---

### Step 16: Test Auto-Approval Setting

**Action (as Admin):**
- Go to `/dashboard/admin/settings`
- Find "Publisher Website Approval Settings"
- Enable "Auto-approve Publisher Websites"
- Save

**Action (as Publisher):**
- Add a new website
- Submit

**Expected:**
- Website status = `approved` immediately
- `approved_at` timestamp set
- Can create ad units immediately

**Test Disable:**
- Disable auto-approval
- Add new website
- Should be `pending` (requires manual approval)

---

## Quick Test Checklist

- [ ] Publisher can register/login
- [ ] Admin can approve publisher
- [ ] Publisher can add website
- [ ] Website verification works (meta tag)
- [ ] Admin can approve website
- [ ] Publisher can create ad unit (only for approved websites)
- [ ] Embed code is generated correctly
- [ ] SDK script loads on test page
- [ ] Ad API returns ad data
- [ ] Ad displays correctly
- [ ] Impression tracking works
- [ ] Click tracking works
- [ ] Publisher balance updates
- [ ] Statistics show correct data
- [ ] Status restrictions work (pending/rejected/disabled)
- [ ] Auto-approval setting works

---

## Common Issues & Solutions

### Issue: Ad Not Showing
**Check:**
1. Website status = `approved`?
2. Ad unit status = `active`?
3. Campaign exists and is `active`?
4. Campaign has budget?
5. Browser console for errors?
6. CORS enabled? (check `config/cors.php`)

### Issue: Impression Not Tracking
**Check:**
1. Ad is 50% visible in viewport?
2. Browser console for API errors?
3. Network tab shows POST to `/api/ad/impression`?
4. API returns success?

### Issue: Verification Fails
**Check:**
1. Meta tag is in `<head>` section?
2. Verification code matches exactly?
3. Website is accessible (not behind firewall)?
4. HTTP vs HTTPS (use correct protocol)?

### Issue: Cannot Create Ad Unit
**Check:**
1. Website status = `approved`?
2. Backend validation error in logs?
3. Check `AdUnitController::create()` method

---

## Testing Tools

### Browser DevTools
- **Console:** Check JavaScript errors
- **Network:** Monitor API calls
- **Elements:** Inspect ad container

### Database Queries
```sql
-- Check website status
SELECT id, domain, status, verification_status, approved_at FROM websites;

-- Check ad units
SELECT id, unit_code, status, website_id FROM ad_units;

-- Check impressions
SELECT COUNT(*) as impressions, SUM(publisher_earning) as earnings 
FROM impressions 
WHERE ad_unit_id = YOUR_AD_UNIT_ID;

-- Check clicks
SELECT COUNT(*) as clicks, SUM(publisher_earning) as earnings 
FROM clicks 
WHERE ad_unit_id = YOUR_AD_UNIT_ID;

-- Check publisher balance
SELECT balance, total_earnings, pending_balance 
FROM publishers 
WHERE id = YOUR_PUBLISHER_ID;
```

### API Testing (Postman/cURL)
```bash
# Test ad serving
curl http://localhost:8000/api/ad/UNIT_CODE

# Test impression tracking
curl -X POST http://localhost:8000/api/ad/impression \
  -H "Content-Type: application/json" \
  -d '{
    "campaign_id": 1,
    "ad_unit_id": 1,
    "impression_id": "test_123"
  }'

# Test click tracking
curl -X POST http://localhost:8000/api/ad/click \
  -H "Content-Type: application/json" \
  -d '{
    "campaign_id": 1,
    "ad_unit_id": 1,
    "impression_id": "test_123"
  }'
```

---

## End-to-End Test Scenario

1. **Setup:**
   - Create publisher account
   - Admin approves publisher
   - Create advertiser account
   - Create and approve campaign

2. **Publisher Flow:**
   - Add website → Verify → Admin approves
   - Create ad unit
   - Get embed code
   - Add to test page

3. **Ad Serving:**
   - Visit test page
   - Ad loads and displays
   - Impression tracked
   - Click ad → Click tracked
   - Check publisher earnings updated

4. **Verification:**
   - Check database for records
   - Check publisher balance
   - Check statistics page

---

**Happy Testing!** 🚀


