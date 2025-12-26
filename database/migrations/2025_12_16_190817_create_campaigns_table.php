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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertiser_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('ad_type', ['banner', 'popup', 'popunder', 'native', 'push'])->default('banner');
            $table->enum('pricing_model', ['cpm', 'cpc', 'cpa'])->default('cpc');
            $table->decimal('budget', 15, 2);
            $table->decimal('daily_budget', 15, 2)->nullable();
            $table->decimal('bid_amount', 10, 4)->default(0.00);
            $table->string('target_url');
            $table->text('ad_content')->nullable(); // JSON or text for ad creative
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['pending', 'active', 'paused', 'stopped', 'rejected', 'completed'])->default('pending');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->decimal('total_spent', 15, 2)->default(0.00);
            $table->bigInteger('impressions')->default(0);
            $table->bigInteger('clicks')->default(0);
            $table->decimal('ctr', 5, 2)->default(0.00);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
