<?php

namespace App\Http\Controllers\Dashboard\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EarningsController extends Controller
{
    /**
     * Display the earnings page.
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
        
        $query = Transaction::where('transactionable_type', \App\Models\Publisher::class)
            ->where('transactionable_id', $publisher->id)
            ->where('type', 'earnings');
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $earnings = $query->latest()->paginate(20);
        
        // Summary stats
        $summary = [
            'total_earnings' => $publisher->total_earnings ?? 0,
            'available_balance' => $publisher->balance ?? 0,
            'pending_balance' => $publisher->pending_balance ?? 0,
            'paid_balance' => $publisher->paid_balance ?? 0,
            'this_month' => Transaction::where('transactionable_type', \App\Models\Publisher::class)
                ->where('transactionable_id', $publisher->id)
                ->where('type', 'earnings')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
        ];
        
        return view('dashboard.publisher.earnings', compact('earnings', 'summary'));
    }
}
