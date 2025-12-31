<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Advertiser;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoicesController extends Controller
{
    /**
     * Display the invoices management page.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['invoiceable.user', 'transaction']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhereHasMorph('invoiceable', [Advertiser::class], function($advertiserQuery) use ($search) {
                      $advertiserQuery->whereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                  });
            });
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('invoice_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('invoice_date', '<=', $request->end_date);
        }
        
        $invoices = $query->latest('invoice_date')->latest()->paginate(20);
        
        // Stats
        $stats = [
            'total' => Invoice::count(),
            'draft' => Invoice::where('status', 'draft')->count(),
            'sent' => Invoice::where('status', 'sent')->count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'total_amount' => Invoice::where('status', 'paid')->sum('total_amount'),
            'pending_amount' => Invoice::whereIn('status', ['draft', 'sent'])->sum('total_amount'),
        ];
        
        return view('dashboard.admin.invoices', compact('invoices', 'stats'));
    }

    /**
     * Display invoice details.
     */
    public function show($id)
    {
        $invoice = Invoice::with(['invoiceable.user', 'transaction'])->findOrFail($id);
        return view('dashboard.admin.invoices.show', compact('invoice'));
    }

    /**
     * Generate invoice for a transaction.
     */
    public function generate($transactionId)
    {
        $transaction = Transaction::with('transactionable.user')->findOrFail($transactionId);
        
        // Check if invoice already exists for this transaction
        $existingInvoice = Invoice::where('transaction_id', $transaction->id)->first();
        if ($existingInvoice) {
            return redirect()->route('dashboard.admin.invoices.show', $existingInvoice->id)
                ->with('info', 'Invoice already exists for this transaction.');
        }
        
        // Only generate invoices for completed deposits from advertisers
        if ($transaction->type !== 'deposit' || $transaction->status !== 'completed') {
            return back()->withErrors(['error' => 'Can only generate invoices for completed deposits.']);
        }
        
        if (!$transaction->transactionable instanceof Advertiser) {
            return back()->withErrors(['error' => 'Can only generate invoices for advertiser transactions.']);
        }
        
        $advertiser = $transaction->transactionable;
        $user = $advertiser->user;
        
        // Get company info from settings
        $companyName = Setting::get('company_name', config('app.name'));
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', config('mail.from.address'));
        $taxRate = Setting::get('tax_rate', 0);
        
        // Calculate tax if applicable
        $taxAmount = ($transaction->amount * $taxRate) / 100;
        $totalAmount = $transaction->amount + $taxAmount;
        
        // Create invoice
        $invoice = Invoice::create([
            'invoiceable_type' => Advertiser::class,
            'invoiceable_id' => $advertiser->id,
            'transaction_id' => $transaction->id,
            'type' => 'deposit',
            'amount' => $transaction->amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => 'sent', // Auto-mark as sent when generated
            'invoice_date' => now(),
            'due_date' => now()->addDays(30), // 30 days payment terms
            'invoice_data' => [
                'company' => [
                    'name' => $companyName,
                    'address' => $companyAddress,
                    'phone' => $companyPhone,
                    'email' => $companyEmail,
                ],
                'client' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
                'items' => [
                    [
                        'description' => 'Account Deposit',
                        'quantity' => 1,
                        'unit_price' => $transaction->amount,
                        'total' => $transaction->amount,
                    ],
                ],
                'payment_method' => $transaction->payment_method,
                'transaction_id' => $transaction->transaction_id,
            ],
            'notes' => $transaction->notes,
        ]);
        
        return redirect()->route('dashboard.admin.invoices.show', $invoice->id)
            ->with('success', 'Invoice generated successfully.');
    }

    /**
     * Download invoice as PDF.
     */
    public function download($id)
    {
        $invoice = Invoice::with(['invoiceable.user', 'transaction'])->findOrFail($id);
        
        $html = view('dashboard.admin.invoices.pdf', compact('invoice'))->render();
        
        // Use DomPDF if available
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->download("invoice_{$invoice->invoice_number}.pdf");
        } elseif (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"invoice_{$invoice->invoice_number}.pdf\"");
        } else {
            // Fallback: return HTML that can be printed as PDF using browser print
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', "inline; filename=\"invoice_{$invoice->invoice_number}.html\"");
        }
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->markAsPaid();
        
        return back()->with('success', 'Invoice marked as paid.');
    }

    /**
     * Mark invoice as sent.
     */
    public function markAsSent($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->markAsSent();
        
        return back()->with('success', 'Invoice marked as sent.');
    }
}
