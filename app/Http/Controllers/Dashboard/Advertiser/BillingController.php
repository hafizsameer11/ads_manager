<?php

namespace App\Http\Controllers\Dashboard\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        
        return view('dashboard.advertiser.billing', compact('transactions', 'summary'));
    }

    /**
     * Store a deposit request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'payment_method' => 'required|in:paypal,coinpayment,faucetpay,stripe,bank_swift,wise',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $advertiser = $user->advertiser;

        if (!$advertiser) {
            return back()->withErrors(['error' => 'Advertiser profile not found.']);
        }

        if ($advertiser->status !== 'approved') {
            return back()->withErrors(['error' => 'Your account must be approved to make deposits.']);
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
            
            $transaction = $paymentService->processDeposit(
                $advertiser,
                $request->amount,
                $request->payment_method,
                $paymentDetails
            );

            return back()->with('success', "Deposit request of $" . number_format($request->amount, 2) . " submitted successfully! Your balance will be updated once admin approves the deposit.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to process deposit: ' . $e->getMessage()])->withInput();
        }
    }
}
