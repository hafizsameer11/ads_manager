<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'admin',
                'username' => 'admin123',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => 1, // 1 = Approved (admins are approved by default)
                'referral_code' => User::generateReferralCode(),
            ]
        );

        // Update if admin already exists with different email
        if (!$admin->wasRecentlyCreated) {
            $admin->update([
                'name' => 'admin',
                'username' => 'admin123',
                'password' => Hash::make('admin 123'),
                'role' => 'admin',
                'is_active' => 1, // 1 = Approved (admins are approved by default)
            ]);
        }

        $this->command->info('Admin user created/updated: admin@gmail.com / admin 123');
    }
}
