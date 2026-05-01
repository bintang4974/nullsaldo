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
        Schema::create('category_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->decimal('monthly_limit', 15, 2); // Budget bulanan
            $table->integer('month'); // 1-12
            $table->integer('year'); // 2024, 2025, dst
            $table->timestamps();

            // Satu kategori hanya punya satu budget per bulan
            $table->unique(['category_id', 'month', 'year']);

            // Index untuk query performance
            $table->index(['month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_budgets');
    }
};
