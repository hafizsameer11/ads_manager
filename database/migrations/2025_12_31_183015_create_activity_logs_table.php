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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // e.g., 'user.created', 'user.updated', 'deposit.approved'
            $table->string('description'); // Human-readable description
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('request_method', 10)->nullable(); // GET, POST, PUT, DELETE
            $table->string('request_url')->nullable();
            $table->json('properties')->nullable(); // Additional data about the action
            $table->string('subject_type')->nullable(); // e.g., App\Models\User, App\Models\Transaction
            $table->unsignedBigInteger('subject_id')->nullable(); // ID of the subject
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
