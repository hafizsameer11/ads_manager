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
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->foreignId('manual_payment_account_id')->nullable()->after('payment_method')->constrained('manual_payment_accounts')->onDelete('set null');
            $table->string('account_type')->nullable()->after('manual_payment_account_id');
            $table->string('account_name')->nullable()->after('account_type');
            $table->string('account_number')->nullable()->after('account_name');
            $table->string('payment_screenshot')->nullable()->after('account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropForeign(['manual_payment_account_id']);
            $table->dropColumn(['manual_payment_account_id', 'account_type', 'account_name', 'account_number', 'payment_screenshot']);
        });
    }
};
