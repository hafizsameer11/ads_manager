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
            $table->enum('verification_status', ['pending', 'verified', 'failed'])->default('pending')->after('verification_code');
        });
        
        // Set existing websites with verified_at as verified
        DB::table('websites')
            ->whereNotNull('verified_at')
            ->update(['verification_status' => 'verified']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('verification_status');
        });
    }
};

