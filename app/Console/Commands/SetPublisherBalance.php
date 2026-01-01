<?php

namespace App\Console\Commands;

use App\Models\Publisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetPublisherBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publishers:set-balance {amount=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set balance to a specific amount for all publisher accounts (for testing)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $amount = (float) $this->argument('amount');
        
        $publishers = Publisher::all();
        
        if ($publishers->isEmpty()) {
            $this->error('No publishers found.');
            return 1;
        }
        
        $this->info("Setting balance to \${$amount} for {$publishers->count()} publisher(s)...");
        
        // Update all publishers to have the specified balance
        $updated = DB::table('publishers')
            ->update([
                'balance' => $amount,
                'total_earnings' => DB::raw("GREATEST(total_earnings, {$amount})"), // Keep existing if higher
            ]);
        
        $this->info("✓ Successfully set balance to \${$amount} for {$updated} publisher(s).");
        
        // Show updated balances
        $publishers = Publisher::with('user')->get();
        $this->line("\nUpdated Publisher Balances:");
        foreach ($publishers as $publisher) {
            $userName = $publisher->user ? $publisher->user->name : 'N/A';
            $this->line("  - Publisher #{$publisher->id} ({$userName}): \${$publisher->balance}");
        }
        
        return 0;
    }
}




