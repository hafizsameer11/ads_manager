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
        Schema::create('impressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('ad_unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('device_type')->nullable(); // desktop, mobile, tablet
            $table->string('os')->nullable();
            $table->string('browser')->nullable();
            $table->boolean('is_bot')->default(false);
            $table->boolean('is_vpn')->default(false);
            $table->boolean('is_proxy')->default(false);
            $table->decimal('revenue', 10, 4)->default(0.00);
            $table->timestamp('impression_at');
            $table->timestamps();
            
            $table->index(['campaign_id', 'impression_at']);
            $table->index(['ad_unit_id', 'impression_at']);
            $table->index('impression_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impressions');
    }
};
