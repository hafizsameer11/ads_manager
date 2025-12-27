<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdScriptController extends Controller
{
    /**
     * Serve the ads network JavaScript SDK.
     * This allows publishers to include: <script src="https://yourdomain.com/js/ads-network.js"></script>
     *
     * @param Request $request
     * @return Response
     */
    public function serveScript(Request $request)
    {
        $scriptPath = public_path('js/ads-network.js');
        
        if (!file_exists($scriptPath)) {
            abort(404, 'Ad script not found');
        }
        
        $script = file_get_contents($scriptPath);
        
        // Replace API URL placeholder with actual API URL
        $apiUrl = config('app.url');
        $script = str_replace('{{API_URL}}', $apiUrl, $script);
        
        return response($script, 200)
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
    }
}

