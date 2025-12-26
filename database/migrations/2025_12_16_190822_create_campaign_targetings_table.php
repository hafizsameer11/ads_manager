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
        Schema::create('campaign_targetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->json('countries')->nullable(); // Array of country codes
            $table->json('devices')->nullable(); // Array: desktop, mobile, tablet
            $table->json('operating_systems')->nullable(); // Array: windows, mac, android, ios, linux
            $table->json('browsers')->nullable(); // Array: chrome, firefox, safari, edge
            $table->json('languages')->nullable(); // Array of language codes
            $table->boolean('is_vpn_allowed')->default(true);
            $table->boolean('is_proxy_allowed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_targetings');
    }
};
