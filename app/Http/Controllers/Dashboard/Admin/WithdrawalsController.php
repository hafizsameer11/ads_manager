<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalsController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the withdrawals management page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Withdrawal::with(['publisher.user']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', $search)
                  ->orWhereHas('publisher.user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $withdrawals = $query->latest()->paginate(20);
        
        // Stats
        $stats = [
            'total' => Withdrawal::count(),
            'pending' => Withdrawal::where('status', 'pending')->count(),
            'approved' => Withdrawal::where('status', 'approved')->count(),
            'processed' => Withdrawal::where('status', 'processed')->count(),
            'rejected' => Withdrawal::where('status', 'rejected')->count(),
            'pending_amount' => Withdrawal::where('status', 'pending')->sum('amount'),
            'total_amount' => Withdrawal::sum('amount'),
        ];
        
        return view('dashboard.admin.withdrawals', compact('withdrawals', 'stats'));
    }

    /**
     * Approve withdrawal.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve($id)
    {
        $withdrawal = Withdrawal::with('publisher.user')->findOrFail($id);
        
        if ($withdrawal->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending withdrawals can be approved.']);
        }

        DB::transaction(function () use ($withdrawal) {
            // Update withdrawal status
            $withdrawal->update([
                'status' => 'approved',
                'processed_at' => now(),
            ]);
            
            // Update publisher balances
            // Balance was already deducted when withdrawal was created
            // Move from pending_balance to paid_balance if pending_balance exists
            $publisher = $withdrawal->publisher;
            if ($publisher->pending_balance >= $withdrawal->amount) {
                $publisher->decrement('pending_balance', $withdrawal->amount);
            }
            $publisher->increment('paid_balance', $withdrawal->amount);
            
            // Create transaction record
            Transaction::create([
                'transactionable_type' => get_class($withdrawal->publisher),
                'transactionable_id' => $withdrawal->publisher->id,
                'type' => 'withdrawal',
                'amount' => $withdrawal->amount,
                'status' => 'completed',
                'transaction_id' => 'WD-' . $withdrawal->id,
                'notes' => "Withdrawal #{$withdrawal->id} approved",
                'processed_at' => now(),
            ]);
        });
        
        // Send notification to publisher
        if ($withdrawal->publisher && $withdrawal->publisher->user) {
            $this->notificationService->notifyWithdrawalProcessing(
                $withdrawal->publisher->user,
                $withdrawal->id,
                $withdrawal->amount,
                'approved'
            );
        }

        return back()->with('success', 'Withdrawal approved successfully.');
    }

    /**
     * Reject withdrawal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, $id)
    {
        $withdrawal = Withdrawal::with('publisher.user')->findOrFail($id);
        
        if ($withdrawal->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending withdrawals can be rejected.']);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($withdrawal, $request) {
            // Update withdrawal status
            $withdrawal->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);
            
            // Refund balance back to publisher
            $publisher = $withdrawal->publisher;
            $publisher->increment('balance', $withdrawal->amount);
            // Clear pending balance if it exists
            if ($publisher->pending_balance >= $withdrawal->amount) {
                $publisher->decrement('pending_balance', $withdrawal->amount);
            }
            
            // Create transaction record
            Transaction::create([
                'transactionable_type' => get_class($withdrawal->publisher),
                'transactionable_id' => $withdrawal->publisher->id,
                'type' => 'refund',
                'amount' => $withdrawal->amount,
                'status' => 'completed',
                'transaction_id' => 'WD-REF-' . $withdrawal->id,
                'notes' => "Withdrawal #{$withdrawal->id} rejected and refunded. Reason: {$request->rejection_reason}",
                'processed_at' => now(),
            ]);
        });
        
        // Send notification to publisher
        if ($withdrawal->publisher && $withdrawal->publisher->user) {
            $this->notificationService->notifyWithdrawalProcessing(
                $withdrawal->publisher->user,
                $withdrawal->id,
                $withdrawal->amount,
                'rejected'
            );
        }

        return back()->with('success', 'Withdrawal rejected and balance refunded.');
    }

    /**
     * Mark withdrawal as paid (processed).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markPaid(Request $request, $id)
    {
        $withdrawal = Withdrawal::with('publisher.user')->findOrFail($id);
        
        if ($withdrawal->status !== 'approved') {
            return back()->withErrors(['error' => 'Only approved withdrawals can be marked as paid.']);
        }

        $validationRules = [
            'transaction_id' => 'nullable|string|max:255',
            'payment_screenshot' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ];

        $request->validate($validationRules);

        $updateData = [
            'status' => 'processed',
            'transaction_id' => $request->transaction_id,
            'processed_at' => now(),
        ];

        // Handle screenshot upload
        if ($request->hasFile('payment_screenshot')) {
            $screenshotPath = $request->file('payment_screenshot')->store('withdrawal-screenshots', 'public');
            $updateData['payment_screenshot'] = $screenshotPath;
        }

        $withdrawal->update($updateData);
        
        // Send notification to publisher
        if ($withdrawal->publisher && $withdrawal->publisher->user) {
            $this->notificationService->notifyWithdrawalProcessing(
                $withdrawal->publisher->user,
                $withdrawal->id,
                $withdrawal->amount,
                'processing'
            );
        }

        return back()->with('success', 'Withdrawal marked as paid.');
    }
}

