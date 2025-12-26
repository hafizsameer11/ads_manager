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
        Schema::table('ad_units', function (Blueprint $table) {
            // Add publisher_id (FK to users table)
            $table->foreignId('publisher_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            
            // Add size field (for banner, e.g., "300x250")
            $table->string('size')->nullable()->after('type');
            
            // Add frequency field (for popup, in seconds)
            $table->integer('frequency')->nullable()->after('size');
            
            // Update type enum to only banner and popup as per requirements
            // Note: We'll keep existing types but restrict in validation
            
            // Update status enum to match requirements (active, paused)
            // Note: We'll keep existing statuses but use active/paused in validation
        });
        
        // Populate publisher_id from website relationship
        DB::statement("
            UPDATE ad_units 
            SET publisher_id = (
                SELECT publisher_id 
                FROM websites 
                WHERE websites.id = ad_units.website_id
            )
        ");
        
        // Make publisher_id required after populating
        Schema::table('ad_units', function (Blueprint $table) {
            $table->foreignId('publisher_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_units', function (Blueprint $table) {
            $table->dropForeign(['publisher_id']);
            $table->dropColumn(['publisher_id', 'size', 'frequency']);
        });
    }
};
