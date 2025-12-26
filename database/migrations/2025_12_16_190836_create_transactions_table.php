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
        Schema::dropIfExists('transactions');
        
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->morphs('transactionable'); // advertiser or publisher
            $table->enum('type', ['deposit', 'withdrawal', 'campaign_spend', 'earnings', 'refund'])->default('deposit');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->decimal('amount', 15, 2);
            $table->string('transaction_id')->unique()->nullable();
            $table->enum('payment_method', ['paypal', 'coinpayment', 'faucetpay', 'bank_swift', 'stripe', 'wise', 'manual'])->nullable();
            $table->text('payment_details')->nullable(); // JSON
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            // Note: morphs() already creates index on transactionable_type and transactionable_id
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
