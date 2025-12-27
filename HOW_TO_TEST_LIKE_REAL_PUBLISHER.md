# How to Test Like a Real Publisher

## 🎯 Overview

This guide shows you how to test the ad network **exactly like a real publisher would** - by copying the embed code from the dashboard and pasting it directly into HTML.

## 📋 Step-by-Step Testing Process

### Step 1: Get Your Embed Code (Just Like a Real Publisher)

1. **Login as Publisher**
   ```
   http://localhost:8000/login
   ```

2. **Navigate to Your Website**
   ```
   Go to: /dashboard/publisher/sites
   Click on your website
   ```

3. **Go to Ad Units**
   ```
   Click "Ad Units" tab
   Or go to: /dashboard/publisher/sites/{website}/ad-units
   ```

4. **View Ad Unit Details**
   ```
   Click on an ad unit
   Or go to: /dashboard/publisher/ad-units/{id}
   ```

5. **Copy the Embed Code**
   - Scroll to "Embed Code" section
   - You'll see code like this:
   ```html
   <!-- Ads Network Ad Unit: Your Ad Name -->
   <div id="ads-network-ABC123XYZ4567890" style="width: 300px; height: 250px; margin: 0 auto;"></div>
   <script>
   (function() {
       if (!window.AdsNetwork) {
           var script = document.createElement('script');
           script.src = 'http://localhost:8000/js/ads-network.js';
           script.async = true;
           document.head.appendChild(script);
           script.onload = function() {
               if (window.AdsNetwork) {
                   window.AdsNetwork.init('ABC123XYZ4567890', '#ads-network-ABC123XYZ4567890', {type: 'banner'});
               }
           };
       } else {
           window.AdsNetwork.init('ABC123XYZ4567890', '#ads-network-ABC123XYZ4567890', {type: 'banner'});
       }
   })();
   </script>
   ```
   - Click "Copy Embed Code" button
   - The code is now in your clipboard

### Step 2: Paste Into Test HTML File

1. **Open Test File**
   ```
   Open: public/publisher-site-example.html
   Or: public/real-publisher-example.html
   ```

2. **Find the Embed Code Placeholders**
   - Look for comments like: `<!-- AD EMBED CODE #1 -->`
   - Or placeholders like: `YOUR_UNIT_CODE_1` or `UNIT_CODE_HERE`

3. **Replace with Your Actual Code**
   - Paste your copied embed code
   - Replace the placeholder code
   - Make sure the unit code matches (16 characters)

4. **Update API URL (if needed)**
   - If your server is not on `localhost:8000`
   - Replace `http://localhost:8000` with your actual URL

5. **Save the File**

### Step 3: Test in Browser

1. **Start Laravel Server**
   ```bash
   php artisan serve
   ```

2. **Open Test Page**
   ```
   http://localhost:8000/publisher-site-example.html
   Or: http://localhost:8000/real-publisher-example.html
   ```

3. **Verify Ads Load**
   - Ads should appear automatically
   - Check browser console (F12) for any errors
   - Scroll page to trigger impressions
   - Click ads to test click tracking

## 📄 Test Files Provided

### 1. `publisher-site-example.html`
- Complete blog layout
- Multiple ad placements with comments showing where to paste code
- Shows exactly where publishers would place ads
- Includes instructions in HTML comments

### 2. `real-publisher-example.html`
- Simpler news site layout
- Shows embed code in action
- Minimal example for quick testing

## 🔍 What Real Publishers See

When a publisher views an ad unit in the dashboard, they see:

```
┌─────────────────────────────────────────┐
│ Embed Code                              │
├─────────────────────────────────────────┤
│ Copy and paste this code into your      │
│ website to display the ad unit.         │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ <!-- Ads Network Ad Unit: ... -->   │ │
│ │ <div id="ads-network-..."></div>    │ │
│ │ <script>...</script>                 │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ [Copy Embed Code] Button                │
└─────────────────────────────────────────┘
```

They copy this code and paste it into their HTML - **that's it!**

## ✅ Testing Checklist

### Before Testing
- [ ] Publisher account created and approved
- [ ] Website added and approved
- [ ] Ad units created (at least 2-3 different sizes)
- [ ] Advertiser account created and approved
- [ ] Campaign created and approved
- [ ] Campaign has budget and is active

### During Testing
- [ ] Copy embed code from dashboard
- [ ] Paste into test HTML file
- [ ] Replace unit code placeholders
- [ ] Save file
- [ ] Open in browser
- [ ] Verify ads load
- [ ] Check browser console for errors
- [ ] Scroll to trigger impressions
- [ ] Click ads to test clicks
- [ ] Verify tracking in database

### After Testing
- [ ] Check impressions table
- [ ] Check clicks table
- [ ] Verify publisher balance updated
- [ ] Verify advertiser balance decreased
- [ ] Check campaign stats updated

## 🎨 Example: Real Integration

Here's what a publisher's HTML might look like:

```html
<!DOCTYPE html>
<html>
<head>
    <title>My Blog</title>
</head>
<body>
    <h1>My Article</h1>
    <p>Some content here...</p>
    
    <!-- Publisher pastes embed code here -->
    <!-- Ads Network Ad Unit: Banner Top -->
    <div id="ads-network-ABC123XYZ4567890" style="width: 728px; height: 90px; margin: 0 auto;"></div>
    <script>
    (function() {
        if (!window.AdsNetwork) {
            var script = document.createElement('script');
            script.src = 'http://localhost:8000/js/ads-network.js';
            script.async = true;
            document.head.appendChild(script);
            script.onload = function() {
                if (window.AdsNetwork) {
                    window.AdsNetwork.init('ABC123XYZ4567890', '#ads-network-ABC123XYZ4567890', {type: 'banner'});
                }
            };
        } else {
            window.AdsNetwork.init('ABC123XYZ4567890', '#ads-network-ABC123XYZ4567890', {type: 'banner'});
        }
    })();
    </script>
    
    <p>More content here...</p>
</body>
</html>
```

That's it! No configuration panels, no special setup - just copy and paste.

## 🚀 Quick Test Workflow

1. **Create Test Data** (one time)
   ```
   Publisher → Website → Ad Units → Campaign
   ```

2. **Get Embed Code**
   ```
   Dashboard → Ad Unit → Copy Embed Code
   ```

3. **Paste in HTML**
   ```
   Open test file → Replace placeholder → Save
   ```

4. **Test**
   ```
   Open in browser → Check console → Verify ads
   ```

## 💡 Pro Tips

1. **Use Multiple Ad Units**: Test different sizes and positions
2. **Test Different Campaigns**: Try CPM, CPC, and CPA campaigns
3. **Check Console**: Always keep browser console open
4. **Verify Database**: Check database after each test
5. **Test Mobile**: Use browser dev tools to test mobile view
6. **Test Multiple Browsers**: Chrome, Firefox, Safari

## 🔧 Troubleshooting

**Ads Not Loading?**
- Check unit code is correct (16 characters)
- Verify API URL is correct
- Check browser console for errors
- Verify ad unit is active
- Verify campaign is approved

**Impressions Not Tracking?**
- Scroll to make ad visible (50% viewport)
- Check browser console
- Verify CORS settings
- Check API endpoint accessibility

**Clicks Not Working?**
- Check click rate limits
- Verify fraud detection
- Check campaign balance
- Verify target URL

---

**This is exactly how real publishers integrate ads!** They copy the embed code from the dashboard and paste it into their HTML. No configuration needed! 🎉

