<?php

namespace App\Http\Controllers\Dashboard\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StripeController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->middleware('auth');
        $this->middleware('role:advertiser');
        $this->stripeService = $stripeService;
    }

    /**
     * Create Stripe checkout session and redirect to Stripe.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
        ]);

        $user = Auth::user();
        $advertiser = $user->advertiser;

        if (!$advertiser) {
            return redirect()->route('dashboard.advertiser.billing')
                ->withErrors(['error' => 'Advertiser profile not found.']);
        }

        if ($advertiser->status !== 'approved') {
            return redirect()->route('dashboard.advertiser.billing')
                ->withErrors(['error' => 'Your account must be approved to make deposits.']);
        }

        // Check if Stripe is enabled
        if (!Setting::get('stripe_enabled', false)) {
            return redirect()->route('dashboard.advertiser.billing')
                ->withErrors(['error' => 'Stripe payment is currently disabled.']);
        }

        try {
            $session = $this->stripeService->createCheckoutSession($advertiser, $request->amount);
            
            return redirect($session->url);
        } catch (\Exception $e) {
            \Log::error('Stripe checkout session creation failed: ' . $e->getMessage());
            return redirect()->route('dashboard.advertiser.billing')
                ->withErrors(['error' => 'Failed to create payment session: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle successful Stripe payment.
     */
    public function success(Request $request)
    {
        $transactionId = $request->query('transaction_id');
        $sessionId = $request->query('session_id');

        if ($transactionId) {
            $transaction = Transaction::find($transactionId);
            
            if ($transaction && $transaction->status === 'completed') {
                return redirect()->route('dashboard.advertiser.billing')
                    ->with('success', 'Payment completed successfully! Your balance has been updated.');
            }
        }

        return redirect()->route('dashboard.advertiser.billing')
            ->with('info', 'Payment is being processed. Your balance will be updated shortly.');
    }

    /**
     * Handle cancelled Stripe payment.
     */
    public function cancel(Request $request)
    {
        return redirect()->route('dashboard.advertiser.billing')
            ->with('info', 'Payment was cancelled. You can try again when ready.');
    }
}

