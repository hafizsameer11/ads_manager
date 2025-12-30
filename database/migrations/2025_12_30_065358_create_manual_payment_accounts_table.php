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
        Schema::create('manual_payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_name');
            $table->string('account_number');
            $table->string('account_type'); // e.g., JazzCash, EasyPaisa, etc.
            $table->string('account_image')->nullable(); // Path to uploaded image
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0); // For ordering display
            $table->timestamps();
            
            $table->index('is_enabled');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_payment_accounts');
    }
};
