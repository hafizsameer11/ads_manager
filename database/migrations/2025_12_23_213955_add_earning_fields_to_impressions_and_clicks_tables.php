<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add earning fields to impressions table (only if they don't exist)
        if (!Schema::hasColumn('impressions', 'publisher_earning')) {
            Schema::table('impressions', function (Blueprint $table) {
                $table->decimal('publisher_earning', 10, 4)->default(0.00)->after('revenue');
            });
        }
        
        if (!Schema::hasColumn('impressions', 'admin_profit')) {
            Schema::table('impressions', function (Blueprint $table) {
                $table->decimal('admin_profit', 10, 4)->default(0.00)->after('publisher_earning');
            });
        }

        // Add earning fields to clicks table (only if they don't exist)
        if (!Schema::hasColumn('clicks', 'publisher_earning')) {
            Schema::table('clicks', function (Blueprint $table) {
                $table->decimal('publisher_earning', 10, 4)->default(0.00)->after('revenue');
            });
        }
        
        if (!Schema::hasColumn('clicks', 'admin_profit')) {
            Schema::table('clicks', function (Blueprint $table) {
                $table->decimal('admin_profit', 10, 4)->default(0.00)->after('publisher_earning');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('impressions', function (Blueprint $table) {
            $table->dropColumn(['publisher_earning', 'admin_profit']);
        });

        Schema::table('clicks', function (Blueprint $table) {
            $table->dropColumn(['publisher_earning', 'admin_profit']);
        });
    }
};
