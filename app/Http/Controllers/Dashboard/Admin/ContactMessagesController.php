<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;

class ContactMessagesController extends Controller
{
    /**
     * Display the contact messages page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = ContactSubmission::query();
        
        // Filter by read status
        if ($request->filled('status')) {
            if ($request->status === 'read') {
                $query->where('is_read', true);
            } elseif ($request->status === 'unread') {
                $query->where('is_read', false);
            }
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }
        
        $messages = $query->latest()->paginate(20);
        
        // Stats
        $stats = [
            'total' => ContactSubmission::count(),
            'unread' => ContactSubmission::where('is_read', false)->count(),
            'read' => ContactSubmission::where('is_read', true)->count(),
        ];
        
        return view('dashboard.admin.contact-messages', compact('messages', 'stats'));
    }

    /**
     * Display a single contact message.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $message = ContactSubmission::findOrFail($id);
        
        // Mark as read if not already read
        if (!$message->is_read) {
            $message->markAsRead();
        }
        
        return view('dashboard.admin.contact-message-show', compact('message'));
    }

    /**
     * Mark message as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead($id)
    {
        $message = ContactSubmission::findOrFail($id);
        $message->markAsRead();
        
        return back()->with('success', 'Message marked as read.');
    }

    /**
     * Mark message as unread.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsUnread($id)
    {
        $message = ContactSubmission::findOrFail($id);
        $message->update([
            'is_read' => false,
            'read_at' => null,
        ]);
        
        return back()->with('success', 'Message marked as unread.');
    }

    /**
     * Delete a contact message.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $message = ContactSubmission::findOrFail($id);
        $message->delete();
        
        return redirect()->route('dashboard.admin.contact-messages')
            ->with('success', 'Message deleted successfully.');
    }
}


