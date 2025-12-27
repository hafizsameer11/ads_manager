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
        Schema::create('conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('click_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('impression_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('ad_unit_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('website_id')->nullable()->constrained()->onDelete('set null');
            $table->string('conversion_type')->default('purchase'); // purchase, signup, download, lead, etc.
            $table->decimal('conversion_value', 15, 2)->nullable(); // Optional value of conversion
            $table->string('conversion_id')->unique(); // Unique conversion identifier
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->text('conversion_data')->nullable(); // JSON data
            $table->string('postback_url')->nullable(); // Optional postback URL
            $table->boolean('postback_sent')->default(false);
            $table->timestamp('converted_at');
            $table->timestamps();
            
            $table->index('campaign_id');
            $table->index('conversion_id');
            $table->index('converted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversions');
    }
};
