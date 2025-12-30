<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    protected $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
        $this->middleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    }

    /**
     * Handle PayPal webhook events.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        try {
            $this->paypalService->verifyWebhook($payload);
            
            // Handle different webhook event types
            $eventType = $payload['event_type'] ?? '';
            
            if ($eventType === 'PAYMENT.SALE.COMPLETED') {
                // Payment completed - handle accordingly
                Log::info('PayPal webhook: Payment completed', $payload);
            }
            
            return response()->json(['received' => true], 200);
        } catch (\Exception $e) {
            Log::error('PayPal webhook error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

