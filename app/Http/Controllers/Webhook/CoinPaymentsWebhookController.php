<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\CoinPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CoinPaymentsWebhookController extends Controller
{
    protected $coinPaymentsService;

    public function __construct(CoinPaymentsService $coinPaymentsService)
    {
        $this->coinPaymentsService = $coinPaymentsService;
        $this->middleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    }

    /**
     * Handle CoinPayments IPN (Instant Payment Notification).
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        // CoinPayments sends HMAC in the payload, not header
        $hmac = $request->input('hmac');

        if (!$hmac) {
            Log::error('CoinPayments IPN: Missing HMAC in payload');
            return response('Missing HMAC', 400);
        }

        try {
            $isValid = $this->coinPaymentsService->verifyIPN($payload, $hmac);
            
            if (!$isValid) {
                Log::error('CoinPayments IPN: Invalid HMAC signature');
                return response('Invalid signature', 400);
            }

            $this->coinPaymentsService->handleIPN($payload);
            
            // CoinPayments expects plain text response
            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('CoinPayments IPN error: ' . $e->getMessage());
            return response('Error: ' . $e->getMessage(), 400);
        }
    }
}

