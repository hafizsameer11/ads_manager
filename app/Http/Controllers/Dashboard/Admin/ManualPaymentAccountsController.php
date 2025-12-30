<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManualPaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ManualPaymentAccountsController extends Controller
{
    /**
     * Display a listing of manual payment accounts.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $accounts = ManualPaymentAccount::ordered()->paginate(20);
        
        return view('dashboard.admin.manual-payment-accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new manual payment account.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('dashboard.admin.manual-payment-accounts.create');
    }

    /**
     * Store a newly created manual payment account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_type' => 'required|string|max:255',
            'account_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle image upload
        if ($request->hasFile('account_image')) {
            $imagePath = $request->file('account_image')->store('manual-payment-accounts', 'public');
            $validated['account_image'] = $imagePath;
        }

        // Handle checkbox - if not present, it's false
        $validated['is_enabled'] = $request->has('is_enabled') ? true : false;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        ManualPaymentAccount::create($validated);

        return redirect()->route('dashboard.admin.manual-payment-accounts.index')
            ->with('success', 'Manual payment account created successfully.');
    }

    /**
     * Show the form for editing the specified manual payment account.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $account = ManualPaymentAccount::findOrFail($id);
        
        return view('dashboard.admin.manual-payment-accounts.edit', compact('account'));
    }

    /**
     * Update the specified manual payment account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $account = ManualPaymentAccount::findOrFail($id);

        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_type' => 'required|string|max:255',
            'account_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle image upload
        if ($request->hasFile('account_image')) {
            // Delete old image if exists
            if ($account->account_image) {
                Storage::disk('public')->delete($account->account_image);
            }
            
            $imagePath = $request->file('account_image')->store('manual-payment-accounts', 'public');
            $validated['account_image'] = $imagePath;
        } else {
            // Keep existing image if no new image uploaded
            unset($validated['account_image']);
        }

        // Handle checkbox - if not present, it's false
        $validated['is_enabled'] = $request->has('is_enabled') ? true : false;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $account->update($validated);

        return redirect()->route('dashboard.admin.manual-payment-accounts.index')
            ->with('success', 'Manual payment account updated successfully.');
    }

    /**
     * Remove the specified manual payment account.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $account = ManualPaymentAccount::findOrFail($id);

        // Delete image if exists
        if ($account->account_image) {
            Storage::disk('public')->delete($account->account_image);
        }

        $account->delete();

        return redirect()->route('dashboard.admin.manual-payment-accounts.index')
            ->with('success', 'Manual payment account deleted successfully.');
    }

    /**
     * Toggle the enabled status of a manual payment account.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($id)
    {
        $account = ManualPaymentAccount::findOrFail($id);
        $account->update(['is_enabled' => !$account->is_enabled]);

        $status = $account->is_enabled ? 'enabled' : 'disabled';
        return back()->with('success', "Manual payment account {$status} successfully.");
    }
}

