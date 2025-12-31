<?php

namespace App\Console\Commands;

use App\Models\Publisher;
use App\Models\Advertiser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddDummyBalanceToAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:add-balance {amount=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add dummy balance to all publisher and advertiser accounts for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $amount = (float) $this->argument('amount');
        
        $publishers = Publisher::all();
        $advertisers = Advertiser::all();
        
        if ($publishers->isEmpty() && $advertisers->isEmpty()) {
            $this->error('No publishers or advertisers found.');
            return 1;
        }
        
        $this->info("Adding \${$amount} balance to publishers and advertisers...\n");
        
        // Update publishers
        if (!$publishers->isEmpty()) {
            $publisherCount = DB::table('publishers')
                ->update([
                    'balance' => DB::raw("balance + {$amount}"),
                    'total_earnings' => DB::raw("total_earnings + {$amount}"),
                ]);
            
            $this->info("✓ Successfully added \${$amount} balance to {$publisherCount} publisher(s).");
        } else {
            $this->warn("No publishers found.");
        }
        
        // Update advertisers
        if (!$advertisers->isEmpty()) {
            $advertiserCount = DB::table('advertisers')
                ->update([
                    'balance' => DB::raw("balance + {$amount}"),
                ]);
            
            $this->info("✓ Successfully added \${$amount} balance to {$advertiserCount} advertiser(s).");
        } else {
            $this->warn("No advertisers found.");
        }
        
        // Show updated balances
        $this->line("\n" . str_repeat('=', 60));
        $this->line("Updated Publisher Balances:");
        $this->line(str_repeat('=', 60));
        
        $publishers = Publisher::with('user')->get();
        if ($publishers->isEmpty()) {
            $this->line("  No publishers found.");
        } else {
            foreach ($publishers as $publisher) {
                $userName = $publisher->user ? $publisher->user->name : 'N/A';
                $this->line("  - Publisher #{$publisher->id} ({$userName}): \${$publisher->balance}");
            }
        }
        
        $this->line("\n" . str_repeat('=', 60));
        $this->line("Updated Advertiser Balances:");
        $this->line(str_repeat('=', 60));
        
        $advertisers = Advertiser::with('user')->get();
        if ($advertisers->isEmpty()) {
            $this->line("  No advertisers found.");
        } else {
            foreach ($advertisers as $advertiser) {
                $userName = $advertiser->user ? $advertiser->user->name : 'N/A';
                $this->line("  - Advertiser #{$advertiser->id} ({$userName}): \${$advertiser->balance}");
            }
        }
        
        $this->line("\n" . str_repeat('=', 60));
        
        return 0;
    }
}
