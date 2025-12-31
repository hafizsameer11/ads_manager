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
use Illuminate\Support\Facades\Response;

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

    /**
     * Export deposits to CSV.
     */
    public function exportCsv(Request $request)
    {
        $query = $this->buildQuery($request);
        $deposits = $query->get();

        $filename = 'deposits_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($deposits) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'ID',
                'Transaction ID',
                'Advertiser',
                'Email',
                'Amount',
                'Payment Method',
                'Status',
                'Created At',
                'Processed At',
                'Notes'
            ]);

            // Data rows
            foreach ($deposits as $deposit) {
                $advertiser = $deposit->transactionable;
                $user = $advertiser && method_exists($advertiser, 'user') ? $advertiser->user : null;
                
                fputcsv($file, [
                    $deposit->id,
                    $deposit->transaction_id ?? 'N/A',
                    $user ? $user->name : 'N/A',
                    $user ? $user->email : 'N/A',
                    number_format($deposit->amount, 2),
                    $deposit->payment_method ?? 'N/A',
                    ucfirst($deposit->status),
                    $deposit->created_at->format('Y-m-d H:i:s'),
                    $deposit->processed_at ? $deposit->processed_at->format('Y-m-d H:i:s') : 'N/A',
                    $deposit->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export deposits to Excel (CSV format).
     */
    public function exportExcel(Request $request)
    {
        $query = $this->buildQuery($request);
        $deposits = $query->get();

        $filename = 'deposits_' . date('Y-m-d_His') . '.xls';
        
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($deposits) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'ID',
                'Transaction ID',
                'Advertiser',
                'Email',
                'Amount',
                'Payment Method',
                'Status',
                'Created At',
                'Processed At',
                'Notes'
            ], "\t");

            // Data rows
            foreach ($deposits as $deposit) {
                $advertiser = $deposit->transactionable;
                $user = $advertiser && method_exists($advertiser, 'user') ? $advertiser->user : null;
                
                fputcsv($file, [
                    $deposit->id,
                    $deposit->transaction_id ?? 'N/A',
                    $user ? $user->name : 'N/A',
                    $user ? $user->email : 'N/A',
                    number_format($deposit->amount, 2),
                    $deposit->payment_method ?? 'N/A',
                    ucfirst($deposit->status),
                    $deposit->created_at->format('Y-m-d H:i:s'),
                    $deposit->processed_at ? $deposit->processed_at->format('Y-m-d H:i:s') : 'N/A',
                    $deposit->notes ?? ''
                ], "\t");
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export deposits to PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildQuery($request);
        $deposits = $query->get();

        $html = view('dashboard.admin.exports.deposits-pdf', compact('deposits'))->render();
        
        // Use DomPDF if available
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download('deposits_' . date('Y-m-d_His') . '.pdf');
        } elseif (class_exists('\Dompdf\Dompdf')) {
            // Use Dompdf directly if available
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="deposits_' . date('Y-m-d_His') . '.pdf"');
        } else {
            // Fallback: return HTML that can be printed as PDF using browser print
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'inline; filename="deposits_' . date('Y-m-d_His') . '.html"');
        }
    }

    /**
     * Build query with filters.
     */
    private function buildQuery(Request $request)
    {
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
        
        return $query->latest();
    }
}
