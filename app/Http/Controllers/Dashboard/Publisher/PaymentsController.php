<?php

namespace App\Http\Controllers\Dashboard\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\AllowedAccountType;
use App\Models\Setting;
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
        
        // Load user relationship to ensure is_active is available
        $publisher->load('user');
        
        // Withdrawals
        $query = Withdrawal::where('publisher_id', $publisher->id);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $withdrawals = $query->latest()->paginate(20);
        
        // Get enabled allowed account types
        $allowedAccountTypes = AllowedAccountType::enabled()->ordered()->get();
        
        // Get withdrawal limits from settings
        $minimumPayout = Setting::get('minimum_payout', 50);
        $maximumPayout = Setting::get('maximum_payout', 10000);
        
        // Payment summary
        $summary = [
            'available_balance' => $publisher->balance ?? 0,
            'pending_balance' => $publisher->pending_balance ?? 0,
            'minimum_payout' => $minimumPayout,
            'maximum_payout' => $maximumPayout,
            'can_withdraw' => ($publisher->balance ?? 0) >= $minimumPayout && ($user->is_active ?? 0) === 1,
            'total_withdrawn' => Withdrawal::where('publisher_id', $publisher->id)
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_withdrawals' => Withdrawal::where('publisher_id', $publisher->id)
                ->where('status', 'pending')
                ->sum('amount'),
        ];
        
        return view('dashboard.publisher.payments', compact('withdrawals', 'summary', 'allowedAccountTypes'));
    }

    /**
     * Store a withdrawal request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $minimumPayout = Setting::get('minimum_payout', 50);
        $maximumPayout = Setting::get('maximum_payout', 10000);
        
        $validationRules = [
            'amount' => "required|numeric|min:{$minimumPayout}|max:{$maximumPayout}",
            'account_type_id' => 'required|exists:allowed_account_types,id',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
        ];

        $request->validate($validationRules);

        $user = Auth::user();
        $publisher = $user->publisher;

        if (!$publisher) {
            return back()->withErrors(['error' => 'Publisher profile not found.']);
        }

        if ($user->is_active !== 1) {
            return back()->withErrors(['error' => 'Your account must be approved to make withdrawals.']);
        }

        // Check if publisher has sufficient balance
        if ($publisher->balance < $request->amount) {
            return back()->withErrors(['error' => 'Insufficient balance.']);
        }

        try {
            $withdrawalService = app(\App\Services\WithdrawalService::class);
            
            $accountType = AllowedAccountType::findOrFail($request->account_type_id);
            
            $paymentDetails = [
                'account_type_id' => $accountType->id,
                'account_type' => $accountType->name,
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
            ];
            
            $withdrawal = $withdrawalService->createWithdrawal(
                $publisher,
                $request->amount,
                'manual', // All withdrawals are now manual
                $paymentDetails
            );

            // Create notification for admins
            \App\Services\NotificationService::notifyAdmins(
                'withdrawal_requested',
                'withdrawal',
                'New Withdrawal Request',
                "Publisher {$publisher->user->name} has submitted a withdrawal request of $" . number_format($request->amount, 2),
                [
                    'withdrawal_id' => $withdrawal->id,
                    'publisher_id' => $publisher->id,
                    'amount' => $request->amount,
                    'account_type' => $accountType->name,
                ]
            );

            return back()->with('success', 'Withdrawal request submitted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
