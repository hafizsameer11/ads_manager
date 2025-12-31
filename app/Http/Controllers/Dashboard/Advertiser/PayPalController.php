<?php

namespace App\Http\Controllers\Dashboard\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayPalController extends Controller
{
    protected $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->middleware('auth');
        $this->middleware('role:advertiser');
        $this->paypalService = $paypalService;
    }

    /**
     * Create PayPal payment and redirect to PayPal.
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

        // Check if PayPal is enabled
        if (!Setting::get('paypal_enabled', false)) {
            return redirect()->route('dashboard.advertiser.billing')
                ->withErrors(['error' => 'PayPal payment is currently disabled.']);
        }

        try {
            $result = $this->paypalService->createPayment($advertiser, $request->amount);
            
            if ($result['success']) {
                return redirect($result['approval_url']);
            }
        } catch (\Exception $e) {
            \Log::error('PayPal checkout failed: ' . $e->getMessage());
            return redirect()->route('dashboard.advertiser.billing')
                ->withErrors(['error' => 'Failed to create payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle successful PayPal payment.
     */
    public function success(Request $request)
    {
        $transactionId = $request->query('transaction_id');
        $paymentId = $request->query('paymentId');
        $payerId = $request->query('PayerID');

        if ($paymentId && $payerId && $transactionId) {
            try {
                $success = $this->paypalService->executePayment($paymentId, $payerId, $transactionId);
                
                if ($success) {
                    return redirect()->route('dashboard.advertiser.billing')
                        ->with('success', 'Payment completed successfully! Your balance has been updated.');
                }
            } catch (\Exception $e) {
                \Log::error('PayPal payment execution failed: ' . $e->getMessage());
            }
        }

        $transaction = Transaction::find($transactionId);
        if ($transaction && $transaction->status === 'completed') {
            return redirect()->route('dashboard.advertiser.billing')
                ->with('success', 'Payment completed successfully! Your balance has been updated.');
        }

        return redirect()->route('dashboard.advertiser.billing')
            ->with('info', 'Payment is being processed. Your balance will be updated shortly.');
    }

    /**
     * Handle cancelled PayPal payment.
     */
    public function cancel(Request $request)
    {
        return redirect()->route('dashboard.advertiser.billing')
            ->with('info', 'Payment was cancelled. You can try again when ready.');
    }
}


