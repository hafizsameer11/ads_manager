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
        Schema::table('blog_categories', function (Blueprint $table) {
            $table->dropIndex(['slug', 'is_active']);
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_categories', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->index(['slug', 'is_active']);
        });
    }
};
