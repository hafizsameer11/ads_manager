<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {--name=admin} {--email=admin@gmail.com} {--password=11221122}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->info("Admin user created successfully!");
        } else {
            $this->info("Admin user already exists. Updating...");
            $user->update([
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Active'],
            [[$user->id, $user->name, $user->email, $user->role, $user->is_active ? 'Yes' : 'No']]
        );

        return Command::SUCCESS;
    }
}
