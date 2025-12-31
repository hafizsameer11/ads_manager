<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailTemplatesController extends Controller
{
    /**
     * Display a listing of email templates.
     */
    public function index()
    {
        $templates = EmailTemplate::latest()->paginate(20);
        return view('dashboard.admin.email-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new email template.
     */
    public function create()
    {
        return view('dashboard.admin.email-templates.create');
    }

    /**
     * Store a newly created email template.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:email_templates,name',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'description' => 'nullable|string',
            'variables' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $template = EmailTemplate::create([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'body_html' => $validated['body_html'],
            'body_text' => $validated['body_text'] ?? strip_tags($validated['body_html']),
            'description' => $validated['description'] ?? null,
            'variables' => $validated['variables'] ?? [],
            'is_active' => $request->has('is_active'),
        ]);

        ActivityLogService::log('email_template.created', "Email template '{$template->name}' was created", $template, [
            'template_id' => $template->id,
            'template_name' => $template->name,
        ]);

        return redirect()->route('dashboard.admin.email-templates.index')
            ->with('success', 'Email template created successfully.');
    }

    /**
     * Display the specified email template.
     */
    public function show(EmailTemplate $emailTemplate)
    {
        return view('dashboard.admin.email-templates.show', compact('emailTemplate'));
    }

    /**
     * Show the form for editing the specified email template.
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        return view('dashboard.admin.email-templates.edit', compact('emailTemplate'));
    }

    /**
     * Update the specified email template.
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:email_templates,name,' . $emailTemplate->id,
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'description' => 'nullable|string',
            'variables' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $emailTemplate->update([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'body_html' => $validated['body_html'],
            'body_text' => $validated['body_text'] ?? strip_tags($validated['body_html']),
            'description' => $validated['description'] ?? null,
            'variables' => $validated['variables'] ?? $emailTemplate->variables,
            'is_active' => $request->has('is_active'),
        ]);

        ActivityLogService::log('email_template.updated', "Email template '{$emailTemplate->name}' was updated", $emailTemplate, [
            'template_id' => $emailTemplate->id,
            'template_name' => $emailTemplate->name,
        ]);

        return redirect()->route('dashboard.admin.email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    /**
     * Remove the specified email template.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        $name = $emailTemplate->name;
        $emailTemplate->delete();

        ActivityLogService::log('email_template.deleted', "Email template '{$name}' was deleted", null, [
            'template_name' => $name,
        ]);

        return redirect()->route('dashboard.admin.email-templates.index')
            ->with('success', 'Email template deleted successfully.');
    }
}
