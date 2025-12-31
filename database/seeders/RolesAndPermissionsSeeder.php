<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('permission_role')->delete();
        DB::table('role_user')->delete();
        DB::table('roles')->delete();
        DB::table('permissions')->delete();

        // Create permissions
        $permissions = [
            ['name' => 'Manage Users', 'slug' => 'manage_users', 'description' => 'Create, edit, and delete users'],
            ['name' => 'Approve Users', 'slug' => 'approve_users', 'description' => 'Approve, reject, suspend, or block users'],
            ['name' => 'Manage Deposits', 'slug' => 'manage_deposits', 'description' => 'Approve or reject advertiser deposits'],
            ['name' => 'Manage Withdrawals', 'slug' => 'manage_withdrawals', 'description' => 'Approve or reject publisher withdrawals'],
            ['name' => 'Manage Settings', 'slug' => 'manage_settings', 'description' => 'Edit system settings'],
            ['name' => 'View Activity Logs', 'slug' => 'view_activity_logs', 'description' => 'View activity logs and audit trail'],
            ['name' => 'Manage Websites', 'slug' => 'manage_websites', 'description' => 'Approve or reject publisher websites'],
            ['name' => 'Manage Campaigns', 'slug' => 'manage_campaigns', 'description' => 'Approve or reject advertiser campaigns'],
            ['name' => 'Manage Ad Units', 'slug' => 'manage_ad_units', 'description' => 'Create, edit, and delete ad units'],
            ['name' => 'Manage Roles', 'slug' => 'manage_roles', 'description' => 'Create, edit, and delete roles and permissions'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create roles
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Full system access with all permissions',
        ]);

        $subAdminRole = Role::create([
            'name' => 'Sub Admin',
            'slug' => 'sub-admin',
            'description' => 'Limited admin access with restricted permissions',
        ]);

        // Assign ALL permissions to admin
        $allPermissions = Permission::all();
        $adminRole->permissions()->attach($allPermissions->pluck('id'));

        // Assign LIMITED permissions to sub-admin
        $subAdminPermissions = Permission::whereIn('slug', [
            'approve_users',
            'manage_deposits',
            'manage_withdrawals',
            'manage_websites',
            'manage_campaigns',
            'view_activity_logs',
        ])->get();
        $subAdminRole->permissions()->attach($subAdminPermissions->pluck('id'));

        // Assign admin role to existing admin users
        $existingAdmins = \App\Models\User::where('role', 'admin')->get();
        foreach ($existingAdmins as $admin) {
            if (!$admin->hasRole('admin')) {
                $admin->assignRole('admin');
            }
        }
    }
}
