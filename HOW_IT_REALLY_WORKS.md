# How Ads Network Really Works - No Configuration Needed!

## 🎯 The Real Publisher Experience

**Publishers don't configure API URLs!** The SDK automatically detects everything from the script source.

## 🔍 How Auto-Detection Works

### Step 1: Publisher Gets Embed Code
When a publisher views an ad unit in the dashboard, they see embed code like this:

```html
<!-- Ads Network Ad Unit: Banner Top -->
<div id="ads-network-ABC123XYZ4567890" style="width: 728px; height: 90px; margin: 0 auto;"></div>
<script>
(function() {
    if (!window.AdsNetwork) {
        var script = document.createElement('script');
        script.src = 'https://yourdomain.com/js/ads-network.js';
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

### Step 2: Publisher Pastes Code
The publisher simply copies this code and pastes it into their HTML. **That's it!**

### Step 3: SDK Auto-Detects API URL
When the script loads from `https://yourdomain.com/js/ads-network.js`, the SDK:

1. **Detects the script source**: `https://yourdomain.com/js/ads-network.js`
2. **Extracts base URL**: `https://yourdomain.com`
3. **Uses it for API calls**: 
   - `https://yourdomain.com/api/ad/{unitCode}`
   - `https://yourdomain.com/api/ad/impression`
   - `https://yourdomain.com/api/ad/click`

**No configuration needed!** It just works automatically.

## 📋 How the SDK Detects API URL

The SDK uses this priority order:

1. **Explicit Configuration** (for testing/debugging only):
   ```javascript
   window.ADS_NETWORK_API_URL = 'http://custom-url.com';
   ```

2. **Auto-Detection from Script Source** (automatic):
   ```javascript
   // SDK finds the script tag that loaded it
   // Extracts base URL from: https://yourdomain.com/js/ads-network.js
   // Result: https://yourdomain.com
   ```

3. **Fallback to Current Page** (if detection fails):
   ```javascript
   window.location.origin
   ```

## ✅ Real-World Example

### Publisher's Website (example.com)

```html
<!DOCTYPE html>
<html>
<head>
    <title>My Blog</title>
</head>
<body>
    <h1>My Article</h1>
    
    <!-- Publisher pastes embed code here -->
    <div id="ads-network-ABC123XYZ4567890" style="width: 728px; height: 90px; margin: 0 auto;"></div>
    <script>
    (function() {
        if (!window.AdsNetwork) {
            var script = document.createElement('script');
            script.src = 'https://adsnetwork.com/js/ads-network.js';
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
    
    <p>More content...</p>
</body>
</html>
```

### What Happens:

1. **Script loads**: `https://adsnetwork.com/js/ads-network.js`
2. **SDK detects**: Base URL = `https://adsnetwork.com`
3. **API calls use**: `https://adsnetwork.com/api/ad/ABC123XYZ4567890`
4. **Works automatically** - no configuration!

## 🔧 For Testing (Local Development)

When testing locally, the embed code will have:
```html
script.src = 'http://localhost:8000/js/ads-network.js';
```

The SDK automatically detects `http://localhost:8000` and uses it for API calls.

**No need to manually set API URL!**

## 📝 Embed Code Generation

The embed code is generated in `AdUnit` model using `config('app.url')`:

```php
$baseUrl = config('app.url'); // e.g., https://adsnetwork.com
script.src = '{$baseUrl}/js/ads-network.js';
```

This ensures:
- Production: Uses your actual domain
- Staging: Uses staging domain
- Local: Uses localhost:8000

## 🎯 Key Points

1. **Publishers never configure API URLs** - SDK handles it automatically
2. **Embed code uses `config('app.url')`** - Always correct for the environment
3. **SDK auto-detects from script source** - Works even if script is loaded from CDN
4. **Multiple ads share same SDK** - Only loads once, detects once
5. **Works across domains** - Each domain uses its own API URL automatically

## 🚀 Testing

To test like a real publisher:

1. Get embed code from dashboard (it already has correct script URL)
2. Paste into HTML file
3. Replace unit code placeholders
4. Open in browser
5. **It just works!** No API URL configuration needed.

The SDK automatically:
- Detects API URL from script source
- Makes API calls to correct endpoint
- Handles all tracking automatically

---

**That's how real ad networks work!** Publishers just copy-paste code, and everything works automatically. 🎉

