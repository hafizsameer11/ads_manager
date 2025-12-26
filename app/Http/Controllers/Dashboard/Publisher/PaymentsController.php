<?php

namespace App\Http\Controllers\Dashboard\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentsController extends Controller
{
    /**
     * Display the payments page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $publisher = $user->publisher;
        
        if (!$publisher) {
            return redirect()->route('dashboard.publisher.home')->with('error', 'Publisher profile not found.');
        }
        
        // Withdrawals
        $query = Withdrawal::where('publisher_id', $publisher->id);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $withdrawals = $query->latest()->paginate(20);
        
        // Payment summary
        $summary = [
            'available_balance' => $publisher->balance ?? 0,
            'pending_balance' => $publisher->pending_balance ?? 0,
            'minimum_payout' => $publisher->minimum_payout ?? 0,
            'can_withdraw' => ($publisher->balance ?? 0) >= ($publisher->minimum_payout ?? 0) && ($publisher->status ?? '') === 'approved',
            'total_withdrawn' => Withdrawal::where('publisher_id', $publisher->id)
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_withdrawals' => Withdrawal::where('publisher_id', $publisher->id)
                ->where('status', 'pending')
                ->sum('amount'),
        ];
        
        return view('dashboard.publisher.payments', compact('withdrawals', 'summary'));
    }

    /**
     * Store a withdrawal request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:paypal,coinpayment,faucetpay,bank_swift,manual',
            'payment_details' => 'required|array',
            'payment_details.account' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $publisher = $user->publisher;

        if (!$publisher) {
            return back()->withErrors(['error' => 'Publisher profile not found.']);
        }

        if ($publisher->status !== 'approved') {
            return back()->withErrors(['error' => 'Your account must be approved to make withdrawals.']);
        }

        try {
            $withdrawalService = app(\App\Services\WithdrawalService::class);
            $withdrawal = $withdrawalService->createWithdrawal(
                $publisher,
                $request->amount,
                $request->payment_method,
                $request->payment_details
            );

            return back()->with('success', 'Withdrawal request submitted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
