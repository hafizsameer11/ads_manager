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
        Schema::create('ad_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['banner', 'popup', 'popunder', 'native', 'push'])->default('banner');
            $table->string('unit_code')->unique();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->enum('status', ['active', 'paused', 'inactive'])->default('active');
            $table->boolean('is_anti_adblock')->default(false);
            $table->decimal('cpm_rate', 10, 4)->default(0.00);
            $table->decimal('cpc_rate', 10, 4)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_units');
    }
};
