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
        // Update status enum to include 'approved' and change 'verified' to 'approved'
        DB::statement("ALTER TABLE websites MODIFY COLUMN status ENUM('pending', 'approved', 'verified', 'rejected') DEFAULT 'pending'");
        
        // Update existing 'verified' records to 'approved'
        DB::table('websites')->where('status', 'verified')->update(['status' => 'approved']);
        
        // Now change enum to only have the correct values
        DB::statement("ALTER TABLE websites MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
        
        // Update verification_method enum to support new methods
        DB::statement("ALTER TABLE websites MODIFY COLUMN verification_method ENUM('manual', 'automatic', 'meta_tag', 'file_upload', 'dns') DEFAULT 'manual'");
        
        // Update existing records to use 'meta_tag' if they were 'manual' or 'automatic'
        DB::table('websites')->whereIn('verification_method', ['manual', 'automatic'])->update(['verification_method' => 'meta_tag']);
        
        // Now change enum to only have the correct values
        DB::statement("ALTER TABLE websites MODIFY COLUMN verification_method ENUM('meta_tag', 'file_upload', 'dns') DEFAULT 'meta_tag'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert verification_method
        DB::statement("ALTER TABLE websites MODIFY COLUMN verification_method ENUM('manual', 'automatic') DEFAULT 'manual'");
        
        // Revert status - change 'approved' back to 'verified'
        DB::table('websites')->where('status', 'approved')->update(['status' => 'verified']);
        DB::statement("ALTER TABLE websites MODIFY COLUMN status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending'");
    }
};
