<?php

namespace App\Http\Controllers\Dashboard\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\CoinPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoinPaymentsController extends Controller
{
    protected $coinPaymentsService;

    public function __construct(CoinPaymentsService $coinPaymentsService)
    {
        $this->middleware('auth');
        $this->middleware('role:advertiser');
        $this->coinPaymentsService = $coinPaymentsService;
    }

    /**
     * Create CoinPayments transaction and redirect to payment page.
     */
    public function checkout(Request $request)
    {
        // Get deposit limits from settings
        $paymentService = app(\App\Services\PaymentService::class);
        $minimumDeposit = $paymentService->getMinimumDeposit();
        $maximumDeposit = $paymentService->getMaximumDeposit();

        $request->validate([
            'amount' => "required|numeric|min:{$minimumDeposit}|max:{$maximumDeposit}",
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

        // Check if CoinPayments is enabled
        if (!Setting::get('coinpayments_enabled', false)) {
            return redirect()->route('dashboard.advertiser.billing')
                ->withErrors(['error' => 'CoinPayments payment is currently disabled.']);
        }

        try {
            $result = $this->coinPaymentsService->createTransaction($advertiser, $request->amount);
            
            if ($result['success']) {
                // Redirect to CoinPayments payment page
                return redirect($result['checkout_url'] ?? $result['status_url']);
            }
        } catch (\Exception $e) {
            \Log::error('CoinPayments checkout failed: ' . $e->getMessage());
            return redirect()->route('dashboard.advertiser.billing')
                ->withErrors(['error' => 'Failed to create payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle successful CoinPayments payment.
     */
    public function success(Request $request)
    {
        $transactionId = $request->query('transaction_id');

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
     * Handle cancelled CoinPayments payment.
     */
    public function cancel(Request $request)
    {
        return redirect()->route('dashboard.advertiser.billing')
            ->with('info', 'Payment was cancelled. You can try again when ready.');
    }
}



