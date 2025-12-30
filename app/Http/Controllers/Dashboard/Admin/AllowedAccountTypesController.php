<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllowedAccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AllowedAccountTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accountTypes = AllowedAccountType::ordered()->get();
        return view('dashboard.admin.allowed-account-types.index', compact('accountTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.admin.allowed-account-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:allowed_account_types,name',
            'description' => 'nullable|string|max:500',
            'is_enabled' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['name', 'description', 'sort_order']);
        $data['slug'] = Str::slug($request->name);
        $data['is_enabled'] = $request->has('is_enabled') ? true : false;

        AllowedAccountType::create($data);

        return redirect()->route('dashboard.admin.allowed-account-types.index')
            ->with('success', 'Account type created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AllowedAccountType $allowedAccountType)
    {
        return view('dashboard.admin.allowed-account-types.edit', compact('allowedAccountType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AllowedAccountType $allowedAccountType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:allowed_account_types,name,' . $allowedAccountType->id,
            'description' => 'nullable|string|max:500',
            'is_enabled' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['name', 'description', 'sort_order']);
        $data['slug'] = Str::slug($request->name);
        $data['is_enabled'] = $request->has('is_enabled') ? true : false;

        $allowedAccountType->update($data);

        return redirect()->route('dashboard.admin.allowed-account-types.index')
            ->with('success', 'Account type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AllowedAccountType $allowedAccountType)
    {
        $allowedAccountType->delete();

        return redirect()->route('dashboard.admin.allowed-account-types.index')
            ->with('success', 'Account type deleted successfully.');
    }

    /**
     * Toggle the enabled status of the account type.
     */
    public function toggleStatus(AllowedAccountType $allowedAccountType)
    {
        $allowedAccountType->update([
            'is_enabled' => !$allowedAccountType->is_enabled,
        ]);

        return back()->with('success', 'Account type status updated successfully.');
    }
}
