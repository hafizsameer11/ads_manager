<?php

namespace App\Http\Controllers\Dashboard\User;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketsController extends Controller
{
    /**
     * Display a listing of the user's support tickets.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = SupportTicket::where('user_id', $user->id)->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(15);

        return view('dashboard.user.support-tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new support ticket.
     */
    public function create()
    {
        return view('dashboard.user.support-tickets.create');
    }

    /**
     * Store a newly created support ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        // Notify admins about new ticket
        $admins = \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('slug', ['admin', 'sub-admin']);
        })->get();

        foreach ($admins as $admin) {
            NotificationService::notifyUser(
                $admin,
                'support_ticket_created',
                'general',
                'New Support Ticket: ' . $ticket->ticket_number,
                'A new support ticket has been created by ' . Auth::user()->name . ': ' . $ticket->subject,
                ['ticket_id' => $ticket->id]
            );
        }

        $user = Auth::user();
        $routePrefix = $user->isPublisher() ? 'publisher' : 'advertiser';
        
        return redirect()->route("dashboard.{$routePrefix}.support-tickets.show", $ticket)
            ->with('success', 'Support ticket created successfully! Your ticket number is: ' . $ticket->ticket_number);
    }

    /**
     * Display the specified support ticket.
     */
    public function show(SupportTicket $supportTicket)
    {
        // Ensure user can only view their own tickets
        if ($supportTicket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this ticket.');
        }

        $supportTicket->load(['assignedTo', 'replies' => function($query) {
            // Users can only see non-internal replies
            $query->where('is_internal', false)->with('user');
        }]);

        return view('dashboard.user.support-tickets.show', compact('supportTicket'));
    }

    /**
     * Add a reply to the ticket.
     */
    public function reply(Request $request, SupportTicket $supportTicket)
    {
        // Ensure user can only reply to their own tickets
        if ($supportTicket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this ticket.');
        }

        // Prevent replies to closed tickets
        if ($supportTicket->status === 'closed') {
            return back()->with('error', 'You cannot reply to a closed ticket. Please create a new ticket if you need further assistance.');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:5',
        ]);

        $reply = SupportTicketReply::create([
            'ticket_id' => $supportTicket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_internal' => false, // User replies are always public
        ]);

        // Update ticket status if it was resolved/closed
        if (in_array($supportTicket->status, ['resolved', 'closed'])) {
            $supportTicket->update(['status' => 'open']);
        }

        // Notify assigned admin or all admins if unassigned
        if ($supportTicket->assignedTo) {
            NotificationService::notifyUser(
                $supportTicket->assignedTo,
                'support_ticket_replied',
                'general',
                'New Reply on Ticket: ' . $supportTicket->ticket_number,
                Auth::user()->name . ' replied to ticket: ' . $supportTicket->subject,
                ['ticket_id' => $supportTicket->id]
            );
        } else {
            $admins = \App\Models\User::whereHas('roles', function($q) {
                $q->whereIn('slug', ['admin', 'sub-admin']);
            })->get();

            foreach ($admins as $admin) {
                NotificationService::notifyUser(
                    $admin,
                    'support_ticket_replied',
                    'general',
                    'New Reply on Ticket: ' . $supportTicket->ticket_number,
                    Auth::user()->name . ' replied to unassigned ticket: ' . $supportTicket->subject,
                    ['ticket_id' => $supportTicket->id]
                );
            }
        }

        return back()->with('success', 'Your reply has been sent successfully.');
    }
}
