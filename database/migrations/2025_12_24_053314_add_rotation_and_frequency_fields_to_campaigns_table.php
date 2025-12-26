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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->integer('max_impressions_per_user')->nullable()->after('ctr');
            $table->integer('max_clicks_per_user')->nullable()->after('max_impressions_per_user');
            $table->integer('rotation_weight')->default(1)->after('max_clicks_per_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['max_impressions_per_user', 'max_clicks_per_user', 'rotation_weight']);
        });
    }
};
