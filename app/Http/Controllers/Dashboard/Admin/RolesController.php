<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RolesController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])->latest()->paginate(20);
        return view('dashboard.admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            // Group permissions by their prefix (e.g., 'manage_', 'view_', 'approve_')
            $parts = explode('_', $permission->slug);
            return $parts[0] ?? 'other';
        });
        
        return view('dashboard.admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->attach($validated['permissions']);
        }

        // Log activity
        ActivityLogService::log('role.created', "Role '{$role->name}' was created", $role, [
            'role_name' => $role->name,
            'role_slug' => $role->slug,
            'permissions_count' => count($validated['permissions'] ?? []),
        ]);

        return redirect()->route('dashboard.admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);
        return view('dashboard.admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        // Prevent editing admin role (protection)
        if ($role->slug === 'admin') {
            return redirect()->route('dashboard.admin.roles.index')
                ->withErrors(['error' => 'The admin role cannot be edited.']);
        }

        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode('_', $permission->slug);
            return $parts[0] ?? 'other';
        });
        
        $role->load('permissions');
        $selectedPermissions = $role->permissions->pluck('id')->toArray();
        
        return view('dashboard.admin.roles.edit', compact('role', 'permissions', 'selectedPermissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        // Prevent editing admin role
        if ($role->slug === 'admin') {
            return redirect()->route('dashboard.admin.roles.index')
                ->withErrors(['error' => 'The admin role cannot be edited.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string|max:1000',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ]);

        // Sync permissions
        $role->permissions()->sync($validated['permissions'] ?? []);

        // Log activity
        ActivityLogService::log('role.updated', "Role '{$role->name}' was updated", $role, [
            'role_name' => $role->name,
            'role_slug' => $role->slug,
            'permissions_count' => count($validated['permissions'] ?? []),
        ]);

        return redirect()->route('dashboard.admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        // Prevent deleting admin role
        if ($role->slug === 'admin') {
            return redirect()->route('dashboard.admin.roles.index')
                ->withErrors(['error' => 'The admin role cannot be deleted.']);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('dashboard.admin.roles.index')
                ->withErrors(['error' => 'Cannot delete role that has users assigned. Remove users first.']);
        }

        $roleName = $role->name;
        $role->delete();

        // Log activity
        ActivityLogService::log('role.deleted', "Role '{$roleName}' was deleted", null, [
            'role_name' => $roleName,
            'role_slug' => $role->slug,
        ]);

        return redirect()->route('dashboard.admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Assign role to user.
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        
        if (!$user->hasRole($role->slug)) {
            $user->assignRole($role->slug);
            
            // Log activity
            ActivityLogService::log('user.role_assigned', "Role '{$role->name}' was assigned to user '{$user->name}'", $user, [
                'user_name' => $user->name,
                'role_name' => $role->name,
            ]);
        }

        return back()->with('success', 'Role assigned successfully.');
    }

    /**
     * Remove role from user.
     */
    public function removeRole(User $user, Role $role)
    {
        // Prevent removing admin role from last admin
        if ($role->slug === 'admin' && Role::where('slug', 'admin')->first()->users()->count() <= 1) {
            return back()->withErrors(['error' => 'Cannot remove admin role from the last admin user.']);
        }

        if ($user->hasRole($role->slug)) {
            $user->removeRole($role->slug);
            
            // Log activity
            ActivityLogService::log('user.role_removed', "Role '{$role->name}' was removed from user '{$user->name}'", $user, [
                'user_name' => $user->name,
                'role_name' => $role->name,
            ]);
        }

        return back()->with('success', 'Role removed successfully.');
    }
}
