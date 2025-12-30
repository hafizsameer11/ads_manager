<?php

namespace App\Console\Commands;

use App\Models\Publisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddDummyBalanceToPublishers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publishers:add-balance {amount=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add dummy balance to all publisher profiles for testing';

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
        
        $this->info("Adding \${$amount} balance to {$publishers->count()} publisher(s)...");
        
        // Update all publishers at once
        $updated = DB::table('publishers')
            ->update([
                'balance' => DB::raw("balance + {$amount}"),
                'total_earnings' => DB::raw("total_earnings + {$amount}"),
            ]);
        
        $this->info("✓ Successfully added \${$amount} balance to {$updated} publisher(s).");
        
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
