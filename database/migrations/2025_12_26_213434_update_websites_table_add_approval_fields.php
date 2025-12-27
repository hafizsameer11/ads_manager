<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            // Add new approval-related fields
            $table->timestamp('approved_at')->nullable()->after('verified_at');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->text('admin_note')->nullable()->after('rejection_reason');
        });
        
        // Update status enum to include 'disabled'
        DB::statement("ALTER TABLE websites MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'disabled') DEFAULT 'pending'");
        
        // Populate approved_at for existing approved websites
        DB::table('websites')
            ->where('status', 'approved')
            ->whereNull('approved_at')
            ->update(['approved_at' => DB::raw('verified_at')]);
        
        // Populate rejected_at for existing rejected websites
        DB::table('websites')
            ->where('status', 'rejected')
            ->whereNull('rejected_at')
            ->update(['rejected_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn(['approved_at', 'rejected_at', 'admin_note']);
        });
        
        // Revert status enum (remove 'disabled')
        DB::statement("ALTER TABLE websites MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
        
        // Convert any 'disabled' status back to 'rejected'
        DB::table('websites')
            ->where('status', 'disabled')
            ->update(['status' => 'rejected']);
    }
};
