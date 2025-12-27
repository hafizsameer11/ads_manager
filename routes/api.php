<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdServerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Ad Server API Routes (Public - no authentication required)
Route::prefix('ad')->group(function () {
    // Serve ad by unit code
    Route::get('/{unitCode}', [AdServerController::class, 'serveAd'])->name('api.ad.serve');
    
    // Track impression
    Route::post('/impression', [AdServerController::class, 'trackImpression'])->name('api.ad.impression');
    
    // Track click
    Route::post('/click', [AdServerController::class, 'trackClick'])->name('api.ad.click');
    
    // Get ad unit stats (requires authentication)
    Route::middleware('auth:sanctum')->get('/stats/{unitCode}', [AdServerController::class, 'getStats'])->name('api.ad.stats');
});

// Conversion Tracking API Routes (Public - no authentication required)
Route::prefix('conversion')->group(function () {
    // Track conversion (POST)
    Route::post('/track', [\App\Http\Controllers\Api\ConversionController::class, 'track'])->name('api.conversion.track');
    
    // Conversion pixel (GET - 1x1 image)
    Route::get('/pixel/{campaignId}', [\App\Http\Controllers\Api\ConversionController::class, 'pixel'])->name('api.conversion.pixel');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
