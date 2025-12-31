<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketsController extends Controller
{
    /**
     * Display a listing of support tickets.
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignedTo'])->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned admin
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Unassigned tickets
        if ($request->filled('unassigned') && $request->unassigned == '1') {
            $query->whereNull('assigned_to');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->paginate(20);
        $admins = User::whereHas('roles', function($q) {
            $q->whereIn('slug', ['admin', 'sub-admin']);
        })->get();

        return view('dashboard.admin.support-tickets.index', compact('tickets', 'admins'));
    }

    /**
     * Display the specified support ticket.
     */
    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load(['user', 'assignedTo', 'replies.user']);
        $admins = User::whereHas('roles', function($q) {
            $q->whereIn('slug', ['admin', 'sub-admin']);
        })->get();

        return view('dashboard.admin.support-tickets.show', compact('supportTicket', 'admins'));
    }

    /**
     * Update ticket status/priority/assignment.
     */
    public function update(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:open,in_progress,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $changes = [];
        if (isset($validated['status'])) {
            $oldStatus = $supportTicket->status;
            $supportTicket->update(['status' => $validated['status']]);
            $changes['status'] = ['from' => $oldStatus, 'to' => $validated['status']];

            if ($validated['status'] === 'resolved') {
                $supportTicket->markAsResolved();
            }
        }

        if (isset($validated['priority'])) {
            $oldPriority = $supportTicket->priority;
            $supportTicket->update(['priority' => $validated['priority']]);
            $changes['priority'] = ['from' => $oldPriority, 'to' => $validated['priority']];
        }

        if (isset($validated['assigned_to'])) {
            $oldAssigned = $supportTicket->assigned_to;
            $supportTicket->update(['assigned_to' => $validated['assigned_to']]);
            $changes['assigned_to'] = ['from' => $oldAssigned, 'to' => $validated['assigned_to']];
        }

        ActivityLogService::log('support_ticket.updated', "Support ticket '{$supportTicket->ticket_number}' was updated", $supportTicket, [
            'ticket_id' => $supportTicket->id,
            'ticket_number' => $supportTicket->ticket_number,
            'changes' => $changes,
        ]);

        return back()->with('success', 'Ticket updated successfully.');
    }

    /**
     * Add a reply to the ticket.
     */
    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal' => 'boolean',
        ]);

        $reply = SupportTicketReply::create([
            'ticket_id' => $supportTicket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_internal' => $request->has('is_internal'),
        ]);

        // Update ticket status if it was closed/resolved
        if (in_array($supportTicket->status, ['resolved', 'closed'])) {
            $supportTicket->update(['status' => 'open']);
        }

        // Notify ticket owner if not internal
        if (!$reply->is_internal) {
            NotificationService::notifyUser(
                $supportTicket->user,
                'support_ticket_replied',
                'general',
                'New Reply on Ticket: ' . $supportTicket->ticket_number,
                'You have received a new reply on your support ticket.',
                ['ticket_id' => $supportTicket->id]
            );
        }

        ActivityLogService::log('support_ticket.replied', "Reply added to ticket '{$supportTicket->ticket_number}'", $supportTicket, [
            'ticket_id' => $supportTicket->id,
            'reply_id' => $reply->id,
            'is_internal' => $reply->is_internal,
        ]);

        return back()->with('success', 'Reply added successfully.');
    }

    /**
     * Remove the specified support ticket.
     */
    public function destroy(SupportTicket $supportTicket)
    {
        $ticketNumber = $supportTicket->ticket_number;
        $supportTicket->delete();

        ActivityLogService::log('support_ticket.deleted', "Support ticket '{$ticketNumber}' was deleted", null, [
            'ticket_number' => $ticketNumber,
        ]);

        return redirect()->route('dashboard.admin.support-tickets.index')
            ->with('success', 'Ticket deleted successfully.');
    }
}
