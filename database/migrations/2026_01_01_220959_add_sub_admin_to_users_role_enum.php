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
        // Modify the ENUM to include 'sub-admin'
        // MySQL doesn't support direct ENUM modification, so we use raw SQL
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'publisher', 'advertiser', 'sub-admin') DEFAULT 'advertiser'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'sub-admin' from the ENUM
        // First, update any existing sub-admin users to 'admin' (temporary)
        DB::statement("UPDATE `users` SET `role` = 'admin' WHERE `role` = 'sub-admin'");
        
        // Then modify the ENUM back to original
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'publisher', 'advertiser') DEFAULT 'advertiser'");
    }
};
