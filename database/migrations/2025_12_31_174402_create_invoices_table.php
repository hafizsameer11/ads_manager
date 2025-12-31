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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->morphs('invoiceable'); // advertiser or publisher
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->enum('type', ['deposit', 'withdrawal', 'campaign_spend', 'earnings', 'refund'])->default('deposit');
            $table->decimal('amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['draft', 'sent', 'paid', 'cancelled'])->default('draft');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('invoice_data')->nullable(); // Store invoice details (items, company info, etc.)
            $table->timestamps();

            $table->index('invoice_number');
            $table->index('status');
            $table->index('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
