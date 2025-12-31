<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateUsersStatusToPending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-status-to-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update publisher and advertiser users to pending status (2) if not already approved (1) or rejected (0)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating user statuses...');

        // Update publishers and advertisers who don't have is_active = 1 (approved) or 0 (rejected)
        // Set them to 2 (pending)
        $updated = DB::table('users')
            ->whereIn('role', ['publisher', 'advertiser'])
            ->whereNotIn('is_active', [0, 1])
            ->update(['is_active' => 2]);

        $this->info("✓ Updated {$updated} user(s) to pending status (2).");

        // Show current status breakdown
        $stats = [
            'approved' => User::whereIn('role', ['publisher', 'advertiser'])->where('is_active', 1)->count(),
            'rejected' => User::whereIn('role', ['publisher', 'advertiser'])->where('is_active', 0)->count(),
            'pending' => User::whereIn('role', ['publisher', 'advertiser'])->where('is_active', 2)->count(),
            'other' => User::whereIn('role', ['publisher', 'advertiser'])->whereNotIn('is_active', [0, 1, 2])->count(),
        ];

        $this->info("\nCurrent Status Breakdown:");
        $this->info("  - Approved (1): {$stats['approved']}");
        $this->info("  - Rejected (0): {$stats['rejected']}");
        $this->info("  - Pending (2): {$stats['pending']}");
        if ($stats['other'] > 0) {
            $this->warn("  - Other/Unknown: {$stats['other']}");
        }

        return Command::SUCCESS;
    }
}
