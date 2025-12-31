<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class WithdrawalsController extends Controller
{
    /**
     * Display the withdrawals management page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Mark all withdrawal category notifications as read when visiting this page
        if (Auth::check() && Auth::user()->isAdmin()) {
            Notification::where('notifiable_type', \App\Models\User::class)
                ->where('notifiable_id', Auth::id())
                ->where('category', 'withdrawal')
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }
        
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
            'total_paid_out' => Withdrawal::where('status', 'processed')->sum('amount'), // Total completed payouts
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
            NotificationService::notifyWithdrawalProcessing(
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
            NotificationService::notifyWithdrawalProcessing(
                $withdrawal->publisher->user,
                $withdrawal->id,
                $withdrawal->amount,
                'rejected',
                $request->rejection_reason
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
            NotificationService::notifyWithdrawalProcessing(
                $withdrawal->publisher->user,
                $withdrawal->id,
                $withdrawal->amount,
                'processed'
            );
        }

        return back()->with('success', 'Withdrawal marked as paid.');
    }

    /**
     * Export withdrawals to CSV.
     */
    public function exportCsv(Request $request)
    {
        $query = $this->buildQuery($request);
        $withdrawals = $query->get();

        $filename = 'withdrawals_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($withdrawals) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'ID',
                'Publisher',
                'Email',
                'Amount',
                'Payment Method',
                'Account Type',
                'Account Name',
                'Account Number',
                'Status',
                'Transaction ID',
                'Created At',
                'Processed At',
                'Rejection Reason'
            ]);

            // Data rows
            foreach ($withdrawals as $withdrawal) {
                $publisher = $withdrawal->publisher;
                $user = $publisher && $publisher->user ? $publisher->user : null;
                
                fputcsv($file, [
                    $withdrawal->id,
                    $user ? $user->name : 'N/A',
                    $user ? $user->email : 'N/A',
                    number_format($withdrawal->amount, 2),
                    $withdrawal->payment_method ?? 'N/A',
                    $withdrawal->account_type ?? 'N/A',
                    $withdrawal->account_name ?? 'N/A',
                    $withdrawal->account_number ?? 'N/A',
                    ucfirst($withdrawal->status),
                    $withdrawal->transaction_id ?? 'N/A',
                    $withdrawal->created_at->format('Y-m-d H:i:s'),
                    $withdrawal->processed_at ? $withdrawal->processed_at->format('Y-m-d H:i:s') : 'N/A',
                    $withdrawal->rejection_reason ?? ''
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export withdrawals to Excel (CSV format).
     */
    public function exportExcel(Request $request)
    {
        $query = $this->buildQuery($request);
        $withdrawals = $query->get();

        $filename = 'withdrawals_' . date('Y-m-d_His') . '.xls';
        
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($withdrawals) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'ID',
                'Publisher',
                'Email',
                'Amount',
                'Payment Method',
                'Account Type',
                'Account Name',
                'Account Number',
                'Status',
                'Transaction ID',
                'Created At',
                'Processed At',
                'Rejection Reason'
            ], "\t");

            // Data rows
            foreach ($withdrawals as $withdrawal) {
                $publisher = $withdrawal->publisher;
                $user = $publisher && $publisher->user ? $publisher->user : null;
                
                fputcsv($file, [
                    $withdrawal->id,
                    $user ? $user->name : 'N/A',
                    $user ? $user->email : 'N/A',
                    number_format($withdrawal->amount, 2),
                    $withdrawal->payment_method ?? 'N/A',
                    $withdrawal->account_type ?? 'N/A',
                    $withdrawal->account_name ?? 'N/A',
                    $withdrawal->account_number ?? 'N/A',
                    ucfirst($withdrawal->status),
                    $withdrawal->transaction_id ?? 'N/A',
                    $withdrawal->created_at->format('Y-m-d H:i:s'),
                    $withdrawal->processed_at ? $withdrawal->processed_at->format('Y-m-d H:i:s') : 'N/A',
                    $withdrawal->rejection_reason ?? ''
                ], "\t");
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export withdrawals to PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildQuery($request);
        $withdrawals = $query->get();

        $html = view('dashboard.admin.exports.withdrawals-pdf', compact('withdrawals'))->render();
        
        // Use DomPDF if available
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download('withdrawals_' . date('Y-m-d_His') . '.pdf');
        } elseif (class_exists('\Dompdf\Dompdf')) {
            // Use Dompdf directly if available
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="withdrawals_' . date('Y-m-d_His') . '.pdf"');
        } else {
            // Fallback: return HTML that can be printed as PDF using browser print
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'inline; filename="withdrawals_' . date('Y-m-d_His') . '.html"');
        }
    }

    /**
     * Build query with filters.
     */
    private function buildQuery(Request $request)
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
        
        return $query->latest();
    }
}

