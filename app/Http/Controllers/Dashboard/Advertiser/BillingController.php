<?php

namespace App\Http\Controllers\Dashboard\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\ManualPaymentAccount;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BillingController extends Controller
{
    /**
     * Display the billing page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $advertiser = $user->advertiser;
        
        if (!$advertiser) {
            return redirect()->route('dashboard.advertiser.home')->with('error', 'Advertiser profile not found.');
        }
        
        // Transactions
        $query = Transaction::where('transactionable_type', \App\Models\Advertiser::class)
            ->where('transactionable_id', $advertiser->id);
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $transactions = $query->latest()->paginate(20);
        
        // Get enabled manual payment accounts
        $manualPaymentAccounts = ManualPaymentAccount::enabled()->ordered()->get();
        
        // Get enabled payment gateways from admin settings
        $enabledPaymentGateways = Setting::get('payment_gateways', []);
        
        // Check if automatic payment gateways are enabled (they only need their specific enable setting)
        $stripeEnabled = Setting::get('stripe_enabled', false);
        $stripePublishableKey = $stripeEnabled ? Setting::get('stripe_publishable_key', '') : '';
        $paypalEnabled = Setting::get('paypal_enabled', false);
        $coinpaymentsEnabled = Setting::get('coinpayments_enabled', false);
        
        // Check which other payment methods are enabled
        $faucetpayEnabled = in_array('faucetpay', $enabledPaymentGateways);
        $bankSwiftEnabled = in_array('bank_swift', $enabledPaymentGateways);
        $wiseEnabled = in_array('wise', $enabledPaymentGateways);
        
        // Summary
        $summary = [
            'balance' => $advertiser->balance ?? 0,
            'total_spent' => $advertiser->total_spent ?? 0,
            'total_deposits' => Transaction::where('transactionable_type', \App\Models\Advertiser::class)
                ->where('transactionable_id', $advertiser->id)
                ->where('type', 'deposit')
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_deposits' => Transaction::where('transactionable_type', \App\Models\Advertiser::class)
                ->where('transactionable_id', $advertiser->id)
                ->where('type', 'deposit')
                ->where('status', 'pending')
                ->sum('amount'),
            'this_month_spend' => Transaction::where('transactionable_type', \App\Models\Advertiser::class)
                ->where('transactionable_id', $advertiser->id)
                ->where('type', 'campaign_spend')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
        ];
        
        // Get deposit limits from settings
        $paymentService = app(\App\Services\PaymentService::class);
        $minimumDeposit = $paymentService->getMinimumDeposit();
        $maximumDeposit = $paymentService->getMaximumDeposit();
        
        return view('dashboard.advertiser.billing', compact(
            'transactions', 
            'summary', 
            'manualPaymentAccounts', 
            'stripeEnabled', 
            'stripePublishableKey', 
            'paypalEnabled', 
            'coinpaymentsEnabled',
            'faucetpayEnabled',
            'bankSwiftEnabled',
            'wiseEnabled',
            'minimumDeposit',
            'maximumDeposit'
        ));
    }

    /**
     * Store a deposit request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Build allowed payment methods based on enabled gateways
        $allowedMethods = [];
        $enabledGateways = Setting::get('payment_gateways', []);
        
        // Automatic payment gateways only need their specific enable setting
        if (Setting::get('paypal_enabled', false)) {
            $allowedMethods[] = 'paypal';
        }
        if (Setting::get('coinpayments_enabled', false)) {
            $allowedMethods[] = 'coinpayment';
        }
        if (Setting::get('stripe_enabled', false)) {
            $allowedMethods[] = 'stripe';
        }
        if (in_array('faucetpay', $enabledGateways)) {
            $allowedMethods[] = 'faucetpay';
        }
        if (in_array('bank_swift', $enabledGateways)) {
            $allowedMethods[] = 'bank_swift';
        }
        if (in_array('wise', $enabledGateways)) {
            $allowedMethods[] = 'wise';
        }
        if (ManualPaymentAccount::enabled()->count() > 0) {
            $allowedMethods[] = 'manual';
        }

        // If no methods are enabled, fallback to prevent validation error
        if (empty($allowedMethods)) {
            $allowedMethods = ['manual']; // At least allow manual if nothing else
        }

        // Get deposit limits from settings
        $paymentService = app(\App\Services\PaymentService::class);
        $minimumDeposit = $paymentService->getMinimumDeposit();
        $maximumDeposit = $paymentService->getMaximumDeposit();

        $validationRules = [
            'amount' => "required|numeric|min:{$minimumDeposit}|max:{$maximumDeposit}",
            'payment_method' => 'required|in:' . implode(',', $allowedMethods),
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ];

        // If manual payment method, require screenshot and transaction ID
        if ($request->payment_method === 'manual') {
            $validationRules['manual_payment_account_id'] = 'required|exists:manual_payment_accounts,id';
            $validationRules['transaction_id'] = 'required|string|max:255';
            $validationRules['payment_screenshot'] = 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120'; // 5MB max
        }

        $request->validate($validationRules);

        $user = Auth::user();
        $advertiser = $user->advertiser;

        if (!$advertiser) {
            return back()->withErrors(['error' => 'Advertiser profile not found.']);
        }

        if ($user->is_active !== 1) {
            return back()->withErrors(['error' => 'Your account must be approved to make deposits.']);
        }

        // Automatic payment gateways are handled separately via checkout
        if ($request->payment_method === 'stripe') {
            return redirect()->route('dashboard.advertiser.stripe.checkout', ['amount' => $request->amount]);
        }
        
        if ($request->payment_method === 'paypal') {
            return redirect()->route('dashboard.advertiser.paypal.checkout', ['amount' => $request->amount]);
        }
        
        if ($request->payment_method === 'coinpayment') {
            return redirect()->route('dashboard.advertiser.coinpayments.checkout', ['amount' => $request->amount]);
        }

        try {
            $paymentService = app(\App\Services\PaymentService::class);
            
            $paymentDetails = [];
            if ($request->transaction_id) {
                $paymentDetails['transaction_id'] = $request->transaction_id;
            }
            if ($request->notes) {
                $paymentDetails['notes'] = $request->notes;
            }
            
            // Handle manual payment account
            if ($request->payment_method === 'manual' && $request->manual_payment_account_id) {
                $manualAccount = ManualPaymentAccount::findOrFail($request->manual_payment_account_id);
                $paymentDetails['manual_payment_account_id'] = $manualAccount->id;
                $paymentDetails['manual_payment_account_name'] = $manualAccount->account_name;
                $paymentDetails['manual_payment_account_type'] = $manualAccount->account_type;
                $paymentDetails['manual_payment_account_number'] = $manualAccount->account_number;
            }
            
            // Handle screenshot upload for manual payments
            $screenshotPath = null;
            if ($request->hasFile('payment_screenshot')) {
                $screenshotPath = $request->file('payment_screenshot')->store('deposit-screenshots', 'public');
            }
            
            $transaction = $paymentService->processDeposit(
                $advertiser,
                $request->amount,
                $request->payment_method,
                $paymentDetails,
                $screenshotPath
            );

            return back()->with('success', "Deposit request of $" . number_format($request->amount, 2) . " submitted successfully! Your balance will be updated once admin approves the deposit.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to process deposit: ' . $e->getMessage()])->withInput();
        }
    }
}
