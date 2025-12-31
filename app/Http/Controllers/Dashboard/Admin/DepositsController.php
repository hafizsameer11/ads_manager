<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Advertiser;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\UserApprovedMail;

class DepositsController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the deposits management page.
     */
    public function index(Request $request)
    {
        // Mark all payment category notifications as read when visiting this page
        if (Auth::check() && Auth::user()->isAdmin()) {
            Notification::where('notifiable_type', \App\Models\User::class)
                ->where('notifiable_id', Auth::id())
                ->where('category', 'payment')
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }
        
        $query = Transaction::where('type', 'deposit')
            ->with('transactionable.user');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhereHasMorph('transactionable', [Advertiser::class], function($advertiserQuery) use ($search) {
                      $advertiserQuery->whereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                  });
            });
        }
        
        $deposits = $query->latest()->paginate(20);
        
        // Stats
        $stats = [
            'total' => Transaction::where('type', 'deposit')->count(),
            'pending' => Transaction::where('type', 'deposit')->where('status', 'pending')->count(),
            'completed' => Transaction::where('type', 'deposit')->where('status', 'completed')->count(),
            'failed' => Transaction::where('type', 'deposit')->where('status', 'failed')->count(),
            'pending_amount' => Transaction::where('type', 'deposit')->where('status', 'pending')->sum('amount'),
            'total_amount' => Transaction::where('type', 'deposit')->where('status', 'completed')->sum('amount'),
        ];
        
        return view('dashboard.admin.deposits', compact('deposits', 'stats'));
    }

    /**
     * Approve deposit.
     */
    public function approve(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        if ($transaction->type !== 'deposit' || $transaction->status !== 'pending') {
            return back()->withErrors(['error' => 'Invalid transaction or already processed.']);
        }

        return DB::transaction(function () use ($transaction, $request) {
            // Update transaction status
            $transaction->markAsCompleted();
            
            // Update advertiser balance
            $advertiser = $transaction->transactionable;
            if ($advertiser instanceof Advertiser) {
                $advertiser->increment('balance', $transaction->amount);
                
                // Update notes if provided
                if ($request->filled('notes')) {
                    $transaction->update(['notes' => $request->notes]);
                }
                
                // Send notification to advertiser
                \App\Services\NotificationService::notifyUser(
                    $advertiser->user,
                    'deposit_approved',
                    'payment',
                    'Deposit Approved',
                    "Your deposit of $" . number_format($transaction->amount, 2) . " has been approved and added to your balance.",
                    ['transaction_id' => $transaction->id, 'amount' => $transaction->amount]
                );
            }
            
            return back()->with('success', 'Deposit approved successfully.');
        });
    }

    /**
     * Reject deposit.
     */
    public function reject(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        if ($transaction->type !== 'deposit' || $transaction->status !== 'pending') {
            return back()->withErrors(['error' => 'Invalid transaction or already processed.']);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        return DB::transaction(function () use ($transaction, $request) {
            // Update transaction status
            $transaction->markAsFailed($request->rejection_reason);
            
            // Send notification to advertiser
            $advertiser = $transaction->transactionable;
            if ($advertiser instanceof Advertiser) {
                \App\Services\NotificationService::notifyUser(
                    $advertiser->user,
                    'deposit_rejected',
                    'payment',
                    'Deposit Rejected',
                    "Your deposit of $" . number_format($transaction->amount, 2) . " has been rejected. Reason: " . $request->rejection_reason,
                    ['transaction_id' => $transaction->id, 'amount' => $transaction->amount, 'reason' => $request->rejection_reason]
                );
            }
            
            return back()->with('success', 'Deposit rejected successfully.');
        });
    }
}
